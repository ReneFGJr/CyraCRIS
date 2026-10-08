<?= view('header/header', ['title' => $title, 'fluid' => true]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="small text-uppercase fw-bold cyra-accent mb-2">Área administrativa</p>
            <h1 class="cyra-heading h2 mb-0"><i class="bi bi-<?= esc($icon, 'attr') ?> me-2" aria-hidden="true"></i><?= esc($title) ?></h1>
        </div>
        <a class="btn btn-outline-info" href="<?= site_url('admin') ?>"><i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Voltar à administração</a>
    </header>
    <section class="cyra-panel p-4">
        <p class="cyra-muted mb-0">O formulário de marcação ainda não está disponível.</p>
    </section>
</main>

<?= view('header/footer', ['fluid' => true]) ?>
