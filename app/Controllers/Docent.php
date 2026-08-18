<?php

namespace App\Controllers;

use App\Services\LattesProductionImporter;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use DateTimeImmutable;
use RuntimeException;
use SimpleXMLElement;
use Throwable;
use ZipArchive;

class Docent extends BaseController
{
    private const LATTES_API = 'https://brapci.inf.br/ws/api/';

    public function show(int $id): string
    {
        $db = db_connect();
        $docente = $db->table('individuo')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($docente === null) {
            throw PageNotFoundException::forPageNotFound('Docente não encontrado.');
        }

        $instituicoes = $db->table('individuo_instituicao ii')
            ->select('inst.nome, inst.sigla, ii.tipo_vinculo, ii.principal')
            ->join('instituicao inst', 'inst.id = ii.instituicao_id')
            ->where('ii.individuo_id', $id)
            ->orderBy('ii.principal', 'DESC')
            ->orderBy('inst.nome', 'ASC')
            ->get()
            ->getResultArray();

        $linhas = $db->table('docentes_linhas_pesquisa dlp')
            ->select('lp.id, lp.nome, lp.area_concentracao, p.id AS programa_id, p.nome AS programa_nome, dlp.tipo_vinculo')
            ->join('linhas_pesquisa lp', 'lp.id = dlp.linha_pesquisa_id')
            ->join('programas_pos_graduacao p', 'p.id = lp.programa_id')
            ->where('dlp.docente_id', $id)
            ->orderBy('p.nome', 'ASC')
            ->orderBy('lp.nome', 'ASC')
            ->get()
            ->getResultArray();

        $orientacoes = $db->table('orientacoes o')
            ->select('o.tipo, o.status, o.ano_inicio, o.ano_final, o.titulo, e.id AS estudante_id, e.nome AS estudante_nome, e.lattes_id AS estudante_lattes_id')
            ->join('individuo e', 'e.id = o.estudante_id')
            ->where('o.orientador_id', $id)
            ->orderBy('o.status', 'ASC')
            ->orderBy('o.ano_final', 'DESC')
            ->orderBy('o.ano_inicio', 'DESC')
            ->get()
            ->getResultArray();

        $orientadores = $db->table('orientacoes o')
            ->select('o.tipo, o.status, o.ano_inicio, o.ano_final, o.titulo, i.id AS orientador_id, i.nome AS orientador_nome')
            ->join('individuo i', 'i.id = o.orientador_id')
            ->where('o.estudante_id', $id)
            ->orderBy('o.status', 'ASC')
            ->orderBy('o.ano_final', 'DESC')
            ->orderBy('o.ano_inicio', 'DESC')
            ->get()
            ->getResultArray();

        $producoes = $db->table('producoes p')
            ->select('p.*, s.tipo AS source_tipo, s.nome AS source_nome, s.issn AS source_issn, s.isbn AS source_isbn, s.editora AS source_editora')
            ->join('source s', 's.id = p.source_id', 'left')
            ->where('p.pesquisador_id', $id)
            ->orderBy('p.ano', 'DESC')
            ->orderBy('p.titulo', 'ASC')
            ->get()
            ->getResultArray();

        $redeIndividual = $this->montarRedeIndividual($docente, $producoes);

        return view('docent/show', [
            'docente'      => $docente,
            'instituicoes' => $instituicoes,
            'linhas'       => $linhas,
            'orientacoes'  => $orientacoes,
            'orientadores' => $orientadores,
            'producoes'    => $producoes,
            'redeIndividual' => $redeIndividual,
            'coletaLattesHabilitada' => filter_var(env('lattes.collectionEnabled', false), FILTER_VALIDATE_BOOL),
        ]);
    }

    public function atualizar(int $id): RedirectResponse
    {
        if (! filter_var(env('lattes.collectionEnabled', false), FILTER_VALIDATE_BOOL)) {
            return redirect()->to(site_url('docent/' . $id))
                ->with('erro', 'A coleta de dados do Lattes está temporariamente desabilitada.');
        }

        $db = db_connect();
        $docente = $db->table('individuo')->where('id', $id)->get()->getRowArray();

        if ($docente === null) {
            throw PageNotFoundException::forPageNotFound('Docente não encontrado.');
        }

        $lattesId = trim((string) ($docente['lattes_id'] ?? ''));

        if (preg_match('/^\d{16}$/', $lattesId) !== 1) {
            return redirect()->to(site_url('docent/' . $id))
                ->with('erro', 'O docente não possui um ID Lattes válido para atualização.');
        }

        $arquivoTemporario = null;

        try {
            $token = trim((string) env('lattes.apiToken'));

            if ($token === '') {
                throw new RuntimeException('O token do serviço Lattes não está configurado.');
            }

            $verificarSsl = filter_var(env('lattes.verifySsl', true), FILTER_VALIDATE_BOOL);
            $caBundle = trim((string) env('lattes.caBundle'));

            if ($verificarSsl && ($caBundle === '' || ! is_file($caBundle))) {
                throw new RuntimeException('O pacote de certificados SSL do serviço Lattes não está configurado.');
            }

            $response = service('curlrequest')->get(self::LATTES_API, [
                'query' => [
                    'verb'  => 'lattes',
                    'q'     => $lattesId,
                    'token' => $token,
                ],
                'connect_timeout' => 15,
                'timeout'         => 90,
                'http_errors'     => false,
                'verify'          => $verificarSsl ? $caBundle : false,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException('O serviço Lattes respondeu com HTTP ' . $response->getStatusCode() . '.');
            }

            $arquivoTemporario = tempnam(WRITEPATH, 'lattes_');

            if ($arquivoTemporario === false || file_put_contents($arquivoTemporario, $response->getBody(), LOCK_EX) === false) {
                throw new RuntimeException('Não foi possível salvar o arquivo temporário.');
            }

            [$xml, $nomeArquivoXml] = $this->lerXmlDoZip($arquivoTemporario, $lattesId);
            $dados = $this->extrairDados($xml);

            $diretorioRepositorio = FCPATH . '_repository';

            if (! is_dir($diretorioRepositorio) && ! mkdir($diretorioRepositorio, 0775, true) && ! is_dir($diretorioRepositorio)) {
                throw new RuntimeException('Não foi possível criar o diretório de arquivos Lattes.');
            }

            $arquivoDestino = $diretorioRepositorio . DIRECTORY_SEPARATOR . $lattesId . '.zip';

            if (! copy($arquivoTemporario, $arquivoDestino)) {
                throw new RuntimeException('Não foi possível armazenar o ZIP no repositório.');
            }

            $db->transStart();
            $db->table('individuo')->where('id', $id)->update(array_merge($dados['docente'], [
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
            $this->atualizarInstituicao($id, $dados['instituicao']);
            $this->atualizarOrientacoes($id, $xml);
            (new LattesProductionImporter())->importar($id, $xml);
            $db->transComplete();

            if (! $db->transStatus()) {
                throw new RuntimeException('Não foi possível gravar os dados importados.');
            }

            log_message('info', 'Lattes {lattesId} atualizado pelo arquivo {arquivo}.', [
                'lattesId' => $lattesId,
                'arquivo'  => $nomeArquivoXml,
            ]);

            return redirect()->to(site_url('docent/' . $id))
                ->with('sucesso', 'Dados do docente atualizados pelo currículo Lattes.');
        } catch (Throwable $e) {
            log_message('error', 'Falha ao atualizar o docente {id}: {erro}', ['id' => $id, 'erro' => $e->getMessage()]);

            return redirect()->to(site_url('docent/' . $id))
                ->with('erro', 'Não foi possível atualizar o docente: ' . $e->getMessage());
        } finally {
            if ($arquivoTemporario !== null && is_file($arquivoTemporario)) {
                unlink($arquivoTemporario);
            }
        }
    }

    public function editar(int $id): RedirectResponse
    {
        $db = db_connect();

        if ($db->table('individuo')->where('id', $id)->countAllResults() === 0) {
            throw PageNotFoundException::forPageNotFound('Docente não encontrado.');
        }

        $nome = trim((string) $this->request->getPost('nome'));
        $genero = (int) $this->request->getPost('genero');
        $email = trim((string) $this->request->getPost('email'));
        $lattesId = trim((string) $this->request->getPost('lattes_id'));
        $orcid = trim((string) $this->request->getPost('orcid'));
        $vinculos = $this->request->getPost('vinculos');
        $vinculos = is_array($vinculos) ? $vinculos : [];

        $erros = [];
        if ($nome === '' || mb_strlen($nome) > 255) {
            $erros[] = 'Informe um nome válido com até 255 caracteres.';
        }
        if (! in_array($genero, [0, 1, 2], true)) {
            $erros[] = 'Selecione um gênero válido.';
        }
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $erros[] = 'Informe um e-mail válido.';
        }
        if ($lattesId !== '' && preg_match('/^\d{16}$/', $lattesId) !== 1) {
            $erros[] = 'O ID Lattes deve conter 16 dígitos.';
        }
        if ($orcid !== '' && preg_match('/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/i', $orcid) !== 1) {
            $erros[] = 'Informe o ORCID no formato 0000-0000-0000-0000.';
        }
        foreach ($vinculos as $tipoVinculo) {
            if (! in_array($tipoVinculo, ['PERMANENTE', 'COLABORADOR'], true)) {
                $erros[] = 'Selecione um tipo de vínculo docente válido.';
                break;
            }
        }

        if ($erros !== []) {
            return redirect()->to(site_url('docent/' . $id))
                ->with('erro', implode(' ', $erros))
                ->withInput();
        }

        $db->transStart();
        $db->table('individuo')->where('id', $id)->update([
            'nome'       => $nome,
            'genero'     => $genero,
            'email'      => $email !== '' ? $email : null,
            'lattes_id'  => $lattesId !== '' ? $lattesId : null,
            'orcid'      => $orcid !== '' ? strtoupper($orcid) : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        foreach ($vinculos as $linhaId => $tipoVinculo) {
            if (! ctype_digit((string) $linhaId)) {
                continue;
            }
            $db->table('docentes_linhas_pesquisa')
                ->where('docente_id', $id)
                ->where('linha_pesquisa_id', (int) $linhaId)
                ->update(['tipo_vinculo' => $tipoVinculo]);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('docent/' . $id))->with('erro', 'Não foi possível salvar as alterações.');
        }

        return redirect()->to(site_url('docent/' . $id))->with('sucesso', 'Dados do docente atualizados manualmente.');
    }

    /** @return array{0: SimpleXMLElement, 1: string} */
    private function lerXmlDoZip(string $arquivoZip, string $lattesId): array
    {
        $zip = new ZipArchive();

        if ($zip->open($arquivoZip) !== true) {
            throw new RuntimeException('O serviço não retornou um arquivo ZIP válido.');
        }

        $indiceXml = $zip->locateName($lattesId . '.xml', ZipArchive::FL_NOCASE);

        if ($indiceXml === false) {
            for ($indice = 0; $indice < $zip->numFiles; $indice++) {
                $nome = (string) $zip->getNameIndex($indice);

                if (str_ends_with(strtolower($nome), '.xml')) {
                    $indiceXml = $indice;
                    break;
                }
            }
        }

        if ($indiceXml === false) {
            $zip->close();
            throw new RuntimeException('O ZIP não contém um currículo em XML.');
        }

        $nomeArquivoXml = (string) $zip->getNameIndex($indiceXml);
        $conteudoXml = $zip->getFromIndex($indiceXml);
        $zip->close();

        if ($conteudoXml === false) {
            throw new RuntimeException('Não foi possível descompactar o XML do currículo.');
        }

        $xml = simplexml_load_string($conteudoXml);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('O XML do currículo é inválido.');
        }

        if ((string) $xml['NUMERO-IDENTIFICADOR'] !== $lattesId) {
            throw new RuntimeException('O currículo retornado não corresponde ao ID Lattes do docente.');
        }

        return [$xml, $nomeArquivoXml];
    }

    /** @return array{docente: array<string, string>, instituicao: array<string, string>} */
    private function extrairDados(SimpleXMLElement $xml): array
    {
        $dadosGerais = $xml->{'DADOS-GERAIS'};

        if (! $dadosGerais instanceof SimpleXMLElement) {
            throw new RuntimeException('O currículo não contém dados gerais.');
        }

        $docente = ['nome' => trim((string) $dadosGerais['NOME-COMPLETO'])];
        $dataAtualizacao = trim((string) $xml['DATA-ATUALIZACAO']);
        $horaAtualizacao = trim((string) $xml['HORA-ATUALIZACAO']);
        $atualizacaoLattes = DateTimeImmutable::createFromFormat('!dmY His', $dataAtualizacao . ' ' . str_pad($horaAtualizacao, 6, '0', STR_PAD_LEFT));

        if ($atualizacaoLattes instanceof DateTimeImmutable) {
            $docente['lattes_updated_at'] = $atualizacaoLattes->format('Y-m-d H:i:s');
        }
        $orcid = preg_replace('#^https?://orcid\.org/#i', '', trim((string) $dadosGerais['ORCID-ID']));

        if ($orcid !== '' && preg_match('/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/i', $orcid) === 1) {
            $docente['orcid'] = strtoupper($orcid);
        }

        $endereco = $dadosGerais->ENDERECO;
        $eletronico = trim((string) ($endereco['ELETRONICO'] ?? ''));

        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $eletronico, $email) === 1) {
            $docente['email'] = $email[0];
        }

        $profissional = $endereco->{'ENDERECO-PROFISSIONAL'};
        $instituicao = [];

        if ($profissional instanceof SimpleXMLElement) {
            $instituicao = [
                'nome'     => trim((string) $profissional['NOME-INSTITUICAO-EMPRESA']),
                'telefone' => trim(implode(' ', array_filter([
                    (string) $profissional['DDD'],
                    (string) $profissional['TELEFONE'],
                ]))),
                'website'  => trim((string) $profissional['HOME-PAGE']),
            ];
        }

        return ['docente' => array_filter($docente, static fn ($valor) => $valor !== ''), 'instituicao' => $instituicao];
    }

    /** @param array<string, string> $dados */
    private function atualizarInstituicao(int $docenteId, array $dados): void
    {
        if (($dados['nome'] ?? '') === '') {
            return;
        }

        $db = db_connect();
        $instituicao = $db->table('instituicao')->select('id')->where('nome', $dados['nome'])->get()->getRowArray();

        if ($instituicao === null) {
            $agora = date('Y-m-d H:i:s');
            $db->table('instituicao')->insert(array_merge($dados, ['created_at' => $agora, 'updated_at' => $agora]));
            $instituicaoId = (int) $db->insertID();
        } else {
            $instituicaoId = (int) $instituicao['id'];
            $db->table('instituicao')->where('id', $instituicaoId)->update(array_merge($dados, ['updated_at' => date('Y-m-d H:i:s')]));
        }

        $db->table('individuo')->where('id', $docenteId)->update(['instituicao' => $instituicaoId]);
        $vinculoExiste = $db->table('individuo_instituicao')
            ->where('individuo_id', $docenteId)
            ->where('instituicao_id', $instituicaoId)
            ->countAllResults() > 0;

        if (! $vinculoExiste) {
            $db->table('individuo_instituicao')->insert([
                'individuo_id'  => $docenteId,
                'instituicao_id'=> $instituicaoId,
                'tipo_vinculo'  => 'VINCULADO',
                'principal'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function atualizarOrientacoes(int $orientadorId, SimpleXMLElement $xml): void
    {
        foreach ([0 => '//ORIENTACOES-EM-ANDAMENTO/*', 1 => '//ORIENTACOES-CONCLUIDAS/*'] as $status => $xpath) {
            foreach ($xml->xpath($xpath) ?: [] as $orientacaoXml) {
                $elementos = $orientacaoXml->children();
                $dadosBasicos = $elementos[0] ?? null;
                $detalhamento = $elementos[1] ?? null;

                if (! $dadosBasicos instanceof SimpleXMLElement || ! $detalhamento instanceof SimpleXMLElement) {
                    continue;
                }

                $nome = trim((string) ($detalhamento['NOME-DO-ORIENTADO'] ?: $detalhamento['NOME-DO-ORIENTANDO']));

                if ($nome === '') {
                    continue;
                }

                $lattesId = trim((string) $detalhamento['NUMERO-ID-ORIENTADO']);
                $lattesId = preg_match('/^\d{16}$/', $lattesId) === 1 ? $lattesId : null;
                $estudanteId = $this->localizarOuCriarEstudante($nome, $lattesId);

                if ($estudanteId === $orientadorId) {
                    log_message('warning', 'Orientação própria ignorada para o indivíduo {id}: {titulo}', [
                        'id'     => $orientadorId,
                        'titulo' => (string) ($dadosBasicos['TITULO'] ?: $dadosBasicos['TITULO-DO-TRABALHO']),
                    ]);
                    continue;
                }

                $tipo = $this->classificarOrientacao($orientacaoXml->getName(), (string) $dadosBasicos['NATUREZA']);
                $ano = (int) $dadosBasicos['ANO'];
                $agora = date('Y-m-d H:i:s');
                $dados = [
                    'status'     => $status,
                    'ano_inicio' => $status === 0 && $ano > 0 ? $ano : null,
                    'ano_final'  => $status === 1 && $ano > 0 ? $ano : null,
                    'titulo'     => trim((string) ($dadosBasicos['TITULO'] ?: $dadosBasicos['TITULO-DO-TRABALHO'])),
                    'updated_at' => $agora,
                ];

                $existente = db_connect()->table('orientacoes')
                    ->select('id, ano_inicio')
                    ->where('orientador_id', $orientadorId)
                    ->where('estudante_id', $estudanteId)
                    ->where('tipo', $tipo)
                    ->get()
                    ->getRowArray();

                if ($existente === null) {
                    db_connect()->table('orientacoes')->insert(array_merge($dados, [
                        'orientador_id' => $orientadorId,
                        'estudante_id'  => $estudanteId,
                        'tipo'          => $tipo,
                        'created_at'    => $agora,
                    ]));
                } else {
                    if ($status === 1 && $dados['ano_inicio'] === null && ! empty($existente['ano_inicio'])) {
                        $dados['ano_inicio'] = (int) $existente['ano_inicio'];
                    }

                    db_connect()->table('orientacoes')->where('id', $existente['id'])->update($dados);
                }
            }
        }
    }

    private function localizarOuCriarEstudante(string $nome, ?string $lattesId): int
    {
        $db = db_connect();
        $builder = $db->table('individuo')->select('id');
        $estudante = $lattesId !== null
            ? $builder->where('lattes_id', $lattesId)->get()->getRowArray()
            : $builder->where('nome', $nome)->get()->getRowArray();

        if ($estudante !== null) {
            return (int) $estudante['id'];
        }

        $agora = date('Y-m-d H:i:s');
        $db->table('individuo')->insert([
            'nome'       => $nome,
            'lattes_id'  => $lattesId,
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);

        return (int) $db->insertID();
    }

    private function classificarOrientacao(string $elemento, string $natureza): string
    {
        $valor = strtoupper($elemento . ' ' . $natureza);

        return match (true) {
            str_contains($valor, 'POS-DOUTORADO'),
            str_contains($valor, 'POS_DOUTORADO') => 'Pos-doc',
            str_contains($valor, 'DOUTORADO') => 'Doutorado',
            str_contains($valor, 'MESTRADO') => 'Mestrado',
            str_contains($valor, 'INICIACAO-CIENTIFICA'),
            str_contains($valor, 'INICIACAO_CIENTIFICA') => 'Iniciação científica',
            str_contains($valor, 'GRADUACAO') => 'TCC (Graduação)',
            str_contains($valor, 'ESPECIALIZACAO'),
            str_contains($valor, 'APERFEICOAMENTO') => 'Especialização',
            default => 'Outras',
        };
    }

    /**
     * @param array<string, mixed> $docente
     * @param array<int, array<string, mixed>> $producoes
     * @return array{nodes: array<int, array<string, mixed>>, links: array<int, array<string, mixed>>}
     */
    private function montarRedeIndividual(array $docente, array $producoes): array
    {
        $idCentral = (int) $docente['id'];
        $nomeCentral = $this->normalizarNome((string) $docente['nome']);
        $nodes = [$idCentral => ['id' => $idCentral, 'nome' => $docente['nome'], 'grupo' => 'central']];
        $links = [];

        $individuos = db_connect()->table('individuo')->select('id, nome')->get()->getResultArray();
        $porNome = [];
        foreach ($individuos as $individuo) {
            $porNome[$this->normalizarNome((string) $individuo['nome'])] = $individuo;
        }
        $porNome[$nomeCentral] = ['id' => $idCentral, 'nome' => $docente['nome']];
        foreach ($producoes as $producao) {
            foreach (explode(';', (string) ($producao['autores'] ?? '')) as $autor) {
                $nome = $this->normalizarNome($autor);
                if (! isset($porNome[$nome])) {
                    continue;
                }
                $coautor = $porNome[$nome];
                $id = (int) $coautor['id'];
                if ($id === $idCentral) {
                    continue;
                }
                $nodes[$id] ??= ['id' => $id, 'nome' => $coautor['nome'], 'grupo' => 'coautor'];
                $chave = 'producao-' . $id;
                $links[$chave] = ['source' => $idCentral, 'target' => $id, 'peso' => ($links[$chave]['peso'] ?? 0) + 1, 'tipo' => 'producao'];
            }
        }

        return ['nodes' => array_values($nodes), 'links' => array_values($links)];
    }

    private function normalizarNome(string $nome): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($nome));
        return preg_replace('/[^a-z0-9]+/', ' ', strtolower($ascii !== false ? $ascii : $nome)) ?? '';
    }
}
