<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Admin extends BaseController
{
    private const SITUACOES = ['Cursando', 'Desistiu', 'Qualificado', 'Defendido', 'Diplomando', 'Desligado'];

    public function buscarPessoasBanca(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (session()->get('auth_logged_in') !== true) {
            return $this->response->setStatusCode(401)->setJSON(['pessoas' => []]);
        }
        $query = mb_substr(trim((string) $this->request->getGet('q')), 0, 150);
        if (mb_strlen($query) < 2) {
            return $this->response->setJSON(['pessoas' => []]);
        }
        $pessoas = (new \App\Models\PersonModel())->select('id, nome, lattes_id')
            ->groupStart()->like('nome', $query)->orLike('lattes_id', $query)->groupEnd()
            ->orderBy('nome', 'ASC')->findAll(20);
        return $this->response->setHeader('Cache-Control', 'no-store')->setJSON(['pessoas' => $pessoas]);
    }

    public function incluirLicenca(int $id): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))->with('error', 'Faça login para incluir uma solicitação de licença.');
        }
        $query = mb_substr(trim((string) $this->request->getPost('q')), 0, 150);
        $url = site_url('admin/qualificacao') . '?' . http_build_query(['q' => $query, 'aluno' => $id]);
        $aluno = db_connect()->table('tmp_orientacoes')->where('id', $id)->get()->getRowArray();
        if ($aluno === null) {
            return redirect()->to(site_url('admin/qualificacao'))->with('error', 'Aluno não encontrado.');
        }
        $data = [];
        foreach (['licenca_tipo', 'data_inicio', 'data_fim', 'observacoes'] as $campo) {
            $data[$campo] = trim((string) $this->request->getPost($campo));
        }
        foreach (['dias_qualificacao', 'dias_defesa'] as $campo) {
            $valor = trim((string) $this->request->getPost($campo));
            $data[$campo] = $valor === '' ? '0' : $valor;
        }
        $rules = [
            'licenca_tipo' => 'required|in_list[saude,maternidade]',
            'data_inicio' => 'required|valid_date[Y-m-d]',
            'data_fim' => 'required|valid_date[Y-m-d]',
            'observacoes' => 'permit_empty|max_length[2000]',
            'dias_qualificacao' => 'required|is_natural|max_length[10]',
            'dias_defesa' => 'required|is_natural|max_length[10]',
        ];
        $popup = in_array($data['licenca_tipo'], ['saude', 'maternidade'], true) ? $data['licenca_tipo'] : 'saude';
        $errorUrl = $url . '&licenca=' . $popup;
        if (! $this->validateData($data, $rules)) {
            return redirect()->to($errorUrl)->withInput()->with('errors', array_values($this->validator->getErrors()));
        }
        if ($data['data_fim'] < $data['data_inicio']) {
            return redirect()->to($errorUrl)->withInput()->with('error', 'A data final da licença deve ser igual ou posterior à data inicial.');
        }
        $data['dias_qualificacao'] = (int) $data['dias_qualificacao'];
        $data['dias_defesa'] = (int) $data['dias_defesa'];
        $evento = $data['licenca_tipo'] === 'saude' ? 'Licença saúde' : 'Licença maternidade';
        $authData = session()->get('auth_data');
        $usuarioId = \App\Libraries\IdPessoaAutenticada::extrair(is_array($authData) ? $authData : []);
        if ($usuarioId === null) {
            return redirect()->to($errorUrl)->withInput()->with('error', 'O serviço de autenticação não informou o ID da pessoa logada. Entre novamente para registrar a solicitação.');
        }
        try {
            $model = new \App\Models\TmpOrientacaoHistoricoModel();
            $saved = $model->insert([
                'orientacao_id' => $id,
                'tipo' => 'Solicitação',
                'evento' => $evento,
                'descricao' => $data['observacoes'] !== '' ? $data['observacoes'] : null,
                'situacao_anterior' => $aluno['situacao'],
                'situacao_nova' => $aluno['situacao'],
                'dados_novos' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'usuario' => $usuarioId,
            ]);
            if ($saved === false) {
                throw new \RuntimeException('Não foi possível registrar a solicitação.');
            }
        } catch (\Throwable $exception) {
            log_message('error', 'Falha ao registrar licença do aluno {id}: {message}', ['id' => $id, 'message' => $exception->getMessage()]);
            return redirect()->to($errorUrl)->withInput()->with('error', 'Não foi possível registrar a licença. Tente novamente.');
        }
        return redirect()->to($url)->with('success', $evento . ' registrada no histórico do aluno.');
    }

    public function atualizarSolicitacao(int $id): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))->with('error', 'Faça login como administrador para editar solicitações.');
        }

        $alunoId = (int) $this->request->getPost('aluno_id');
        $query = mb_substr(trim((string) $this->request->getPost('q')), 0, 150);
        $url = site_url('admin/qualificacao') . '?' . http_build_query(['q' => $query, 'aluno' => $alunoId]);
        $model = new \App\Models\TmpOrientacaoHistoricoModel();
        $solicitacao = $model->where('id', $id)
            ->where('orientacao_id', $alunoId)
            ->where('tipo', 'Solicitação')
            ->first();

        if ($alunoId < 1 || $solicitacao === null) {
            return redirect()->to(site_url('admin/qualificacao'))
                ->with('error', 'A solicitação não foi encontrada para o aluno selecionado.');
        }

        $dadosAnteriores = json_decode((string) ($solicitacao['dados_novos'] ?? ''), true);
        $dadosAnteriores = is_array($dadosAnteriores) ? $dadosAnteriores : [];
        if (! in_array($solicitacao['evento'], ['Licença saúde', 'Licença maternidade'], true)) {
            return redirect()->to($url)->with('error', 'Este tipo de solicitação não pode ser editado por este formulário.');
        }

        $data = [
            'licenca_tipo' => trim((string) $this->request->getPost('licenca_tipo')),
            'data_inicio'  => trim((string) $this->request->getPost('data_inicio')),
            'data_fim'     => trim((string) $this->request->getPost('data_fim')),
            'observacoes'  => trim((string) $this->request->getPost('observacoes')),
            'dias_qualificacao' => trim((string) ($this->request->getPost('dias_qualificacao') ?? '0')),
            'dias_defesa' => trim((string) ($this->request->getPost('dias_defesa') ?? '0')),
        ];
        $rules = [
            'licenca_tipo' => 'required|in_list[saude,maternidade]',
            'data_inicio'  => 'required|valid_date[Y-m-d]',
            'data_fim'     => 'required|valid_date[Y-m-d]',
            'observacoes'  => 'permit_empty|max_length[2000]',
            'dias_qualificacao' => 'required|is_natural|max_length[10]',
            'dias_defesa' => 'required|is_natural|max_length[10]',
        ];

        if (! $this->validateData($data, $rules)) {
            return redirect()->to($url)
                ->withInput()
                ->with('errors', array_values($this->validator->getErrors()));
        }
        if ($data['data_fim'] < $data['data_inicio']) {
            return redirect()->to($url)->withInput()
                ->with('error', 'A data final da licença deve ser igual ou posterior à data inicial.');
        }
        $data['dias_qualificacao'] = (int) $data['dias_qualificacao'];
        $data['dias_defesa'] = (int) $data['dias_defesa'];

        $evento = $data['licenca_tipo'] === 'saude' ? 'Licença saúde' : 'Licença maternidade';
        $snapshot = [
            'evento'       => $solicitacao['evento'],
            'descricao'    => $solicitacao['descricao'],
            'dados_novos'  => $dadosAnteriores,
        ];

        try {
            $saved = $model->update($id, [
                'evento'          => $evento,
                'descricao'       => $data['observacoes'] !== '' ? $data['observacoes'] : null,
                'dados_anteriores'=> json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'dados_novos'     => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ]);
            if ($saved === false) {
                throw new \RuntimeException('Não foi possível atualizar a solicitação.');
            }
        } catch (\Throwable $exception) {
            log_message('error', 'Falha ao editar solicitação {requestId} do aluno {studentId}: {message}', [
                'requestId' => $id,
                'studentId' => $alunoId,
                'message'   => $exception->getMessage(),
            ]);

            return redirect()->to($url)->withInput()
                ->with('error', 'Não foi possível atualizar a solicitação. Tente novamente.');
        }

        return redirect()->to($url)->with('success', 'Solicitação atualizada com sucesso.');
    }

    public function salvarSituacao(int $id): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))->with('error', 'Faça login para editar a situação do aluno.');
        }
        $query = mb_substr(trim((string) $this->request->getPost('q')), 0, 150);
        $modo = (string) $this->request->getPost('modo');
        $url = site_url('admin/qualificacao') . '?' . http_build_query(['q' => $query, 'aluno' => $id, 'modo' => $modo]);
        $db = db_connect();
        $aluno = $db->table('tmp_orientacoes')->where('id', $id)->get()->getRowArray();
        if ($aluno === null) {
            return redirect()->to(site_url('admin/qualificacao'))->with('error', 'Aluno não encontrado.');
        }
        $situacaoAtual = $aluno['situacao'] ?: 'Cursando';
        if (! in_array($modo, ['situacao', 'qualificacao', 'defesa'], true)
            || ($modo === 'qualificacao' && $situacaoAtual !== 'Cursando')
            || ($modo === 'defesa' && $situacaoAtual !== 'Qualificado')) {
            return redirect()->to($url)->with('error', 'Esta ação não está disponível para a situação atual do aluno.');
        }
        $groups = $modo === 'situacao' ? ['entrada_programa', 'qualificacao', 'defesa'] : [$modo];
        $rules = [];
        foreach ($groups as $group) {
            $rules['data_' . $group] = ($modo === 'situacao' ? 'permit_empty' : 'required') . '|valid_date[Y-m-d]';
            if ($group !== 'entrada_programa') {
                $rules['hora_' . $group] = ($modo === 'situacao' ? 'permit_empty' : 'required') . '|regex_match[/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/]';
                $rules['local_' . $group] = ($modo === 'situacao' ? 'permit_empty' : 'required') . '|max_length[2000]';
                $rules['titulo_' . $group] = ($modo === 'situacao' ? 'permit_empty' : 'required') . '|max_length[5000]';
            }
        }
        if ($modo === 'situacao') {
            $rules['situacao'] = 'required|in_list[' . implode(',', self::SITUACOES) . ']';
            $rules['modalidade'] = 'permit_empty|in_list[D,M]';
            $rules['extensao_dias'] = 'required|is_natural|less_than_equal_to[4294967295]';
        }
        if ($modo === 'qualificacao') {
            foreach (['avalidor_interno', 'avalidor_externo', 'suplente'] as $prefix) {
                for ($number = 1; $number <= 3; $number++) {
                    $rules[$prefix . '_' . $number] = 'permit_empty|is_natural_no_zero|is_not_unique[individuo.id]';
                }
            }
        }
        $data = [];
        foreach ($rules as $field => $rule) {
            $data[$field] = trim((string) $this->request->getPost($field));
        }
        if (! $this->validateData($data, $rules)) {
            return redirect()->to($url)->withInput()->with('errors', array_values($this->validator->getErrors()));
        }
        if ($modo === 'qualificacao') {
            $membros = array_filter(array_intersect_key($data, array_flip([
                'avalidor_interno_1', 'avalidor_interno_2', 'avalidor_interno_3',
                'avalidor_externo_1', 'avalidor_externo_2', 'avalidor_externo_3',
                'suplente_1', 'suplente_2', 'suplente_3',
            ])), static fn ($value) => $value !== '');
            if (count($membros) !== count(array_unique(array_map('intval', $membros)))) {
                return redirect()->to($url)->withInput()->with('error', 'Uma pessoa não pode ocupar mais de uma posição na banca.');
            }
        }
        foreach ($data as $field => $value) {
            if ($field === 'extensao_dias') {
                $data[$field] = (int) $value;
                continue;
            }
            $data[$field] = $value === '' ? null : (str_starts_with($field, 'hora_') ? $value . ':00' : $value);
        }
        try {
            // Check the current situation again so a stale scheduling form cannot override a status change.
            $builder = $db->table('tmp_orientacoes')->where('id', $id);
            if ($modo !== 'situacao') {
                $builder->where('situacao', $aluno['situacao']);
            }
            if (! $builder->update($data)) {
                throw new \RuntimeException('Update failed');
            }
            if ($modo !== 'situacao' && $db->affectedRows() === 0) {
                $current = $db->table('tmp_orientacoes')->where('id', $id)->get()->getRowArray();
                if ($current === null || $current['situacao'] !== $aluno['situacao']) {
                    return redirect()->to($url)->with('error', 'A situação do aluno mudou. Atualize a página antes de marcar o evento.');
                }
            }
        } catch (\Throwable $exception) {
            log_message('error', 'Falha ao salvar marcação do aluno {id}: {message}', ['id' => $id, 'message' => $exception->getMessage()]);
            return redirect()->to($url)->withInput()->with('error', 'Não foi possível salvar os dados. Tente novamente.');
        }
        $message = match ($modo) {
            'qualificacao' => 'Qualificação marcada com sucesso.',
            'defesa' => 'Defesa marcada com sucesso.',
            default => 'Situação e dados acadêmicos atualizados com sucesso.',
        };
        return redirect()->to(site_url('admin/qualificacao') . '?' . http_build_query(['q' => $query, 'aluno' => $id]))->with('success', $message);
    }

    public function qualificacao(): string|RedirectResponse
    {
        return $this->marcacao('Marcação de qualificação', 'calendar-check', true);
    }

    public function defesa(): string|RedirectResponse
    {
        return $this->marcacao('Marcação de defesa', 'mortarboard');
    }

    private function marcacao(string $title, string $icon, bool $buscarAluno = false): string|RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login para acessar a marcação de qualificações e defesas.');
        }

        if ($buscarAluno) {
            $query = mb_substr(trim((string) $this->request->getGet('q')), 0, 150);
            $alunos = [];
            $aluno = null;
            $hasMore = false;
            $db = db_connect();
            if ($query !== '') {
                $alunos = $db->table('tmp_orientacoes')
                    ->select('id, nome, id_lattes, ano, cracha, orientador')
                    ->groupStart()->like('nome', $query)->orLike('id_lattes', $query)->orLike('cracha', $query)->groupEnd()
                    ->orderBy('nome', 'ASC')->orderBy('ano', 'DESC')->orderBy('id', 'ASC')
                    ->limit(51)->get()->getResultArray();
                $hasMore = count($alunos) > 50;
                $alunos = array_slice($alunos, 0, 50);
            }
            $alunoId = (int) $this->request->getGet('aluno');
            if ($alunoId > 0) {
                $aluno = $db->table('tmp_orientacoes')
                    ->where('id', $alunoId)->get()->getRowArray();
                if ($aluno === null) {
                    session()->setFlashdata('error', 'O aluno selecionado não foi encontrado.');
                }
            }
            $modo = (string) $this->request->getGet('modo');
            $situacoes = self::SITUACOES;
            $solicitacoes = $aluno === null ? [] : (new \App\Models\TmpOrientacaoHistoricoModel())
                ->where('orientacao_id', $aluno['id'])->where('tipo', 'Solicitação')
                ->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->findAll();
            if ($aluno !== null) {
                $aluno['dias_solicitacao_qualificacao'] = $this->somarDiasSolicitados($solicitacoes, 'dias_qualificacao');
                $aluno['dias_solicitacao_defesa'] = $this->somarDiasSolicitados($solicitacoes, 'dias_defesa');
            }
            $bancaPessoas = [];
            if ($aluno !== null) {
                $idsBanca = [];
                foreach (['avalidor_interno', 'avalidor_externo', 'suplente'] as $prefix) {
                    for ($number = 1; $number <= 3; $number++) {
                        $value = old($prefix . '_' . $number, $aluno[$prefix . '_' . $number] ?? '');
                        if (ctype_digit((string) $value) && (int) $value > 0) {
                            $idsBanca[] = (int) $value;
                        }
                    }
                }
                if ($idsBanca !== []) {
                    foreach ((new \App\Models\PersonModel())->select('id, nome')->whereIn('id', $idsBanca)->findAll() as $pessoa) {
                        $bancaPessoas[$pessoa['id']] = $pessoa['nome'];
                    }
                }
            }
            return view('admin/qualificacao', compact('title', 'icon', 'query', 'alunos', 'aluno', 'hasMore', 'modo', 'situacoes', 'solicitacoes', 'bancaPessoas'));
        }

        return view('admin/marcacao', compact('title', 'icon'));
    }

    /** @param array<int, array<string, mixed>> $solicitacoes */
    private function somarDiasSolicitados(array $solicitacoes, string $campo): int
    {
        $total = 0;
        foreach ($solicitacoes as $solicitacao) {
            $dados = json_decode((string) ($solicitacao['dados_novos'] ?? ''), true);
            $dias = is_array($dados) ? filter_var($dados[$campo] ?? 0, FILTER_VALIDATE_INT) : 0;
            if ($dias !== false && $dias > 0) {
                $total += $dias;
            }
        }

        return $total;
    }

    public function index(): string|RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login para acessar a área administrativa.');
        }

        return view('admin/index', [
            'title'    => 'Administração',
            'givename' => (string) session()->get('auth_givename'),
            'username' => (string) session()->get('auth_user'),
        ]);
    }
}
