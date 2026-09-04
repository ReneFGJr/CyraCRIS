<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PersonModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

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
            return $model->like($field, $query);
        }

        return $model
            ->groupStart()
            ->like('nome', $query)
            ->orLike('email', $query)
            ->orLike('lattes_id', $query)
            ->orLike('orcid', $query)
            ->orLike('cpf', $query)
            ->orLike('cracha', $query)
            ->groupEnd();
    }

    public function inport(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        return view('admin/person/inport', [
            'title' => 'Importar pessoas',
        ]);
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

            if (count($columns) < 5) {
                $errors[] = 'Linha ' . ($index + 1) . ': informe Nome, ID Lattes, e-mail, CPF e crachá.';
                continue;
            }

            [$name, $lattesId, $email, $cpf, $cracha] = array_slice($columns, 0, 5);

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

            $imported++;
        }

        return redirect()->to(site_url('admin/person/inport'))->with('import_result', [
            'imported' => $imported,
            'errors'   => $errors,
        ]);
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
