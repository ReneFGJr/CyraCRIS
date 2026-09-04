<?= view('layout/header', [
    'title'       => $title ?? 'Possíveis nomes duplicados',
    'description' => 'Nomes com similaridade superior a 90% pelo algoritmo de Levenshtein.',
    'fluid'       => true,
]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-people-fill me-2"></i>Manutenção do cadastro</p>
            <h1 class="display-6 cyra-heading text-white mb-2">Possíveis nomes duplicados</h1>
            <p class="cyra-muted mb-0">Pares de nomes não agrupados com similaridade de Levenshtein superior a 90%.</p>
        </div>
        <a class="btn btn-outline-info rounded-0" href="<?= site_url('admin/report') ?>"><i class="bi bi-arrow-left me-2"></i>Voltar aos relatórios</a>
    </header>

    <?php if ($duplicates === []) : ?>
        <section class="cyra-panel p-5 text-center">
            <i class="bi bi-check-circle display-4 cyra-accent"></i>
            <h2 class="h4 text-white mt-3">Nenhuma possível duplicidade</h2>
            <p class="cyra-muted mb-0">Não foram encontrados nomes com similaridade superior a 90%.</p>
        </section>
    <?php else : ?>
        <p class="cyra-muted mb-3"><strong class="text-white"><?= count($duplicates) ?></strong> pares encontrados, em ordem alfabética.</p>
        <div class="table-responsive cyra-panel">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr><th class="px-4 py-3">Primeiro nome</th><th class="py-3">Segundo nome</th><th class="px-4 py-3 text-end">Similaridade</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($duplicates as $duplicate) : ?>
                        <tr>
                            <td class="px-4">
                                <a class="text-white fw-semibold" href="<?= site_url('admin/person/join') . '?person_id=' . (int) $duplicate['left']['id'] ?>" target="_blank" rel="noopener noreferrer">
                                    <?= esc($duplicate['left']['nome']) ?> <i class="bi bi-box-arrow-up-right ms-1 cyra-accent"></i>
                                </a>
                                <small class="d-block cyra-muted">ID <?= (int) $duplicate['left']['id'] ?> (Lattes: <?= esc($duplicate['left']['lattes_id'] ?: '—') ?>)</small>
                            </td>
                            <td>
                                <a class="text-white fw-semibold" href="<?= site_url('admin/person/join') . '?person_id=' . (int) $duplicate['right']['id'] ?>" target="_blank" rel="noopener noreferrer">
                                    <?= esc($duplicate['right']['nome']) ?> <i class="bi bi-box-arrow-up-right ms-1 cyra-accent"></i>
                                </a>
                                <small class="d-block cyra-muted">ID <?= (int) $duplicate['right']['id'] ?> (Lattes: <?= esc($duplicate['right']['lattes_id'] ?: '—') ?>)</small>
                            </td>
                            <td class="px-4 text-end"><span class="badge text-bg-info rounded-0"><?= number_format($duplicate['similarity'], 2, ',', '.') ?>%</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?= view('layout/footer', ['fluid' => true]) ?>
