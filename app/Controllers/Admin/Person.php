<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PersonModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use RuntimeException;
use SimpleXMLElement;
use Throwable;
use ZipArchive;

class Person extends BaseController
{
    public function index(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $query = trim((string) $this->request->getGet('q'));
        $field = (string) $this->request->getGet('field');

        $searchableFields = [
            'all'       => 'Todos os campos',
            'nome'      => 'Nome',
            'email'     => 'E-mail',
            'lattes_id' => 'ID Lattes',
            'orcid'     => 'ORCID',
            'cpf'       => 'CPF',
            'cracha'    => 'Crachá',
        ];

        if (! array_key_exists($field, $searchableFields)) {
            $field = 'all';
        }

        $model      = $this->applySearch(new PersonModel(), $query, $field);
        $countModel = $this->applySearch(new PersonModel(), $query, $field);
        $persons    = $model
            ->orderBy('nome', 'ASC')
            ->orderBy('use', 'ASC')
            ->orderBy("CASE WHEN `lattes_id` IS NOT NULL AND TRIM(`lattes_id`) != '' THEN 0 ELSE 1 END", '', false)
            ->paginate(25, 'persons');
        $pager      = $model->pager;
        $pager->only(['q', 'field']);

        return view('admin/person/index', [
            'title'            => 'Administrar pessoas',
            'persons'          => $persons,
            'pager'            => $pager,
            'total'            => $countModel->countAllResults(),
            'query'            => $query,
            'field'            => $field,
            'searchableFields' => $searchableFields,
        ]);
    }

    private function applySearch(PersonModel $model, string $query, string $field): PersonModel
    {
        if ($query === '') {
            return $model;
        }

        if ($field !== 'all') {
            return $field === 'nome'
                ? $this->applyNameSearch($model, $query)
                : $model->like($field, $query, 'both', null, true);
        }

        $model->groupStart()->groupStart();
        $this->applyNameSearch($model, $query)->groupEnd();

        return $model
            ->orLike('email', $query, 'both', null, true)
            ->orLike('lattes_id', $query, 'both', null, true)
            ->orLike('orcid', $query, 'both', null, true)
            ->orLike('cpf', $query, 'both', null, true)
            ->orLike('cracha', $query, 'both', null, true)
            ->groupEnd();
    }

    private function applyNameSearch(PersonModel $model, string $query): PersonModel
    {
        $words = preg_split('/\\s+/u', trim($query), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($words as $word) {
            $model->like('nome', $word, 'both', null, true);
        }

        return $model;
    }

    public function inport(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        return view('admin/person/inport', [
            'title'         => 'Importar pessoas',
            'lookupResult'  => null,
            'lattesId'      => '',
        ]);
    }

    public function searchLattes(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $lattesId = preg_replace('/\D/', '', (string) $this->request->getPost('lattes_id')) ?? '';
        if (preg_match('/^\d{16}$/', $lattesId) !== 1) {
            return view('admin/person/inport', [
                'title'        => 'Importar pessoas',
                'lattesId'     => $lattesId,
                'lookupResult' => [
                    'status'  => 'error',
                    'message' => 'Informe um ID Lattes com exatamente 16 dígitos.',
                ],
            ]);
        }

        $existingPerson = db_connect()->table('individuo')
            ->where('lattes_id', $lattesId)
            ->orderBy('use', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if ($existingPerson !== null) {
            return view('admin/person/inport', [
                'title'        => 'Importar pessoas',
                'lattesId'     => $lattesId,
                'lookupResult' => [
                    'status'  => 'duplicate',
                    'message' => 'Este ID Lattes já está cadastrado.',
                    'person'  => $existingPerson,
                ],
            ]);
        }

        $personData = [
            'nome'  => '',
            'email' => '',
            'orcid' => '',
        ];
        $sources = [];

        try {
            $personData = $this->fetchLattesPersonData($lattesId);
            if ($personData['nome'] !== '') {
                $sources[] = 'serviço de consulta do currículo Lattes';
            }
        } catch (Throwable $exception) {
            log_message('warning', 'Consulta Lattes falhou para o ID {id} ({exception}).', [
                'id'        => $lattesId,
                'exception' => $exception::class,
            ]);
        }

        $scholarMessage = 'O Google Acadêmico não oferece uma API oficial para localizar uma pessoa pelo ID Lattes.';
        if ($personData['nome'] === '') {
            try {
                $scholarResponse = service('curlrequest')->get('https://scholar.google.com/scholar', [
                    'query'          => ['hl' => 'pt-BR', 'q' => $lattesId],
                    'connect_timeout' => 5,
                    'timeout'         => 10,
                    'http_errors'     => false,
                ]);
                if ($scholarResponse->getStatusCode() === 200) {
                    $scholarMessage .= ' A busca pública respondeu, mas não foi possível confirmar um perfil correspondente com segurança.';
                } else {
                    $scholarMessage .= ' A busca pública não está disponível no momento.';
                }
            } catch (Throwable $exception) {
                log_message('warning', 'Busca pública no Google Acadêmico falhou para o ID Lattes {id} ({exception}).', [
                    'id'        => $lattesId,
                    'exception' => $exception::class,
                ]);
                $scholarMessage .= ' A busca pública não está disponível no momento.';
            }
        }

        $found = $personData['nome'] !== '';
        return view('admin/person/inport', [
            'title'        => 'Importar pessoas',
            'lattesId'     => $lattesId,
            'lookupResult' => [
                'status'         => $found ? 'found' : 'not_found',
                'message'        => $found
                    ? 'Dados encontrados. Revise-os no cadastro antes de salvar.'
                    : 'Não foi possível obter dados pessoais automaticamente. Você ainda pode abrir o cadastro e preencher os dados manualmente.',
                'personData'     => $personData,
                'sources'        => $sources,
                'scholarMessage' => $scholarMessage,
            ],
        ]);
    }

    public function registerLattesPerson(): RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $lattesId = trim((string) $this->request->getPost('lattes_id'));
        if (preg_match('/^\d{16}$/', $lattesId) !== 1) {
            return redirect()->to(site_url('admin/person/inport'))
                ->with('error', 'O ID Lattes deve conter exatamente 16 dígitos.');
        }

        $existingPerson = db_connect()->table('individuo')
            ->where('lattes_id', $lattesId)
            ->orderBy('use', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();
        if ($existingPerson !== null) {
            return redirect()->to(site_url('person/edit/' . (int) $existingPerson['id']))
                ->with('info', 'Este ID Lattes já estava cadastrado. Você pode editar o cadastro existente.');
        }

        $name = trim((string) $this->request->getPost('nome'));
        $email = trim((string) $this->request->getPost('email'));
        $orcid = trim((string) $this->request->getPost('orcid'));
        if ($name === '') {
            $name = '# Docente Lattes';
        }
        if (mb_strlen($name) > 255
            || ($email !== '' && (mb_strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false))
            || ($orcid !== '' && preg_match('/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/i', $orcid) !== 1)) {
            return redirect()->to(site_url('admin/person/inport'))
                ->with('error', 'Os dados recebidos são inválidos. Faça a busca novamente e revise os valores.');
        }

        $personModel = new PersonModel();
        $personId = $personModel->insert([
            'nome'      => $name,
            'lattes_id' => $lattesId,
            'email'     => $email !== '' ? $email : null,
            'orcid'     => $orcid !== '' ? strtoupper($orcid) : null,
        ]);
        if ($personId === false) {
            return redirect()->to(site_url('admin/person/inport'))
                ->with('error', implode(' ', $personModel->errors()));
        }

        return redirect()->to(site_url('person/edit/' . (int) $personId))
            ->with('success', 'Cadastro criado. Revise e complete os dados da pessoa.');
    }

    /** @return array{nome: string, email: string, orcid: string} */
    private function fetchLattesPersonData(string $lattesId): array
    {
        $token = trim((string) env('lattes.apiToken'));
        if ($token === '') {
            throw new RuntimeException('O serviço de consulta Lattes não está configurado.');
        }

        $verifySsl = filter_var(env('lattes.verifySsl', true), FILTER_VALIDATE_BOOL);
        $caBundle = trim((string) env('lattes.caBundle'));
        if ($verifySsl && ($caBundle === '' || ! is_file($caBundle))) {
            throw new RuntimeException('O certificado SSL do serviço Lattes não está configurado.');
        }

        $response = service('curlrequest')->get('https://brapci.inf.br/ws/api/', [
            'query' => [
                'verb'  => 'lattes',
                'q'     => $lattesId,
                'token' => $token,
            ],
            'connect_timeout' => 10,
            'timeout'         => 60,
            'http_errors'     => false,
            'verify'          => $verifySsl ? $caBundle : false,
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('O serviço de consulta Lattes respondeu com HTTP ' . $response->getStatusCode() . '.');
        }

        $temporaryFile = tempnam(WRITEPATH, 'lattes_lookup_');
        if ($temporaryFile === false) {
            throw new RuntimeException('Não foi possível preparar o arquivo temporário para consulta Lattes.');
        }

        try {
            if (file_put_contents($temporaryFile, $response->getBody(), LOCK_EX) === false) {
                throw new RuntimeException('Não foi possível gravar a resposta da consulta Lattes.');
            }

            $zip = new ZipArchive();
            if ($zip->open($temporaryFile) !== true) {
                throw new RuntimeException('O serviço de consulta Lattes não retornou um arquivo válido.');
            }

            $xmlContent = false;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entryName = (string) $zip->getNameIndex($index);
                if (str_ends_with(strtolower($entryName), '.xml')) {
                    $xmlContent = $zip->getFromIndex($index);
                    break;
                }
            }
            $zip->close();

            if (! is_string($xmlContent)) {
                throw new RuntimeException('A resposta Lattes não contém um currículo XML.');
            }

            $previous = libxml_use_internal_errors(true);
            try {
                $xml = simplexml_load_string($xmlContent, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOBLANKS);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
            if (! $xml instanceof SimpleXMLElement || (string) $xml['NUMERO-IDENTIFICADOR'] !== $lattesId) {
                throw new RuntimeException('O currículo retornado não corresponde ao ID Lattes solicitado.');
            }

            $generalData = $xml->{'DADOS-GERAIS'};
            if (! $generalData instanceof SimpleXMLElement) {
                throw new RuntimeException('O currículo não contém dados pessoais gerais.');
            }
            $name = trim((string) $generalData['NOME-COMPLETO']);
            $orcid = preg_replace('#^https?://orcid\.org/#i', '', trim((string) $generalData['ORCID-ID'])) ?? '';
            $email = '';
            $address = $generalData->ENDERECO;
            $emailSource = trim((string) ($address['ELETRONICO'] ?? ''));
            if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $emailSource, $match) === 1) {
                $email = $match[0];
            }

            return [
                'nome'  => mb_substr($name, 0, 255),
                'email' => filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : '',
                'orcid' => preg_match('/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/i', $orcid) === 1 ? strtoupper($orcid) : '',
            ];
        } finally {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    public function processInport(): RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $rawData = trim((string) $this->request->getPost('data'));

        if ($rawData === '') {
            return redirect()->to(site_url('admin/person/inport'))
                ->with('error', 'Cole ao menos uma linha para importar.');
        }

        $model    = new PersonModel();
        $imported = 0;
        $errors   = [];
        $lines    = preg_split('/\R/u', $rawData) ?: [];
        $existingNames = [];
        $existingLattes = [];
        foreach ((new PersonModel())->select('nome, lattes_id')->findAll() as $person) {
            $existingNames[$this->normalizeImportName((string) $person['nome'])] = true;
            $existingId = trim((string) ($person['lattes_id'] ?? ''));
            if ($existingId !== '') {
                $existingLattes[$existingId] = true;
            }
        }

        foreach ($lines as $index => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $line      = str_ireplace('/tab', "\t", $line);
            $delimiter = str_contains($line, "\t") ? "\t" : (str_contains($line, ';') ? ';' : ',');
            $columns   = array_map('trim', str_getcsv($line, $delimiter));

            if ($index === 0 && $this->isHeader($columns)) {
                continue;
            }

            if (count($columns) < 3) {
                $errors[] = 'Linha ' . ($index + 1) . ': informe Nome, ID Lattes e e-mail. CPF e crachá são opcionais.';
                continue;
            }

            [$name, $lattesId, $email, $cpf, $cracha] = array_pad(array_slice($columns, 0, 5), 5, '');

            if ($name === '' || $lattesId === '' || $email === '') {
                $errors[] = 'Linha ' . ($index + 1) . ': Nome, ID Lattes e e-mail são obrigatórios.';
                continue;
            }
            $normalizedName = $this->normalizeImportName($name);
            $duplicates = [];
            if (isset($existingNames[$normalizedName])) {
                $duplicates[] = 'nome já cadastrado';
            }
            if (isset($existingLattes[$lattesId])) {
                $duplicates[] = 'ID Lattes já cadastrado';
            }
            if ($duplicates !== []) {
                $errors[] = 'Linha ' . ($index + 1) . ': importação rejeitada — ' . implode(' e ', $duplicates) . '.';
                continue;
            }

            if ($model->insert([
                'nome'      => $name,
                'lattes_id' => $lattesId !== '' ? $lattesId : null,
                'email'     => $email !== '' ? $email : null,
                'cpf'       => $cpf !== '' ? $cpf : null,
                'cracha'    => $cracha !== '' ? $cracha : null,
            ]) === false) {
                $errors[] = 'Linha ' . ($index + 1) . ': ' . implode(' ', $model->errors());
                continue;
            }

            $existingNames[$normalizedName] = true;
            $existingLattes[$lattesId] = true;
            $imported++;
        }

        return redirect()->to(site_url('admin/person/inport'))->with('import_result', [
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    private function normalizeImportName(string $name): string
    {
        $name = \Normalizer::normalize($name, \Normalizer::FORM_D) ?: $name;
        $name = preg_replace('/\p{Mn}/u', '', $name) ?? $name;
        return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $name) ?? $name));
    }

    public function joinNames(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $personId = (int) $this->request->getGet('person_id');
        $person = (new PersonModel())->find($personId);

        if ($person === null) {
            throw PageNotFoundException::forPageNotFound('Pessoa não encontrada.');
        }

        $words = $this->nameWords((string) $person['nome']);
        $builder = db_connect()->table('individuo')
            ->where('id !=', $personId)
            ->where('use', 0)
            ->groupStart();

        foreach ($words as $index => $word) {
            if ($index === 0) {
                $builder->like('nome', $word);
            } else {
                $builder->orLike('nome', $word);
            }
        }

        $matches = $builder->groupEnd()
            ->get()
            ->getResultArray();

        foreach ($matches as &$match) {
            $match['similarity'] = $this->nameSimilarity((string) $person['nome'], (string) $match['nome']);
        }
        unset($match);

        usort($matches, static function (array $first, array $second): int {
            $similarityOrder = $second['similarity'] <=> $first['similarity'];

            return $similarityOrder !== 0
                ? $similarityOrder
                : strcasecmp((string) $first['nome'], (string) $second['nome']);
        });

        return view('admin/person/join', [
            'title'   => 'Agrupar nomes',
            'person'  => $person,
            'words'   => $words,
            'matches' => $matches,
        ]);
    }

    public function processJoin(): RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $personId = (int) $this->request->getPost('person_id');
        $useId = (int) $this->request->getPost('use_id');
        $model = new PersonModel();
        $person = $model->find($personId);
        $selectedPerson = $model->find($useId);

        if ($person === null || $selectedPerson === null || $personId === $useId) {
            return redirect()->to(site_url('admin/person'))
                ->with('error', 'Não foi possível agrupar os nomes informados.');
        }

        $validMatches = array_column($this->matchingPeople($personId, (string) $person['nome']), 'id');

        if (! in_array($useId, array_map('intval', $validMatches), true)) {
            return redirect()->to(site_url('admin/person/join') . '?person_id=' . $personId)
                ->with('error', 'O nome selecionado não corresponde aos critérios de agrupamento.');
        }

        if (db_connect()->table('individuo')->where('id', $useId)->update(['use' => $personId]) === false) {
            return redirect()->to(site_url('admin/person/join') . '?person_id=' . $personId)
                ->with('error', 'Não foi possível salvar o agrupamento.');
        }

        return redirect()->to(site_url('admin/person/join') . '?person_id=' . $personId)
            ->with('success', $selectedPerson['nome'] . ' foi agrupado com este cadastro principal.');
    }

    /** @return list<string> */
    private function nameWords(string $name): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique($words));
    }

    /** @return array<int, array<string, mixed>> */
    private function matchingPeople(int $personId, string $name): array
    {
        $words = $this->nameWords($name);
        $builder = db_connect()->table('individuo')
            ->select('id')
            ->where('id !=', $personId)
            ->where('use', 0)
            ->groupStart();

        foreach ($words as $index => $word) {
            $index === 0 ? $builder->like('nome', $word) : $builder->orLike('nome', $word);
        }

        return $builder->groupEnd()->get()->getResultArray();
    }

    private function nameSimilarity(string $first, string $second): float
    {
        $first = $this->normalizeName($first);
        $second = $this->normalizeName($second);
        $maxLength = max(strlen($first), strlen($second));

        return $maxLength === 0
            ? 100.0
            : (1 - levenshtein($first, $second) / $maxLength) * 100;
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name), 'UTF-8');
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $name = $ascii === false ? $name : $ascii;
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name) ?? $name;

        return trim(preg_replace('/\s+/', ' ', $name) ?? $name);
    }

    private function requireLogin(): ?RedirectResponse
    {
        if (session()->get('auth_logged_in') === true) {
            return null;
        }

        return redirect()->to(site_url('login'))
            ->with('error', 'Faça login para acessar a administração de pessoas.');
    }

    private function isHeader(array $columns): bool
    {
        $firstColumn = mb_strtolower($columns[0] ?? '');
        $secondColumn = mb_strtolower($columns[1] ?? '');

        return $firstColumn === 'nome' && str_contains($secondColumn, 'lattes');
    }
}
