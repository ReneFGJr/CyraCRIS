<?php
/** @var array<string, mixed> $programa */
/** @var array<string, array<int, mixed>> $listas */
/** @var array<int, array<string, mixed>> $linhas */
/** @var array<int, array<int, array<string, mixed>>> $docentesPorLinha */
/** @var array<int, array<string, mixed>> $alunos */
/** @var array{nodes: array<int, array<string, mixed>>, links: array<int, array<string, mixed>>} $rede */
$idsDocentes = [];
foreach ($docentesPorLinha as $docentesDaLinha) {
    foreach ($docentesDaLinha as $docente) {
        $idsDocentes[(int) $docente['id']] = true;
    }
}
$totalDocentes = count($idsDocentes);
$totalAreas = count(array_unique(array_filter(array_column($linhas, 'area_concentracao'))));
$alunosAtivos = array_values(array_filter($alunos, static fn (array $aluno): bool => (int) $aluno['status'] === 0));
$alunosConcluidos = array_values(array_filter($alunos, static fn (array $aluno): bool => (int) $aluno['status'] === 1));
?>
<?= view('layout/header', [
    'title' => $programa['nome'],
    'description' => 'Detalhes do programa de pós-graduação ' . $programa['nome'] . '.',
]) ?>
<?= view('layout/navbar') ?>

<style>
    .ppg-hero { position: relative; overflow: hidden; border: 1px solid rgba(23, 189, 197, .3); background: linear-gradient(125deg, rgba(18, 102, 177, .34), rgba(5, 19, 40, .72)); }
    .ppg-hero::after { position: absolute; right: -4rem; bottom: -7rem; width: 20rem; height: 20rem; content: ""; border: 1px solid rgba(23, 189, 197, .25); border-radius: 50%; box-shadow: 0 0 0 3rem rgba(23, 189, 197, .04), 0 0 0 6rem rgba(23, 189, 197, .025); }
    .ppg-hero-content { position: relative; z-index: 1; }
    .ppg-stat { min-height: 8rem; border: 1px solid rgba(151, 205, 225, .17); background: linear-gradient(145deg, rgba(5, 19, 40, .6), rgba(18, 102, 177, .12)); transition: transform .2s ease, border-color .2s ease; }
    .ppg-stat:hover { transform: translateY(-2px); border-color: rgba(23, 189, 197, .55); }
    .ppg-stat-value { font-family: Georgia, "Times New Roman", serif; font-size: 2rem; line-height: 1; }
    .ppg-card { border-top: 3px solid rgba(23, 189, 197, .55); }
    .ppg-quick-link { border-color: rgba(151, 205, 225, .25); color: var(--cyra-muted); }
    .ppg-quick-link:hover { color: #fff; border-color: var(--cyra-cyan); background: rgba(23, 189, 197, .1); }
    .ppg-research-table { border-collapse: separate; border-spacing: 0 .75rem; }
    .ppg-research-table thead th { border: 0; color: var(--cyra-muted); font-size: .75rem; letter-spacing: .06em; text-transform: uppercase; }
    .ppg-research-table tbody tr { background: rgba(5, 19, 40, .65); }
    .ppg-research-table tbody td { padding: 1.25rem; border-top: 1px solid rgba(151, 205, 225, .14); border-bottom: 1px solid rgba(151, 205, 225, .14); }
    .ppg-research-table tbody td:first-child { border-left: 3px solid var(--cyra-cyan); }
    .ppg-research-table tbody td:last-child { border-right: 1px solid rgba(151, 205, 225, .14); }
    .ppg-teacher { padding: .65rem 0; border-bottom: 1px solid rgba(151, 205, 225, .1); }
    .ppg-teacher:last-child { border-bottom: 0; }
    .ppg-line { overflow: hidden; border: 1px solid rgba(151, 205, 225, .18); background: rgba(5, 19, 40, .48); }
    .ppg-line + .ppg-line { margin-top: 1rem; }
    .ppg-line-header { border-bottom: 1px solid rgba(151, 205, 225, .12); background: linear-gradient(90deg, rgba(18, 102, 177, .18), transparent); }
    .ppg-line-number { display: grid; width: 2.5rem; height: 2.5rem; place-items: center; flex: 0 0 auto; border: 1px solid rgba(23, 189, 197, .4); color: var(--cyra-cyan); font-family: Georgia, serif; }
    .ppg-teacher-card { height: 100%; border: 1px solid rgba(151, 205, 225, .14); background: rgba(7, 26, 54, .75); transition: border-color .2s ease, transform .2s ease; }
    .ppg-teacher-card:hover { transform: translateY(-2px); border-color: rgba(23, 189, 197, .5); }
    .ppg-avatar { display: grid; width: 2.75rem; height: 2.75rem; place-items: center; flex: 0 0 auto; border-radius: 50%; color: #061328; background: var(--cyra-cyan); font-weight: 700; }
    .ppg-meta { display: inline-flex; align-items: center; padding: .2rem .45rem; border: 1px solid rgba(151, 205, 225, .14); color: var(--cyra-muted); font-size: .72rem; }
    .ppg-add-form { border-top: 1px dashed rgba(23, 189, 197, .3); background: rgba(23, 189, 197, .05); }
    .ppg-tabs .nav-link { color: var(--cyra-muted); border: 0; border-bottom: 3px solid transparent; }
    .ppg-tabs .nav-link:hover { color: #fff; border-bottom-color: rgba(23, 189, 197, .35); }
    .ppg-tabs .nav-link.active { color: #fff; background: rgba(23, 189, 197, .1); border-bottom-color: var(--cyra-cyan); }
    .ppg-student-card { border: 1px solid rgba(151, 205, 225, .14); background: rgba(7, 26, 54, .7); }
    #rede-academica { width: 100%; min-height: 34rem; border: 1px solid rgba(151, 205, 225, .14); background: radial-gradient(circle at center, rgba(18, 102, 177, .14), rgba(5, 19, 40, .8)); }
</style>

<main class="container py-5">
    <a class="btn btn-sm btn-outline-info rounded-0 mb-4" href="<?= site_url('ppg') ?>">
        <i class="bi bi-arrow-left me-2"></i>Voltar para programas
    </a>

    <header class="ppg-hero p-4 p-lg-5 mb-4">
        <div class="ppg-hero-content col-xl-9">
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-mortarboard me-2"></i>Programa de pós-graduação</p>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="badge text-bg-info rounded-0 px-3 py-2">Nota CAPES <?= esc($programa['nota_capes'] ?? '-') ?></span>
                <span class="badge border border-success text-success rounded-0 px-3 py-2"><i class="bi bi-check-circle me-1"></i><?= esc($programa['situacao'] ?? '-') ?></span>
                <span class="badge border border-light border-opacity-25 text-light rounded-0 px-3 py-2"><?= esc($programa['modalidade'] ?? '-') ?></span>
            </div>
            <h1 class="cyra-heading display-6 text-white mb-3"><?= esc($programa['nome']) ?></h1>
            <p class="lead cyra-muted mb-0"><i class="bi bi-building me-2 cyra-accent"></i><?= esc($programa['instituicao_nome'] ?? '-') ?><?php if (! empty($programa['instituicao_sigla'])) : ?> <span class="text-white">(<?= esc($programa['instituicao_sigla']) ?>)</span><?php endif; ?></p>
        </div>
    </header>

    <?php if (session('sucesso')) : ?>
        <div class="alert alert-success rounded-0" role="alert"><?= esc(session('sucesso')) ?></div>
    <?php endif; ?>
    <?php if (session('erro')) : ?>
        <div class="alert alert-danger rounded-0" role="alert"><?= esc(session('erro')) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <?php foreach ([
            ['diagram-3', count($linhas), 'Linhas de pesquisa'],
            ['people', $totalDocentes, 'Docentes vinculados'],
            ['bullseye', $totalAreas, 'Áreas de concentração'],
            ['award', count($listas['graus']), 'Graus acadêmicos'],
        ] as [$icone, $valor, $rotulo]) : ?>
            <div class="col-6 col-lg-3"><div class="ppg-stat p-3 p-md-4"><i class="bi bi-<?= $icone ?> fs-4 cyra-accent"></i><strong class="ppg-stat-value d-block text-white mt-3"><?= (int) $valor ?></strong><span class="small cyra-muted"><?= esc($rotulo) ?></span></div></div>
        <?php endforeach; ?>
    </div>

    <nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Navegação nesta página">
        <a class="btn btn-sm rounded-0 ppg-quick-link" href="#dados-programa"><i class="bi bi-info-circle me-1"></i>Dados do programa</a>
        <a class="btn btn-sm rounded-0 ppg-quick-link" href="#informacoes-academicas"><i class="bi bi-list-check me-1"></i>Informações acadêmicas</a>
        <a class="btn btn-sm rounded-0 ppg-quick-link" href="#linhas-docentes"><i class="bi bi-people me-1"></i>Linhas e docentes</a>
    </nav>

    <div class="row g-4" id="dados-programa">
        <div class="col-lg-7">
            <section class="cyra-panel ppg-card p-4 h-100">
                <h2 class="h5 text-white border-bottom border-light border-opacity-10 pb-3 mb-4"><i class="bi bi-info-circle me-2 cyra-accent"></i>Dados básicos</h2>
                <dl class="row gy-3 mb-0">
                    <dt class="col-sm-5 cyra-muted">Código CAPES</dt>
                    <dd class="col-sm-7 text-white"><?= esc($programa['codigo_capes']) ?></dd>
                    <dt class="col-sm-5 cyra-muted">ID Sucupira</dt>
                    <dd class="col-sm-7 text-white"><?= esc($programa['sucupira_id'] ?? '-') ?></dd>
                    <dt class="col-sm-5 cyra-muted">Ano de início</dt>
                    <dd class="col-sm-7 text-white"><?= esc($programa['ano_inicio'] ?? '-') ?></dd>
                    <dt class="col-sm-5 cyra-muted">Modalidade</dt>
                    <dd class="col-sm-7 text-white"><?= esc($programa['modalidade'] ?? '-') ?></dd>
                    <dt class="col-sm-5 cyra-muted">Cursos</dt>
                    <dd class="col-sm-7 text-white"><?= esc(implode(', ', $listas['cursos'])) ?></dd>
                    <dt class="col-sm-5 cyra-muted">Graus</dt>
                    <dd class="col-sm-7 text-white"><?= esc(implode(', ', $listas['graus'])) ?></dd>
                </dl>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="cyra-panel ppg-card p-4 h-100">
                <h2 class="h5 text-white border-bottom border-light border-opacity-10 pb-3 mb-4"><i class="bi bi-building me-2 cyra-accent"></i>Instituição e contato</h2>
                <dl class="mb-0">
                    <dt class="cyra-muted mb-1">Instituição</dt>
                    <dd class="text-white mb-3"><?= esc($programa['instituicao_nome'] ?? '-') ?></dd>
                    <dt class="cyra-muted mb-1">Telefone</dt>
                    <dd class="text-white mb-3"><?= esc($programa['telefone'] ?? '-') ?></dd>
                    <dt class="cyra-muted mb-1">E-mail</dt>
                    <dd class="mb-3"><a class="cyra-accent" href="mailto:<?= esc($programa['email'] ?? '', 'attr') ?>"><?= esc($programa['email'] ?? '-') ?></a></dd>
                    <dt class="cyra-muted mb-1">Website</dt>
                    <dd class="mb-0"><a class="cyra-accent text-break" href="<?= esc($programa['website'] ?? '#', 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($programa['website'] ?? '-') ?></a></dd>
                </dl>
            </section>
        </div>

        <?php foreach ([
            'instituicoes_associadas' => 'Instituições associadas',
            'atos_normativos' => 'Atos normativos',
        ] as $chave => $rotulo) : ?>
            <?php if ($listas[$chave] !== []) : ?>
                <div class="col-md-6">
                    <section class="cyra-panel ppg-card p-4 h-100" id="<?= $chave === 'areas_concentracao' ? 'informacoes-academicas' : '' ?>">
                        <h2 class="h5 text-white mb-3"><i class="bi bi-list-check me-2 cyra-accent"></i><?= esc($rotulo) ?></h2>
                        <ul class="cyra-muted mb-0">
                            <?php foreach ($listas[$chave] as $item) : ?>
                                <li><?= esc(is_array($item) ? implode(' - ', $item) : $item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="col-12" id="informacoes-academicas">
            <section class="cyra-panel">
                <ul class="nav nav-tabs ppg-tabs flex-nowrap overflow-x-auto px-3 pt-3" id="ppg-tabs" role="tablist">
                    <?php foreach ([['academico','mortarboard','Estrutura acadêmica'],['docentes','people','Docentes vinculados'],['alunos','person-workspace','Alunos vinculados'],['rede','share','Rede de colaboração']] as $indice => [$idAba,$icone,$rotulo]) : ?>
                        <li class="nav-item" role="presentation"><button class="nav-link text-nowrap rounded-0 <?= $indice === 0 ? 'active' : '' ?>" id="tab-<?= $idAba ?>" data-bs-toggle="tab" data-bs-target="#painel-<?= $idAba ?>" type="button" role="tab" aria-controls="painel-<?= $idAba ?>" aria-selected="<?= $indice === 0 ? 'true' : 'false' ?>"><i class="bi bi-<?= $icone ?> me-2"></i><?= esc($rotulo) ?></button></li>
                    <?php endforeach; ?>
                </ul>
                <div class="tab-content p-3 p-md-4" id="ppg-tabs-content">
                    <section class="tab-pane fade show active" id="painel-academico" role="tabpanel" aria-labelledby="tab-academico" tabindex="0">
                        <div class="row g-4">
                            <div class="col-lg-4"><h3 class="h6 text-white mb-3"><i class="bi bi-award me-2 cyra-accent"></i>Graus acadêmicos</h3><div class="d-flex flex-wrap gap-2"><?php foreach ($listas['graus'] as $grau) : ?><span class="badge border border-info text-info rounded-0 px-3 py-2"><?= esc($grau) ?></span><?php endforeach; ?></div></div>
                            <div class="col-lg-4"><h3 class="h6 text-white mb-3"><i class="bi bi-bullseye me-2 cyra-accent"></i>Áreas de concentração</h3><ul class="cyra-muted mb-0"><?php foreach ($listas['areas_concentracao'] as $area) : ?><li class="mb-2"><?= esc(is_array($area) ? implode(' - ', $area) : $area) ?></li><?php endforeach; ?></ul></div>
                            <div class="col-lg-4"><h3 class="h6 text-white mb-3"><i class="bi bi-diagram-3 me-2 cyra-accent"></i>Linhas de pesquisa</h3><ul class="cyra-muted mb-0"><?php foreach ($linhas as $linha) : ?><li class="mb-2"><?= esc($linha['nome']) ?></li><?php endforeach; ?></ul></div>
                        </div>
                    </section>

                    <section class="tab-pane fade" id="painel-docentes" role="tabpanel" aria-labelledby="tab-docentes" tabindex="0">
            <section class="cyra-panel p-4" id="linhas-docentes">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <h2 class="h5 text-white mb-0"><i class="bi bi-diagram-3 me-2 cyra-accent"></i>Linhas de pesquisa e docentes</h2>
                    <span class="badge border border-light border-opacity-25 text-light rounded-0"><?= count($linhas) ?> linhas</span>
                </div>

                <?php if ($linhas === []) : ?>
                    <p class="cyra-muted mb-0">Nenhuma linha de pesquisa cadastrada para este programa.</p>
                <?php else : ?>
                    <div>
                        <?php foreach ($linhas as $indice => $linha) : ?>
                            <?php $docentes = $docentesPorLinha[$linha['id']] ?? []; ?>
                            <article class="ppg-line">
                                <header class="ppg-line-header p-3 p-md-4">
                                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <span class="ppg-line-number"><?= $indice + 1 ?></span>
                                            <div>
                                                <h3 class="h5 text-white mb-2"><?= esc($linha['nome']) ?></h3>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="ppg-meta"><i class="bi bi-bullseye me-1 cyra-accent"></i><?= esc($linha['area_concentracao'] ?? 'Área não informada') ?></span>
                                                    <span class="ppg-meta"><i class="bi bi-people me-1 cyra-accent"></i><?= count($docentes) ?> <?= count($docentes) === 1 ? 'docente' : 'docentes' ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-info rounded-0 flex-shrink-0" type="button" data-bs-toggle="collapse" data-bs-target="#inserir-docente-<?= (int) $linha['id'] ?>" aria-expanded="false" aria-controls="inserir-docente-<?= (int) $linha['id'] ?>"><i class="bi bi-person-plus me-2"></i>Adicionar docente</button>
                                    </div>
                                </header>

                                <div class="collapse ppg-add-form p-3 p-md-4" id="inserir-docente-<?= (int) $linha['id'] ?>">
                                    <form class="row g-2 align-items-end" method="post" action="<?= site_url('ppg/' . $programa['id'] . '/linhas/' . $linha['id'] . '/docentes') ?>">
                                        <?= csrf_field() ?>
                                        <div class="col-md"><label class="form-label small cyra-muted" for="lattes-id-<?= (int) $linha['id'] ?>">ID Lattes do novo docente</label><input class="form-control form-control-sm rounded-0" id="lattes-id-<?= (int) $linha['id'] ?>" name="lattes_id" type="text" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" placeholder="Informe os 16 dígitos" required></div>
                                        <div class="col-md-auto"><button class="btn btn-sm btn-info rounded-0 w-100" type="submit"><i class="bi bi-check-lg me-1"></i>Inserir na linha</button></div>
                                    </form>
                                </div>

                                <div class="p-3 p-md-4">
                                    <?php if ($docentes === []) : ?>
                                        <div class="text-center py-4"><i class="bi bi-person-dash display-6 cyra-muted"></i><p class="cyra-muted mt-2 mb-0">Nenhum docente vinculado a esta linha.</p></div>
                                    <?php else : ?>
                                        <div class="row g-3">
                                            <?php foreach ($docentes as $docente) : ?>
                                                <div class="col-md-6 col-xl-4">
                                                    <div class="ppg-teacher-card p-3">
                                                        <div class="d-flex gap-3">
                                                            <span class="ppg-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr((string) $docente['nome'], 0, 1))) ?></span>
                                                            <div class="min-w-0">
                                                                <a class="text-white fw-semibold text-decoration-none" href="<?= site_url('docent/' . $docente['id']) ?>"><?= esc($docente['nome']) ?> <i class="bi bi-arrow-up-right small cyra-accent"></i></a>
                                                                <small class="d-block cyra-muted mt-1"><?= esc($docente['tipo_vinculo']) ?> · <?= esc(match ((int) ($docente['genero'] ?? 0)) { 1 => 'Masculino', 2 => 'Feminino', default => 'Gênero não informado' }) ?></small>
                                                            </div>
                                                        </div>
                                                        <?php if (! empty($docente['instituicoes'])) : ?><p class="small cyra-muted mt-3 mb-2"><i class="bi bi-building me-1 cyra-accent"></i><?= esc($docente['instituicoes']) ?></p><?php endif; ?>
                                                        <div class="d-flex flex-wrap gap-1 mt-3">
                                                            <?php if (! empty($docente['lattes_id'])) : ?><span class="ppg-meta"><i class="bi bi-journal-text me-1"></i>Lattes <?= esc($docente['lattes_id']) ?></span><?php endif; ?>
                                                            <?php if (! empty($docente['orcid'])) : ?><span class="ppg-meta"><i class="bi bi-person-badge me-1"></i><?= esc($docente['orcid']) ?></span><?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
                    </section>

                    <section class="tab-pane fade" id="painel-alunos" role="tabpanel" aria-labelledby="tab-alunos" tabindex="0">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4"><h2 class="h5 text-white mb-0"><i class="bi bi-person-workspace me-2 cyra-accent"></i>Alunos de mestrado e doutorado</h2><span class="badge border border-light border-opacity-25 text-light rounded-0"><?= count($alunos) ?> vínculos</span></div>
                        <?php if ($alunos === []) : ?><div class="text-center py-5"><i class="bi bi-person-dash display-5 cyra-muted"></i><p class="cyra-muted mt-3 mb-0">Nenhum aluno de mestrado ou doutorado vinculado.</p></div><?php else : ?>
                            <?php foreach ([['ativos', 'hourglass-split', 'Em andamento', $alunosAtivos, 'text-warning'], ['concluidos', 'check-circle', 'Concluídos', $alunosConcluidos, 'text-success']] as [$grupoId, $icone, $tituloGrupo, $alunosGrupo, $cor]) : ?>
                                <section class="<?= $grupoId === 'concluidos' ? 'mt-5' : '' ?>">
                                    <div class="d-flex align-items-center gap-2 mb-3"><h3 class="h6 text-white mb-0"><i class="bi bi-<?= $icone ?> me-2 <?= $cor ?>"></i><?= esc($tituloGrupo) ?></h3><span class="badge border border-light border-opacity-25 text-light rounded-0"><?= count($alunosGrupo) ?></span></div>
                                    <?php if ($alunosGrupo === []) : ?><p class="cyra-muted small mb-0">Nenhum aluno nesta situação.</p><?php else : ?>
                                        <div class="row g-3"><?php foreach ($alunosGrupo as $aluno) : ?><div class="col-md-6 col-xl-4"><article class="ppg-student-card p-3 h-100"><div class="d-flex justify-content-between gap-2"><div><a class="text-white fw-semibold text-decoration-none" href="<?= site_url('docent/' . $aluno['id']) ?>"><?= esc($aluno['nome']) ?> <i class="bi bi-arrow-up-right small cyra-accent"></i></a><small class="d-block cyra-muted mt-1">Orientador: <a class="cyra-accent" href="<?= site_url('docent/' . $aluno['orientador_id']) ?>"><?= esc($aluno['orientador_nome']) ?></a></small></div><span class="badge <?= $aluno['tipo'] === 'Doutorado' ? 'text-bg-info' : 'bg-primary' ?> rounded-0 align-self-start"><?= esc($aluno['tipo']) ?></span></div><div class="d-flex flex-wrap gap-2 mt-3"><span class="ppg-meta <?= $cor ?>"><?= esc($tituloGrupo) ?></span><span class="ppg-meta"><?= esc($aluno['ano_inicio'] ?? '-') ?> – <?= esc($aluno['ano_final'] ?? '-') ?></span></div></article></div><?php endforeach; ?></div>
                                    <?php endif; ?>
                                </section>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </section>

                    <section class="tab-pane fade" id="painel-rede" role="tabpanel" aria-labelledby="tab-rede" tabindex="0">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><div><h2 class="h5 text-white mb-1"><i class="bi bi-share me-2 cyra-accent"></i>Rede de colaboração</h2><p class="small cyra-muted mb-0">Conexões calculadas a partir da coautoria nas produções importadas.</p></div><div class="d-flex gap-3 small"><span class="text-info"><i class="bi bi-circle-fill me-1"></i>Docente</span><span class="text-warning"><i class="bi bi-circle-fill me-1"></i>Aluno</span></div></div>
                        <svg id="rede-academica" role="img" aria-label="Rede de colaboração entre docentes e alunos"></svg>
                    </section>
                </div>
            </section>
        </div>
    </div>

    <?php if (! empty($programa['fonte_url'])) : ?>
        <p class="small cyra-muted mt-5 mb-0">Fonte: <a class="cyra-accent" href="<?= esc($programa['fonte_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer">Plataforma Sucupira</a></p>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const data = <?= json_encode($rede, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const svg = document.getElementById('rede-academica');
    if (!svg || !data.nodes.length) return;
    const ns = 'http://www.w3.org/2000/svg';
    const render = () => {
        const width = svg.clientWidth || 900, height = 540;
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
        svg.replaceChildren();
        const nodes = data.nodes.map((node, index) => ({...node, x: width / 2 + Math.cos(index * Math.PI * 2 / data.nodes.length) * width * .34, y: height / 2 + Math.sin(index * Math.PI * 2 / data.nodes.length) * height * .36}));
        const byId = Object.fromEntries(nodes.map(node => [node.id, node]));
        data.links.forEach(link => { const a=byId[link.source], b=byId[link.target]; if(!a||!b)return; const line=document.createElementNS(ns,'line'); line.setAttribute('x1',a.x);line.setAttribute('y1',a.y);line.setAttribute('x2',b.x);line.setAttribute('y2',b.y);line.setAttribute('stroke','rgba(170,193,212,.45)');line.setAttribute('stroke-width',Math.min(1+link.peso,6));svg.appendChild(line); });
        nodes.forEach(node => { const link=document.createElementNS(ns,'a');link.setAttribute('href',`<?= site_url('docent') ?>/${node.id}`); const circle=document.createElementNS(ns,'circle');circle.setAttribute('cx',node.x);circle.setAttribute('cy',node.y);circle.setAttribute('r',node.grupo==='docente'?10:7);circle.setAttribute('fill',node.grupo==='docente'?'#17bdc5':'#ffc107'); const title=document.createElementNS(ns,'title');title.textContent=node.nome;circle.appendChild(title);link.appendChild(circle); const text=document.createElementNS(ns,'text');text.setAttribute('x',node.x+13);text.setAttribute('y',node.y+4);text.setAttribute('fill','#edf7fb');text.setAttribute('font-size','11');text.textContent=node.nome.length>28?node.nome.slice(0,27)+'…':node.nome;link.appendChild(text);svg.appendChild(link); });
    };
    document.getElementById('tab-rede')?.addEventListener('shown.bs.tab', render, {once:true});
});
</script>

<?= view('layout/footer') ?>
