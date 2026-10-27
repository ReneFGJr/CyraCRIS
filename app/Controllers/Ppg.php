<?php

namespace App\Controllers;

use App\Models\ProgramaPosGraduacaoModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Ppg extends BaseController
{
    public function index(): string
    {
        $model = new ProgramaPosGraduacaoModel();

        return view('ppg/index', [
            'title'    => 'Programas de Pós-Graduação',
            'programas'=> $model->orderBy('nome', 'ASC')->findAll(),
        ]);
    }

    public function new(): string|RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login como administrador para cadastrar programas.');
        }

        return view('ppg/form', ['title' => 'Novo programa']);
    }

    public function import(): ResponseInterface
    {
        if (session()->get('auth_logged_in') !== true) {
            return $this->response->setStatusCode(401)->setJSON([
                'message' => 'Faça login como administrador para importar programas.',
                'csrf'    => ['name' => csrf_token(), 'hash' => csrf_hash()],
            ]);
        }

        $codigo = strtoupper(trim((string) $this->request->getPost('codigo_capes')));
        $csrf = ['name' => csrf_token(), 'hash' => csrf_hash()];

        if (preg_match('/^\d{11}P\d$/', $codigo) !== 1) {
            return $this->response->setStatusCode(422)->setJSON([
                'message' => 'Informe um código CAPES válido, como 42001013173P0.',
                'csrf'    => $csrf,
            ]);
        }

        $model = new ProgramaPosGraduacaoModel();
        if ($model->where('codigo_capes', $codigo)->first() !== null) {
            return $this->response->setStatusCode(409)->setJSON([
                'message' => 'Este código já está cadastrado no sistema.',
                'csrf'    => $csrf,
            ]);
        }

        try {
            $response = service('curlrequest')->get('https://apigw-proxy.capes.gov.br/observatorio/data/observatorio/ppg', [
                'query' => [
                    'query' => 'codigo:(' . $codigo . ')',
                    'page'  => 0,
                    'size'  => 10,
                ],
                'headers'     => ['Accept' => 'application/json'],
                'http_errors' => false,
                'timeout'     => 15,
                'verify'      => env('auth.caBundle', true),
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Falha ao consultar programa CAPES {codigo}: {message}', [
                'codigo'  => $codigo,
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setStatusCode(502)->setJSON([
                'message' => 'Não foi possível consultar a CAPES agora. Tente novamente mais tarde.',
                'csrf'    => $csrf,
            ]);
        }

        $resultado = json_decode((string) $response->getBody(), true);
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300 || ! is_array($resultado)) {
            log_message('error', 'Resposta inválida da CAPES ao consultar programa {codigo}; status HTTP {status}.', [
                'codigo' => $codigo,
                'status' => $response->getStatusCode(),
            ]);

            return $this->response->setStatusCode(502)->setJSON([
                'message' => 'A consulta à CAPES falhou. Tente novamente mais tarde.',
                'csrf'    => $csrf,
            ]);
        }

        $programa = null;
        $idPrograma = 0;
        $itens = is_array($resultado['content'] ?? null) ? $resultado['content'] : [];
        foreach ($itens as $item) {
            if (is_array($item) && strtoupper((string) ($item['codigo'] ?? '')) === $codigo) {
                $programa = [
                    'codigo_capes'      => $codigo,
                    'nome'              => (string) ($item['nome'] ?? ''),
                    'instituicao_sigla' => (string) ($item['siglaIes'] ?? ''),
                    'nota_capes'        => strtoupper((string) ($item['conceito'] ?? '')),
                    'modalidade'        => (string) ($item['modalidade'] ?? ''),
                    'situacao'          => (string) ($item['situacao'] ?? ''),
                ];
                $idPrograma = (int) ($item['idPrograma'] ?? 0);
                break;
            }
        }

        if ($programa === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'message' => 'Código não encontrado na CAPES. Confira o código e tente novamente. Exemplo: 42001013173P0.',
                'csrf'    => $csrf,
            ]);
        }

        if ($idPrograma < 1) {
            log_message('error', 'A CAPES não retornou um ID válido para o programa {codigo}.', ['codigo' => $codigo]);

            return $this->response->setStatusCode(502)->setJSON([
                'message' => 'A CAPES encontrou o programa, mas não retornou os dados necessários para importar os detalhes.',
                'csrf'    => $csrf,
            ]);
        }

        try {
            $respostaDetalhes = service('curlrequest')->get(
                'https://apigw-proxy.capes.gov.br/observatorio/data/online/ppg/' . $idPrograma,
                [
                    'headers'     => ['Accept' => 'application/json'],
                    'http_errors' => false,
                    'timeout'     => 15,
                    'verify'      => env('auth.caBundle', true),
                ],
            );
        } catch (Throwable $exception) {
            log_message('error', 'Falha ao consultar detalhes do programa CAPES {codigo}: {message}', [
                'codigo'  => $codigo,
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setStatusCode(502)->setJSON([
                'message' => 'A CAPES encontrou o programa, mas não foi possível carregar os dados completos. Tente novamente mais tarde.',
                'csrf'    => $csrf,
            ]);
        }

        $detalhes = json_decode((string) $respostaDetalhes->getBody(), true);
        if ($respostaDetalhes->getStatusCode() < 200 || $respostaDetalhes->getStatusCode() >= 300
            || ! is_array($detalhes)
            || strtoupper((string) ($detalhes['codigo'] ?? '')) !== $codigo
            || ! is_array($detalhes['online'] ?? null)) {
            log_message('error', 'Resposta inválida ao carregar detalhes CAPES do programa {codigo}; status HTTP {status}.', [
                'codigo' => $codigo,
                'status' => $respostaDetalhes->getStatusCode(),
            ]);

            return $this->response->setStatusCode(502)->setJSON([
                'message' => 'A CAPES encontrou o programa, mas não retornou os dados completos esperados. Tente novamente mais tarde.',
                'csrf'    => $csrf,
            ]);
        }

        $detalhesOnline = $detalhes['online'];
        $programa['ano_inicio'] = (string) ($detalhesOnline['anoInicio'] ?? '');
        $programa['instituicao_codigo'] = (string) ($detalhesOnline['idIes'] ?? '');
        $programa['instituicao_nome'] = '';
        $programa['telefone'] = '';
        $programa['email'] = (string) ($detalhesOnline['contatoEmail'] ?? '');
        $programa['website'] = (string) ($detalhesOnline['contatoUrl'] ?? '');

        $instituicoes = is_array($detalhesOnline['instituicoes'] ?? null) ? $detalhesOnline['instituicoes'] : [];
        foreach ($instituicoes as $instituicao) {
            if (is_array($instituicao)
                && ((int) ($instituicao['principal'] ?? 0) === 1 || $programa['instituicao_nome'] === '')) {
                $programa['instituicao_nome'] = (string) ($instituicao['nomeIes'] ?? $programa['instituicao_nome']);
                $programa['instituicao_codigo'] = (string) ($instituicao['idIes'] ?? $programa['instituicao_codigo']);
                $programa['instituicao_sigla'] = (string) ($instituicao['siglaIes'] ?? $programa['instituicao_sigla']);

                if ((int) ($instituicao['principal'] ?? 0) === 1) {
                    break;
                }
            }
        }

        $ddd = trim((string) ($detalhesOnline['telDdd'] ?? ''));
        $numeroTelefone = trim((string) ($detalhesOnline['telNumero'] ?? ''));
        $ramal = trim((string) ($detalhesOnline['telRamal'] ?? ''));
        if ($numeroTelefone !== '') {
            $programa['telefone'] = trim(($ddd !== '' ? '(' . $ddd . ') ' : '') . $numeroTelefone . ($ramal !== '' ? ' ramal ' . $ramal : ''));
        }

        if ($programa['nome'] === '') {
            log_message('error', 'A CAPES retornou dados incompletos para o programa {codigo}.', ['codigo' => $codigo]);

            return $this->response->setStatusCode(502)->setJSON([
                'message' => 'A CAPES retornou dados incompletos para este programa. Tente novamente mais tarde.',
                'csrf'    => $csrf,
            ]);
        }

        $camposPendentes = array_filter(
            ['ano_inicio', 'telefone', 'email', 'website', 'instituicao_nome'],
            static fn (string $campo): bool => trim((string) ($programa[$campo] ?? '')) === '',
        );

        return $this->response->setJSON([
            'message' => $camposPendentes === []
                ? 'Dados atualizados do programa e da instituição encontrados na CAPES. Confira antes de cadastrar.'
                : 'Dados atualizados encontrados. Alguns campos não estão disponíveis na CAPES e devem ser preenchidos manualmente: ' . implode(', ', $camposPendentes) . '.',
            'programa' => $programa,
            'csrf'     => $csrf,
            'warning'  => $camposPendentes !== [],
        ]);
    }

    public function create(): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login como administrador para cadastrar programas.');
        }

        $data = [];
        foreach ([
            'codigo_capes',
            'nome',
            'instituicao_codigo',
            'instituicao_nome',
            'instituicao_sigla',
            'telefone',
            'email',
            'website',
            'ano_inicio',
            'situacao',
            'nota_capes',
            'modalidade',
        ] as $field) {
            $data[$field] = trim((string) $this->request->getPost($field));
        }
        $data['nota_capes'] = strtoupper($data['nota_capes']);

        $rules = [
            'codigo_capes'       => 'required|max_length[20]|is_unique[programas_pos_graduacao.codigo_capes]',
            'nome'               => 'required|max_length[255]',
            'instituicao_codigo' => 'permit_empty|is_natural|less_than_equal_to[4294967295]',
            'instituicao_nome'   => 'permit_empty|max_length[255]',
            'instituicao_sigla'  => 'permit_empty|max_length[30]',
            'telefone'           => 'permit_empty|max_length[30]',
            'email'              => 'permit_empty|valid_email|max_length[190]',
            'website'            => 'permit_empty|valid_url_strict|max_length[255]',
            'ano_inicio'         => 'permit_empty|exact_length[4]|integer|greater_than_equal_to[1900]|less_than_equal_to[2100]',
            'situacao'           => 'permit_empty|max_length[50]',
            'nota_capes'         => 'permit_empty|in_list[1,2,3,4,5,6,7,A]',
            'modalidade'         => 'permit_empty|max_length[30]',
        ];

        if (! $this->validateData($data, $rules)) {
            return redirect()->to(site_url('ppg/new'))
                ->withInput()
                ->with('errors', array_values($this->validator->getErrors()));
        }

        foreach ([
            'instituicao_codigo',
            'instituicao_nome',
            'instituicao_sigla',
            'telefone',
            'email',
            'website',
            'ano_inicio',
            'situacao',
            'nota_capes',
            'modalidade',
        ] as $field) {
            if ($data[$field] === '') {
                $data[$field] = null;
            }
        }

        $model = new ProgramaPosGraduacaoModel();
        $id = $model->insert($data);

        if ($id === false) {
            $errors = array_values($model->errors());

            return redirect()->to(site_url('ppg/new'))
                ->withInput()
                ->with('errors', $errors !== [] ? $errors : ['Não foi possível cadastrar o programa.']);
        }

        return redirect()->to(site_url('ppg/' . $id))
            ->with('success', 'Programa cadastrado com sucesso.');
    }

    public function syncCapes(int $id): ResponseInterface
    {
        if (session()->get('auth_logged_in') !== true) {
            return $this->response->setStatusCode(401)->setJSON([
                'message' => 'Faça login como administrador para atualizar os dados pela CAPES.',
                'csrf'    => ['name' => csrf_token(), 'hash' => csrf_hash()],
            ]);
        }

        $programa = (new ProgramaPosGraduacaoModel())->find($id);
        if ($programa === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'message' => 'Programa não encontrado.',
                'csrf'    => ['name' => csrf_token(), 'hash' => csrf_hash()],
            ]);
        }

        $codigo = strtoupper(trim((string) ($programa['codigo_capes'] ?? '')));
        $csrf = ['name' => csrf_token(), 'hash' => csrf_hash()];
        $db = null;
        $transacaoIniciada = false;

        try {
            $respostaLista = service('curlrequest')->get('https://apigw-proxy.capes.gov.br/observatorio/data/observatorio/ppg', [
                'query' => [
                    'query' => 'codigo:(' . $codigo . ')',
                    'page'  => 0,
                    'size'  => 10,
                ],
                'headers'     => ['Accept' => 'application/json'],
                'http_errors' => false,
                'timeout'     => 15,
                'verify'      => env('auth.caBundle', true),
            ]);

            $resultadoLista = json_decode((string) $respostaLista->getBody(), true);
            if ($respostaLista->getStatusCode() < 200 || $respostaLista->getStatusCode() >= 300
                || ! is_array($resultadoLista)) {
                throw new \RuntimeException('A consulta CAPES do programa falhou.');
            }

            $idCapes = 0;
            foreach (is_array($resultadoLista['content'] ?? null) ? $resultadoLista['content'] : [] as $item) {
                if (is_array($item) && strtoupper((string) ($item['codigo'] ?? '')) === $codigo) {
                    $idCapes = (int) ($item['idPrograma'] ?? 0);
                    break;
                }
            }

            if ($idCapes < 1) {
                return $this->response->setStatusCode(404)->setJSON([
                    'message' => 'O programa não foi encontrado na CAPES pelo código ' . $codigo . '.',
                    'csrf'    => $csrf,
                ]);
            }

            $respostaDetalhes = service('curlrequest')->get(
                'https://apigw-proxy.capes.gov.br/observatorio/data/online/ppg/' . $idCapes,
                [
                    'headers'     => ['Accept' => 'application/json'],
                    'http_errors' => false,
                    'timeout'     => 15,
                    'verify'      => env('auth.caBundle', true),
                ],
            );
            $detalhes = json_decode((string) $respostaDetalhes->getBody(), true);

            if ($respostaDetalhes->getStatusCode() < 200 || $respostaDetalhes->getStatusCode() >= 300
                || ! is_array($detalhes)
                || strtoupper((string) ($detalhes['codigo'] ?? '')) !== $codigo
                || ! is_array($detalhes['online'] ?? null)) {
                throw new \RuntimeException('A CAPES retornou dados incompletos para a sincronização.');
            }

            $dadosOnline = $detalhes['online'];
            foreach (['areasConcentracao', 'linhasPesquisa', 'cursos'] as $campo) {
                if (! is_array($dadosOnline[$campo] ?? null)) {
                    throw new \RuntimeException('A CAPES não retornou a lista esperada: ' . $campo . '.');
                }
            }

            $areas = [];
            foreach ($dadosOnline['areasConcentracao'] as $area) {
                if (is_array($area) && trim((string) ($area['nome'] ?? '')) !== '') {
                    $areas[] = trim((string) $area['nome']);
                }
            }
            $areas = array_values(array_unique($areas));

            $modalidades = [];
            foreach ($dadosOnline['cursos'] as $curso) {
                if (! is_array($curso)) {
                    continue;
                }

                $grau = mb_strtolower(trim((string) ($curso['grau'] ?? '')));
                foreach (['mestrado' => 'Mestrado', 'doutorado' => 'Doutorado'] as $termo => $modalidade) {
                    if (str_contains($grau, $termo)) {
                        $modalidades[] = $modalidade;
                    }
                }
            }
            $modalidades = array_values(array_unique($modalidades));

            $linhasPorNome = [];
            foreach ($dadosOnline['linhasPesquisa'] as $linha) {
                if (! is_array($linha) || trim((string) ($linha['nome'] ?? '')) === '') {
                    continue;
                }

                $nome = trim((string) $linha['nome']);
                $inicio = substr(trim((string) ($linha['inicio'] ?? '')), 0, 10);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio) !== 1
                    || ! checkdate((int) substr($inicio, 5, 2), (int) substr($inicio, 8, 2), (int) substr($inicio, 0, 4))) {
                    $inicio = null;
                }

                $linhasPorNome[$nome] = [
                    'nome'              => $nome,
                    'area_concentracao' => trim((string) ($linha['nomeAreaConcentracao'] ?? '')) ?: null,
                    'inicio'            => $inicio,
                ];
            }

            $db = db_connect();
            $agora = date('Y-m-d H:i:s');
            if (! $db->transBegin()) {
                throw new \RuntimeException('Não foi possível iniciar a transação do banco de dados.');
            }
            $transacaoIniciada = true;

            $programaAtualizado = $db->table('programas_pos_graduacao')
                ->where('id', $id)
                ->update([
                    'sucupira_id'        => $idCapes,
                    'areas_concentracao' => json_encode($areas, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'linhas_pesquisa'    => json_encode(array_values(array_column($linhasPorNome, 'nome')), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'cursos'             => json_encode($modalidades, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'graus'              => json_encode($modalidades, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'updated_at'         => $agora,
                ]);

            if (! $programaAtualizado) {
                throw new \RuntimeException('Não foi possível atualizar os dados do programa.');
            }

            $tabelaLinhas = $db->table('linhas_pesquisa');
            foreach ($linhasPorNome as $dadosLinha) {
                $existente = $db->table('linhas_pesquisa')
                    ->where('programa_id', $id)
                    ->where('nome', $dadosLinha['nome'])
                    ->get()
                    ->getRowArray();
                $dadosLinha['programa_id'] = $id;
                $dadosLinha['updated_at'] = $agora;

                if ($existente === null) {
                    $dadosLinha['created_at'] = $agora;
                    if (! $tabelaLinhas->insert($dadosLinha)) {
                        throw new \RuntimeException('Não foi possível inserir a linha de pesquisa ' . $dadosLinha['nome'] . '.');
                    }
                } elseif (! $tabelaLinhas->where('id', $existente['id'])->update($dadosLinha)) {
                    throw new \RuntimeException('Não foi possível atualizar a linha de pesquisa ' . $dadosLinha['nome'] . '.');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('A transação do banco de dados falhou.');
            }
            if (! $db->transCommit()) {
                throw new \RuntimeException('Não foi possível confirmar a atualização no banco de dados.');
            }
            $transacaoIniciada = false;
        } catch (Throwable $exception) {
            if ($transacaoIniciada && $db !== null) {
                $db->transRollback();
            }

            log_message('error', 'Falha ao sincronizar programa {id} com CAPES: {message}', [
                'id'      => $id,
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setStatusCode(502)->setJSON([
                'message' => 'Não foi possível atualizar os dados pela CAPES. Tente novamente mais tarde.',
                'csrf'    => $csrf,
            ]);
        }

        return $this->response->setJSON([
            'message' => 'Estrutura do programa atualizada pela CAPES: ' . count($areas) . ' áreas, '
                . count($linhasPorNome) . ' linhas de pesquisa e ' . count($modalidades) . ' modalidades de curso. Os vínculos dos docentes foram preservados.',
            'csrf'    => $csrf,
        ]);
    }

    public function show(int $id): string
    {
        $programa = (new ProgramaPosGraduacaoModel())->find($id);

        if ($programa === null) {
            throw PageNotFoundException::forPageNotFound('Programa de pós-graduação não encontrado.');
        }

        $listas = [];

        foreach (['graus', 'cursos', 'areas_concentracao', 'linhas_pesquisa', 'instituicoes_associadas', 'atos_normativos'] as $campo) {
            $valor = json_decode((string) ($programa[$campo] ?? '[]'), true);
            $listas[$campo] = is_array($valor) ? $valor : [];
        }

        $db = db_connect();

        $linhas = $db->table('linhas_pesquisa')
            ->where('programa_id', $id)
            ->orderBy('nome', 'ASC')
            ->get()
            ->getResultArray();

        $vinculos = $db->table('docentes_linhas_pesquisa dlp')
            ->select("dlp.linha_pesquisa_id, i.id, i.nome, i.genero, i.email, i.lattes_id, i.orcid, dlp.tipo_vinculo, GROUP_CONCAT(DISTINCT inst.nome ORDER BY inst.nome SEPARATOR ', ') AS instituicoes", false)
            ->join('individuo i', 'i.id = dlp.docente_id')
            ->join('linhas_pesquisa lp', 'lp.id = dlp.linha_pesquisa_id')
            ->join('individuo_instituicao ii', 'ii.individuo_id = i.id', 'left')
            ->join('instituicao inst', 'inst.id = ii.instituicao_id', 'left')
            ->where('lp.programa_id', $id)
            ->orderBy('i.nome', 'ASC')
            ->groupBy('dlp.linha_pesquisa_id, i.id, dlp.tipo_vinculo')
            ->get()
            ->getResultArray();

        $docentesPorLinha = [];

        foreach ($vinculos as $vinculo) {
            $docentesPorLinha[$vinculo['linha_pesquisa_id']][] = $vinculo;
        }

        $docenteIds = array_values(array_unique(array_map(static fn (array $vinculo): int => (int) $vinculo['id'], $vinculos)));
        $alunos = [];

        if ($docenteIds !== []) {
            $alunos = $db->table('orientacoes o')
                ->select('e.id, e.nome, e.lattes_id, o.tipo, o.status, o.ano_inicio, o.ano_final, orientador.id AS orientador_id, orientador.nome AS orientador_nome')
                ->join('individuo e', 'e.id = o.estudante_id')
                ->join('individuo orientador', 'orientador.id = o.orientador_id')
                ->whereIn('o.orientador_id', $docenteIds)
                ->where('o.programa_id', $id)
                ->whereIn('o.tipo', ['Mestrado', 'Doutorado'])
                ->orderBy('e.nome', 'ASC')
                ->get()
                ->getResultArray();
        }

        $agendamentos = [];
        $hoje = date('Y-m-d');
        $orientacoesAgendadas = $db->table('tmp_orientacoes')
            ->select('id, nome, modalidade, orientador, data_qualificacao, hora_qualificacao, titulo_qualificacao, local_qualificacao, data_defesa, hora_defesa, titulo_defesa, local_defesa')
            ->where('programa_id', $id)
            ->groupStart()
                ->where('data_qualificacao >=', $hoje)
                ->orWhere('data_defesa >=', $hoje)
            ->groupEnd()
            ->get()
            ->getResultArray();

        foreach ($orientacoesAgendadas as $orientacao) {
            foreach (['qualificacao' => 'Qualificação', 'defesa' => 'Defesa'] as $evento => $rotuloEvento) {
                $dataEvento = (string) ($orientacao['data_' . $evento] ?? '');
                if ($dataEvento < $hoje) {
                    continue;
                }
                $agendamentos[] = [
                    'id'         => (int) $orientacao['id'],
                    'nome'       => $orientacao['nome'],
                    'modalidade' => $orientacao['modalidade'] === 'D' ? 'Doutorado' : ($orientacao['modalidade'] === 'M' ? 'Mestrado' : ''),
                    'orientador' => $orientacao['orientador'],
                    'evento'     => $rotuloEvento,
                    'data'       => $dataEvento,
                    'hora'       => $orientacao['hora_' . $evento],
                    'titulo'     => $orientacao['titulo_' . $evento],
                    'local'      => $orientacao['local_' . $evento],
                ];
            }
        }

        usort($agendamentos, static function (array $a, array $b): int {
            $dataHoraA = $a['data'] . ' ' . ($a['hora'] ?? '00:00:00');
            $dataHoraB = $b['data'] . ' ' . ($b['hora'] ?? '00:00:00');

            return strcmp($dataHoraA, $dataHoraB);
        });

        $pessoasRede = [];
        foreach ($vinculos as $vinculo) {
            $pessoasRede[(int) $vinculo['id']] = ['id' => (int) $vinculo['id'], 'nome' => $vinculo['nome'], 'grupo' => 'docente'];
        }
        foreach ($alunos as $aluno) {
            $pessoasRede[(int) $aluno['id']] = ['id' => (int) $aluno['id'], 'nome' => $aluno['nome'], 'grupo' => 'aluno'];
        }

        $arestasRede = [];
        $idsRede = array_keys($pessoasRede);
        if ($idsRede !== []) {
            $pessoasPorNome = [];
            foreach ($pessoasRede as $pessoa) {
                $pessoasPorNome[$this->normalizarNome($pessoa['nome'])] = $pessoa['id'];
            }
            $producoes = $db->table('producoes')->select('pesquisador_id, autores')->whereIn('pesquisador_id', $idsRede)->get()->getResultArray();
            foreach ($producoes as $producao) {
                $presentes = [(int) $producao['pesquisador_id'] => true];
                foreach (explode(';', (string) ($producao['autores'] ?? '')) as $autor) {
                    $nomeNormalizado = $this->normalizarNome($autor);
                    if (isset($pessoasPorNome[$nomeNormalizado])) {
                        $presentes[$pessoasPorNome[$nomeNormalizado]] = true;
                    }
                }
                $idsPresentes = array_keys($presentes);
                for ($i = 0, $total = count($idsPresentes); $i < $total; $i++) {
                    for ($j = $i + 1; $j < $total; $j++) {
                        $a = min($idsPresentes[$i], $idsPresentes[$j]);
                        $b = max($idsPresentes[$i], $idsPresentes[$j]);
                        $chave = $a . '-' . $b;
                        $arestasRede[$chave] = ['source' => $a, 'target' => $b, 'peso' => ($arestasRede[$chave]['peso'] ?? 0) + 1];
                    }
                }
            }
        }

        return view('ppg/show', [
            'programa'         => $programa,
            'listas'           => $listas,
            'linhas'           => $linhas,
            'docentesPorLinha' => $docentesPorLinha,
            'alunos'           => $alunos,
            'agendamentos'     => $agendamentos,
            'rede'             => ['nodes' => array_values($pessoasRede), 'links' => array_values($arestasRede)],
        ]);
    }

    public function uploadLogo(int $id): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('erro', 'Faça login como administrador para enviar o logo.');
        }

        $model = new ProgramaPosGraduacaoModel();
        $programa = $model->find($id);

        if ($programa === null) {
            throw PageNotFoundException::forPageNotFound('Programa de pós-graduação não encontrado.');
        }

        $logo = $this->request->getFile('logo');

        if ($logo === null || ! $logo->isValid()) {
            return redirect()->to(site_url('ppg/' . $id))->with('erro', 'Selecione uma imagem válida para o logo.');
        }

        if ($logo->getSize() > 5 * 1024 * 1024) {
            return redirect()->to(site_url('ppg/' . $id))->with('erro', 'O logo deve ter no máximo 5 MB.');
        }

        $imagem = @getimagesize($logo->getTempName());
        $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $mime = (string) ($imagem['mime'] ?? '');

        if ($imagem === false || ! isset($extensoes[$mime])) {
            return redirect()->to(site_url('ppg/' . $id))->with('erro', 'Envie um logo JPEG, PNG, GIF ou WebP.');
        }

        $diretorio = FCPATH . '_repository' . DIRECTORY_SEPARATOR . 'logoPPG';

        if (! is_dir($diretorio) && ! mkdir($diretorio, 0775, true) && ! is_dir($diretorio)) {
            return redirect()->to(site_url('ppg/' . $id))->with('erro', 'Não foi possível preparar o diretório de logos.');
        }

        $nomeArquivo = $id . '.' . $extensoes[$mime];

        try {
            $logo->move($diretorio, $nomeArquivo, true);
        } catch (\Throwable $e) {
            log_message('error', 'Falha ao salvar logo do PPG {id}: {erro}', ['id' => $id, 'erro' => $e->getMessage()]);

            return redirect()->to(site_url('ppg/' . $id))->with('erro', 'Não foi possível salvar o logo enviado.');
        }

        if (! $model->update($id, ['logo' => $nomeArquivo])) {
            if (is_file($diretorio . DIRECTORY_SEPARATOR . $nomeArquivo)) {
                unlink($diretorio . DIRECTORY_SEPARATOR . $nomeArquivo);
            }

            return redirect()->to(site_url('ppg/' . $id))->with('erro', 'Não foi possível vincular o logo ao programa.');
        }

        return redirect()->to(site_url('ppg/' . $id))->with('sucesso', 'Logo do programa salvo com sucesso.');
    }

    private function normalizarNome(string $nome): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
        return preg_replace('/[^a-z0-9]+/', ' ', strtolower($ascii !== false ? $ascii : $nome)) ?? '';
    }

    public function adicionarDocente(int $programaId, int $linhaId): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login como administrador para adicionar docentes ao programa.');
        }

        $db = db_connect();
        $linha = $db->table('linhas_pesquisa')
            ->select('id')
            ->where('id', $linhaId)
            ->where('programa_id', $programaId)
            ->get()
            ->getRowArray();

        if ($linha === null) {
            throw PageNotFoundException::forPageNotFound('Linha de pesquisa não encontrada neste programa.');
        }

        $lattesId = trim((string) $this->request->getPost('lattes_id'));

        if (preg_match('/^\d{16}$/', $lattesId) !== 1) {
            return redirect()->to(site_url('ppg/' . $programaId))
                ->with('erro', 'Informe um ID Lattes válido com 16 dígitos.')
                ->withInput();
        }

        $agora = date('Y-m-d H:i:s');
        $db->transStart();

        $individuo = $db->table('individuo')
            ->select('id')
            ->where('lattes_id', $lattesId)
            ->get()
            ->getRowArray();

        if ($individuo === null) {
            $db->table('individuo')->insert([
                'nome'       => 'Docente Lattes ' . $lattesId,
                'lattes_id'  => $lattesId,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
            $individuoId = (int) $db->insertID();
        } else {
            $individuoId = (int) $individuo['id'];
        }

        $vinculoExiste = $db->table('docentes_linhas_pesquisa')
            ->where('linha_pesquisa_id', $linhaId)
            ->where('docente_id', $individuoId)
            ->countAllResults() > 0;

        if (! $vinculoExiste) {
            $db->table('docentes_linhas_pesquisa')->insert([
                'linha_pesquisa_id' => $linhaId,
                'docente_id'        => $individuoId,
                'tipo_vinculo'      => 'PERMANENTE',
                'created_at'        => $agora,
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('ppg/' . $programaId))
                ->with('erro', 'Não foi possível inserir o docente. Tente novamente.');
        }

        $mensagem = $vinculoExiste
            ? 'Este docente já está vinculado à linha de pesquisa.'
            : 'Docente inserido na linha de pesquisa.';

        return redirect()->to(site_url('ppg/' . $programaId))->with('sucesso', $mensagem);
    }
}
