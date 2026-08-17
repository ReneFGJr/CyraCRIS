<?php
/** @var array<string, mixed> $programa */
/** @var array<string, array<int, mixed>> $listas */
/** @var array<int, array<string, mixed>> $linhas */
/** @var array<int, array<int, array<string, mixed>>> $docentesPorLinha */
?>
<?= view('layout/header', [
    'title' => $programa['nome'],
    'description' => 'Detalhes do programa de pós-graduação ' . $programa['nome'] . '.',
]) ?>
<?= view('layout/navbar') ?>

<main class="container py-5">
    <a class="btn btn-sm btn-outline-info rounded-0 mb-4" href="<?= site_url('ppg') ?>">
        <i class="bi bi-arrow-left me-2"></i>Voltar para programas
    </a>

    <header class="mb-5">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="badge text-bg-info rounded-0">CAPES <?= esc($programa['nota_capes'] ?? '-') ?></span>
            <span class="badge border border-success text-success rounded-0"><i class="bi bi-check-circle me-1"></i><?= esc($programa['situacao'] ?? '-') ?></span>
        </div>
        <h1 class="cyra-heading text-white mb-2"><?= esc($programa['nome']) ?></h1>
        <p class="lead cyra-muted mb-0"><?= esc($programa['instituicao_nome'] ?? '-') ?> (<?= esc($programa['instituicao_sigla'] ?? '-') ?>)</p>
    </header>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="cyra-panel p-4 h-100">
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
            <section class="cyra-panel p-4 h-100">
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
            'areas_concentracao' => 'Áreas de concentração',
            'linhas_pesquisa' => 'Linhas de pesquisa',
            'instituicoes_associadas' => 'Instituições associadas',
            'atos_normativos' => 'Atos normativos',
        ] as $chave => $rotulo) : ?>
            <?php if ($listas[$chave] !== []) : ?>
                <div class="col-md-6">
                    <section class="cyra-panel p-4 h-100">
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

        <div class="col-12">
            <section class="cyra-panel p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <h2 class="h5 text-white mb-0"><i class="bi bi-diagram-3 me-2 cyra-accent"></i>Linhas de pesquisa e docentes</h2>
                    <span class="badge border border-light border-opacity-25 text-light rounded-0"><?= count($linhas) ?> linhas</span>
                </div>

                <?php if ($linhas === []) : ?>
                    <p class="cyra-muted mb-0">Nenhuma linha de pesquisa cadastrada para este programa.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <caption class="visually-hidden">Linhas de pesquisa e docentes vinculados</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Linha de pesquisa</th>
                                    <th scope="col">Área de concentração</th>
                                    <th scope="col">Docentes vinculados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($linhas as $linha) : ?>
                                    <tr>
                                        <td class="text-white fw-semibold"><?= esc($linha['nome']) ?></td>
                                        <td class="cyra-muted"><?= esc($linha['area_concentracao'] ?? '-') ?></td>
                                        <td>
                                            <?php $docentes = $docentesPorLinha[$linha['id']] ?? []; ?>
                                            <?php if ($docentes === []) : ?>
                                                <span class="cyra-muted"><i class="bi bi-person-dash me-1"></i>Não informado</span>
                                            <?php else : ?>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($docentes as $docente) : ?>
                                                        <li class="text-white"><i class="bi bi-person me-2 cyra-accent"></i><?= esc($docente['nome']) ?> <small class="cyra-muted">(<?= esc($docente['tipo_vinculo']) ?>)</small></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <?php if (! empty($programa['fonte_url'])) : ?>
        <p class="small cyra-muted mt-5 mb-0">Fonte: <a class="cyra-accent" href="<?= esc($programa['fonte_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer">Plataforma Sucupira</a></p>
    <?php endif; ?>
</main>

<?= view('layout/footer') ?>
