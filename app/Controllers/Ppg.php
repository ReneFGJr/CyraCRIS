<?php

namespace App\Controllers;

use App\Models\ProgramaPosGraduacaoModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

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
                ->whereIn('o.tipo', ['Mestrado', 'Doutorado'])
                ->orderBy('e.nome', 'ASC')
                ->get()
                ->getResultArray();
        }

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
            'rede'             => ['nodes' => array_values($pessoasRede), 'links' => array_values($arestasRede)],
        ]);
    }

    private function normalizarNome(string $nome): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
        return preg_replace('/[^a-z0-9]+/', ' ', strtolower($ascii !== false ? $ascii : $nome)) ?? '';
    }

    public function adicionarDocente(int $programaId, int $linhaId): RedirectResponse
    {
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
