<?= view('header/header', ['title' => $title, 'fluid' => true]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="small text-uppercase fw-bold cyra-accent mb-2">Área administrativa</p>
            <h1 class="cyra-heading h2 mb-0">Marcação de qualificação</h1>
        </div>
        <a class="btn btn-outline-info" href="<?= site_url('admin') ?>">Voltar à administração</a>
    </header>

    <section class="cyra-panel p-4 mb-4" aria-labelledby="student-search-title">
        <h2 class="h5 cyra-heading" id="student-search-title">Identificar aluno</h2>
        <form method="get" action="<?= site_url('admin/qualificacao') ?>" role="search">
            <label class="form-label" for="student-query">Nome, ID Lattes ou crachá do aluno</label>
            <div class="d-flex flex-column flex-sm-row gap-2">
                <input class="form-control" id="student-query" name="q" type="search" value="<?= esc($query, 'attr') ?>" maxlength="150" placeholder="Digite os dados do aluno" required>
                <button class="btn btn-info px-4" type="submit"><i class="bi bi-search me-2" aria-hidden="true"></i>Buscar</button>
            </div>
        </form>
    </section>

    <?php if ($aluno !== null) : ?>
        <section class="cyra-panel p-4 mb-4" aria-labelledby="selected-student-title">
            <h2 class="h5 cyra-heading" id="selected-student-title">Aluno selecionado</h2>
            <?= view('components/aluno_dados', ['aluno' => $aluno, 'bancaPessoas' => $bancaPessoas ?? []], ['saveData' => false]) ?>
            <?php $situacao = ($aluno['situacao'] ?? null) ?: 'Cursando'; ?>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <?php $acoes = ['situacao' => 'Alterar status da situação']; ?>
                <?php if ($situacao === 'Cursando') : $acoes = ['qualificacao' => 'Marcar qualificação'] + $acoes; endif; ?>
                <?php if ($situacao === 'Qualificado') : $acoes = ['defesa' => 'Marcar defesa'] + $acoes; endif; ?>
                <?php foreach ($acoes as $acao => $rotulo) : ?>
                    <a class="btn <?= $acao === 'situacao' ? 'btn-outline-info' : 'btn-info' ?>" href="<?= site_url('admin/qualificacao') . '?' . esc(http_build_query(['q' => $query, 'aluno' => (int) $aluno['id'], 'modo' => $acao]), 'attr') ?>#student-edit"><?= esc($rotulo) ?></a>
                <?php endforeach; ?>
                <div class="dropdown">
                    <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Incluir</button>
                    <ul class="dropdown-menu">
                        <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#licenca-saude">Licença saúde</button></li>
                        <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#licenca-maternidade">Licença maternidade</button></li>
                    </ul>
                </div>
            </div>
        </section>
        <?= view('components/aluno_licencas', ['aluno' => $aluno, 'query' => $query], ['saveData' => false]) ?>
        <?php if (isset($acoes[$modo])) : ?>
            <?php
            $grupos = $modo === 'situacao' ? ['entrada_programa' => 'Ingresso no programa', 'qualificacao' => 'Qualificação', 'defesa' => 'Defesa'] : [$modo => $modo === 'defesa' ? 'Defesa' : 'Qualificação'];
            $valor = static function (string $campo) use ($aluno): string {
                $atual = $aluno[$campo] ?? '';
                return (string) old($campo, str_starts_with($campo, 'hora_') ? substr((string) $atual, 0, 5) : $atual);
            };
            ?>
            <section class="cyra-panel p-4 mb-4" id="student-edit" aria-labelledby="student-edit-title">
                <h2 class="h4 cyra-heading mb-4" id="student-edit-title"><?= esc($acoes[$modo]) ?></h2>
                <form method="post" action="<?= site_url('admin/qualificacao/' . (int) $aluno['id']) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="q" value="<?= esc($query, 'attr') ?>">
                    <input type="hidden" name="modo" value="<?= esc($modo, 'attr') ?>">
                    <?php if ($modo === 'situacao') : ?>
                        <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="student-modality">Modalidade</label>
                            <select class="form-select" id="student-modality" name="modalidade">
                                <option value="">Não definida</option>
                                <?php foreach (['M' => 'Mestrado', 'D' => 'Doutorado'] as $codigo => $nomeModalidade) : ?>
                                    <option value="<?= $codigo ?>" <?= old('modalidade', $aluno['modalidade'] ?? '') === $codigo ? 'selected' : '' ?>><?= esc($nomeModalidade) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="student-situation">Situação</label>
                            <select class="form-select" id="student-situation" name="situacao" required>
                                <?php foreach ($situacoes as $opcao) : ?><option value="<?= esc($opcao, 'attr') ?>" <?= old('situacao', $situacao) === $opcao ? 'selected' : '' ?>><?= esc($opcao) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($modo === 'situacao') : ?>
                        <ul class="nav nav-tabs cyra-student-tabs mb-3" role="tablist" aria-label="Dados acadêmicos do aluno">
                            <?php foreach ($grupos as $grupo => $nomeGrupo) : $ativo = $grupo === array_key_first($grupos); ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $ativo ? 'active' : '' ?>" id="tab-<?= $grupo ?>" data-bs-toggle="tab" data-bs-target="#panel-<?= $grupo ?>" type="button" role="tab" aria-controls="panel-<?= $grupo ?>" aria-selected="<?= $ativo ? 'true' : 'false' ?>"><?= $grupo === 'entrada_programa' ? 'Ingresso' : esc($nomeGrupo) ?></button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="tab-content">
                    <?php endif; ?>
                    <?php foreach ($grupos as $grupo => $nomeGrupo) : ?>
                        <?php if ($modo === 'situacao') : ?>
                            <div class="tab-pane fade <?= $grupo === array_key_first($grupos) ? 'show active' : '' ?>" id="panel-<?= $grupo ?>" role="tabpanel" aria-labelledby="tab-<?= $grupo ?>" tabindex="0">
                        <?php endif; ?>
                        <fieldset class="border rounded p-3 p-md-4 mb-4">
                            <legend class="float-none w-auto px-2 h5 cyra-heading"><?= esc($nomeGrupo) ?></legend>
                            <?php if ($grupo !== 'entrada_programa') : ?>
                                <div class="mb-3">
                                    <label class="form-label" for="titulo-<?= $grupo ?>">Título do trabalho — <?= esc($nomeGrupo) ?></label>
                                    <textarea class="form-control" id="titulo-<?= $grupo ?>" name="titulo_<?= $grupo ?>" rows="2" maxlength="5000" <?= $modo !== 'situacao' ? 'required' : '' ?>><?= esc($valor('titulo_' . $grupo)) ?></textarea>
                                </div>
                            <?php endif; ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="data-<?= $grupo ?>"><?= $grupo === 'entrada_programa' ? 'Data de ingresso' : 'Data' ?></label>
                                    <input class="form-control" id="data-<?= $grupo ?>" name="data_<?= $grupo ?>" type="date" value="<?= esc($valor('data_' . $grupo), 'attr') ?>" <?= $modo !== 'situacao' ? 'required' : '' ?>>
                                </div>
                                <?php if ($grupo === 'entrada_programa') : ?>
                                    <div class="col-md-4">
                                        <label class="form-label" for="extensao-dias">Extensão do prazo (dias)</label>
                                        <input class="form-control" id="extensao-dias" name="extensao_dias" type="number" min="0" max="4294967295" step="1" value="<?= esc((string) old('extensao_dias', $aluno['extensao_dias'] ?? 0), 'attr') ?>" required>
                                    </div>
                                <?php endif; ?>
                                <?php if ($grupo !== 'entrada_programa') : ?>
                                <div class="col-md-3">
                                    <label class="form-label" for="hora-<?= $grupo ?>">Hora</label>
                                    <input class="form-control" id="hora-<?= $grupo ?>" name="hora_<?= $grupo ?>" type="time" value="<?= esc($valor('hora_' . $grupo), 'attr') ?>" <?= $modo !== 'situacao' ? 'required' : '' ?>>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="local-<?= $grupo ?>">Local físico ou link HTTPS</label>
                                    <input class="form-control" id="local-<?= $grupo ?>" name="local_<?= $grupo ?>" type="text" maxlength="2000" placeholder="Sala, endereço ou https://…" value="<?= esc($valor('local_' . $grupo), 'attr') ?>" <?= $modo !== 'situacao' ? 'required' : '' ?>>
                                </div>
                                <?php endif; ?>
                            </div>
                        </fieldset>
                        <?php if ($modo === 'situacao') : ?></div><?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($modo === 'situacao') : ?></div><?php endif; ?>
                    <?php if ($modo === 'qualificacao') : ?>
                        <?= view('components/banca_form', ['aluno' => $aluno, 'bancaPessoas' => $bancaPessoas ?? []], ['saveData' => false]) ?>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-info px-4" type="submit"><?= $modo === 'situacao' ? 'Salvar alterações' : 'Salvar marcação' ?></button>
                        <a class="btn btn-outline-info" href="<?= site_url('admin/qualificacao') . '?' . esc(http_build_query(['q' => $query, 'aluno' => (int) $aluno['id']]), 'attr') ?>">Cancelar</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($aluno !== null && session()->get('auth_logged_in') === true) : ?>
        <?= view('components/aluno_solicitacoes', ['solicitacoes' => $solicitacoes ?? []], ['saveData' => false]) ?>
    <?php endif; ?>

    <?php if ($query !== '' && $aluno === null) : ?>
        <section class="cyra-panel p-4" aria-labelledby="student-results-title">
            <h2 class="h5 cyra-heading" id="student-results-title">Resultados da busca</h2>
            <?php if ($hasMore) : ?><p class="small cyra-muted">Exibindo os primeiros 50 registros. Refine a busca para localizar o aluno.</p><?php endif; ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead><tr><th scope="col">Aluno</th><th scope="col">ID Lattes</th><th scope="col">Crachá</th><th scope="col">Ano</th><th scope="col">Orientador</th><th scope="col">Seleção</th></tr></thead>
                    <tbody>
                        <?php foreach ($alunos as $registro) : ?>
                            <tr>
                                <td><?= esc($registro['nome']) ?></td>
                                <td><?= esc($registro['id_lattes'] ?: '—') ?></td>
                                <td><?= esc($registro['cracha'] ?: '—') ?></td>
                                <td><?= esc((string) $registro['ano']) ?></td>
                                <td><?= esc($registro['orientador'] ?: '—') ?></td>
                                <td><a class="btn btn-sm btn-outline-info" href="<?= site_url('admin/qualificacao') . '?' . esc(http_build_query(['q' => $query, 'aluno' => (int) $registro['id']]), 'attr') ?>" aria-label="<?= esc('Selecionar ' . $registro['nome'], 'attr') ?>">Selecionar</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($alunos === []) : ?><tr><td colspan="6" class="cyra-muted">Nenhum aluno encontrado para esta busca.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</main>

<?= view('header/footer', ['fluid' => true]) ?>
