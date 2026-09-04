<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class Report extends BaseController
{
    public function index(): string|RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login para acessar os relatórios do sistema.');
        }

        return view('admin/report/index', [
            'title' => 'Relatórios do sistema',
            'groups' => [
                [
                    'title'       => 'Docentes',
                    'description' => 'Relatórios sobre docentes e seus vínculos acadêmicos.',
                    'icon'        => 'person-video3',
                    'reports'     => [
                        ['label' => 'Docentes por linhas', 'slug' => 'docentes-por-linhas', 'icon' => 'diagram-3'],
                        ['label' => 'Todos os docentes', 'slug' => 'todos-os-docentes', 'icon' => 'people'],
                    ],
                ],
                [
                    'title'       => 'Estudantes',
                    'description' => 'Relatórios de estudantes atuais e egressos.',
                    'icon'        => 'mortarboard',
                    'reports'     => [
                        ['label' => 'Todos os estudantes ativos', 'slug' => 'estudantes-ativos', 'icon' => 'person-check'],
                        ['label' => 'Todos os egressos', 'slug' => 'egressos', 'icon' => 'person-dash'],
                    ],
                ],
                [
                    'title'       => 'Manutenção do cadastro',
                    'description' => 'Consultas para revisão e melhoria da qualidade dos dados.',
                    'icon'        => 'tools',
                    'reports'     => [
                        ['label' => 'Nomes duplicados', 'slug' => 'nomes-duplicados', 'icon' => 'people-fill', 'url' => 'admin/tools/names_duplicate'],
                        ['label' => 'Padronizar nomes', 'slug' => 'padronizacao-dos-nomes', 'icon' => 'spellcheck', 'url' => 'admin/tools/names'],
                    ],
                ],
            ],
        ]);
    }
}
