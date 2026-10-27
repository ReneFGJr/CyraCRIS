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

        $report = (string) $this->request->getGet('report');
        if (in_array($report, ['estudantes-ativos', 'egressos'], true)) {
            $ativos = $report === 'estudantes-ativos';
            $builder = db_connect()->table('tmp_orientacoes t')
                ->select('t.id, t.nome, t.id_lattes, t.cracha, t.ano, t.modalidade, t.situacao, t.orientador, t.data_entrada_programa, t.data_qualificacao, t.data_defesa, p.nome AS programa_nome, lp.nome AS linha_pesquisa_nome')
                ->join('programas_pos_graduacao p', 'p.id = t.programa_id', 'left')
                ->join('linhas_pesquisa lp', 'lp.id = t.linha_pesquisa_id', 'left');

            if ($ativos) {
                $builder->groupStart()
                    ->whereIn('t.situacao', ['Cursando', 'Qualificado'])
                    ->orWhere('t.situacao IS NULL', null, false)
                    ->orWhere('t.situacao', '')
                    ->groupEnd();
            } else {
                $builder->whereIn('t.situacao', ['Defendido', 'Diplomando']);
            }

            $estudantes = $builder
                ->orderBy('t.nome', 'ASC')
                ->orderBy('t.ano', 'DESC')
                ->orderBy('t.id', 'ASC')
                ->get()
                ->getResultArray();

            return view('admin/report/students', [
                'title' => $ativos ? 'Todos os estudantes ativos' : 'Todos os egressos',
                'report' => $report,
                'estudantes' => $estudantes,
            ]);
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
