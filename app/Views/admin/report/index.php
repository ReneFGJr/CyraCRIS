<?= view('layout/header', [
    'title'       => $title ?? 'Relatórios do sistema',
    'description' => 'Central de relatórios administrativos do CyraCRIS.',
    'fluid'       => true,
]) ?>

<style>
    .report-group { border-top: 3px solid var(--cyra-cyan); }
    .report-link { display: flex; align-items: center; gap: 1rem; min-height: 5rem; border: 1px solid rgba(151, 205, 225, .18); color: var(--cyra-ice); background: rgba(5, 19, 40, .35); text-decoration: none; transition: border-color .2s ease, background-color .2s ease, transform .2s ease; }
    .report-link:hover, .report-link:focus { border-color: var(--cyra-cyan); color: #fff; background: rgba(23, 189, 197, .1); transform: translateY(-2px); }
    .report-link-icon { display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; flex: 0 0 auto; color: var(--cyra-cyan); font-size: 1.35rem; }
</style>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-5">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-file-earmark-bar-graph me-2"></i>Administração</p>
            <h1 class="display-6 cyra-heading text-white mb-2">Relatórios do sistema</h1>
            <p class="cyra-muted mb-0">Selecione uma categoria e o relatório que deseja gerar.</p>
        </div>
        <a class="btn btn-outline-info rounded-0" href="<?= site_url('admin') ?>"><i class="bi bi-arrow-left me-2"></i>Voltar à administração</a>
    </header>

    <div class="row g-4">
        <?php foreach ($groups as $groupIndex => $group) : ?>
            <div class="col-12 col-xl-4">
                <section class="cyra-panel report-group p-4 h-100" aria-labelledby="report-group-<?= (int) $groupIndex ?>">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <i class="bi bi-<?= esc($group['icon'], 'attr') ?> fs-2 cyra-accent" aria-hidden="true"></i>
                        <h2 class="h4 text-white mb-0" id="report-group-<?= (int) $groupIndex ?>"><?= esc($group['title']) ?></h2>
                    </div>
                    <p class="cyra-muted mb-4"><?= esc($group['description']) ?></p>

                    <div class="d-grid gap-3">
                        <?php foreach ($group['reports'] as $report) : ?>
                            <a class="report-link p-3" href="<?= isset($report['url']) ? site_url($report['url']) : site_url('admin/report') . '?report=' . rawurlencode($report['slug']) ?>">
                                <span class="report-link-icon"><i class="bi bi-<?= esc($report['icon'], 'attr') ?>" aria-hidden="true"></i></span>
                                <span class="fw-semibold flex-grow-1"><?= esc($report['label']) ?></span>
                                <i class="bi bi-arrow-right cyra-accent" aria-hidden="true"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?= view('layout/footer', ['fluid' => true]) ?>
