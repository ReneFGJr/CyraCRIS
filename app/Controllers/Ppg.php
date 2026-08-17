<?php

namespace App\Controllers;

use App\Models\ProgramaPosGraduacaoModel;
use CodeIgniter\Exceptions\PageNotFoundException;

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
            ->select('dlp.linha_pesquisa_id, d.nome, d.email, d.lattes_url, dlp.tipo_vinculo')
            ->join('docentes d', 'd.id = dlp.docente_id')
            ->join('linhas_pesquisa lp', 'lp.id = dlp.linha_pesquisa_id')
            ->where('lp.programa_id', $id)
            ->orderBy('d.nome', 'ASC')
            ->get()
            ->getResultArray();

        $docentesPorLinha = [];

        foreach ($vinculos as $vinculo) {
            $docentesPorLinha[$vinculo['linha_pesquisa_id']][] = $vinculo;
        }

        return view('ppg/show', [
            'programa'         => $programa,
            'listas'           => $listas,
            'linhas'           => $linhas,
            'docentesPorLinha' => $docentesPorLinha,
        ]);
    }
}
