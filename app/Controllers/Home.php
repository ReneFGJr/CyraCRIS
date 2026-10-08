<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $programas = (new \App\Models\ProgramaPosGraduacaoModel())->orderBy('nome', 'ASC')->findAll();
        $selecionado = null;
        $id = (int) $this->request->getGet('ppg');
        foreach ($programas as $programa) {
            if ((int) $programa['id'] === $id) {
                $selecionado = $programa;
                break;
            }
        }
        $selecionado ??= $programas[0] ?? null;
        $linhas = [];
        $docentes = [];
        if ($selecionado !== null) {
            $db = db_connect();
            $linhas = $db->table('linhas_pesquisa')->where('programa_id', $selecionado['id'])
                ->orderBy('nome', 'ASC')->get()->getResultArray();
            $docentes = $db->table('docentes_linhas_pesquisa dlp')
                ->select('i.id, i.nome')->distinct()
                ->join('individuo i', 'i.id = dlp.docente_id')
                ->join('linhas_pesquisa lp', 'lp.id = dlp.linha_pesquisa_id')
                ->where('lp.programa_id', $selecionado['id'])
                ->orderBy('i.nome', 'ASC')->get()->getResultArray();
        }
        return view('welcome_message', compact('programas', 'selecionado', 'linhas', 'docentes'));
    }

    public function about(): string
    {
        return view('about');
    }
}
