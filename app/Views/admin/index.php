<?= view('layout/header', [
    'title'       => $title ?? 'Administração',
    'description' => 'Painel administrativo do CyraCRIS.',
    'fluid'       => true,
]) ?>

<style>
    .admin-hero {
        border: 1px solid rgba(23, 189, 197, .28);
        background: linear-gradient(120deg, rgba(18, 102, 177, .3), rgba(5, 19, 40, .72));
        box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, .16);
    }
    .admin-card {
        display: flex;
        height: 100%;
        flex-direction: column;
        border: 1px solid rgba(151, 205, 225, .18);
        color: inherit;
        background: rgba(5, 19, 40, .48);
        text-decoration: none;
        transition: transform .2s ease, border-color .2s ease, background .2s ease;
    }
    .admin-card:hover, .admin-card:focus {
        transform: translateY(-3px);
        border-color: rgba(23, 189, 197, .55);
        color: inherit;
        background: rgba(18, 102, 177, .18);
    }
    .admin-icon {
        display: grid;
        width: 3.25rem;
        height: 3.25rem;
        place-items: center;
        border: 1px solid rgba(23, 189, 197, .3);
        color: var(--cyra-cyan);
        background: rgba(23, 189, 197, .08);
        font-size: 1.35rem;
    }
</style>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="admin-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-4">
            <div>
                <p class="text-uppercase fw-bold small cyra-accent mb-3">
                    <i class="bi bi-speedometer2 me-2"></i>Área administrativa
                </p>
                <h1 class="display-5 cyra-heading text-white mb-3">Olá, <?= esc($givename) ?></h1>
                <p class="cyra-muted mb-0">Gerencie os recursos e acompanhe os dados do CyraCRIS.</p>
            </div>
            <span class="badge border border-info text-info rounded-0 px-3 py-2">
                <i class="bi bi-shield-check me-2"></i><?= esc($username) ?>
            </span>
        </div>
    </header>

    <section aria-labelledby="admin-resources-title">
        <div class="mb-4">
            <p class="text-uppercase fw-bold small cyra-accent mb-2">Acesso rápido</p>
            <h2 class="h3 cyra-heading text-white mb-0" id="admin-resources-title">Recursos do sistema</h2>
        </div>

        <div class="row g-3 g-xl-4">
            <div class="col-12 col-md-6 col-xl-4">
                <a class="admin-card p-4" href="<?= site_url('ppg') ?>">
                    <div class="admin-icon mb-4"><i class="bi bi-mortarboard"></i></div>
                    <h3 class="h5 text-white">Programas de pós-graduação</h3>
                    <p class="cyra-muted mb-4">Consulte programas, linhas de pesquisa, docentes e alunos vinculados.</p>
                    <span class="mt-auto cyra-accent">Acessar <i class="bi bi-arrow-right ms-2"></i></span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <a class="admin-card p-4" href="<?= site_url('profile') ?>">
                    <div class="admin-icon mb-4"><i class="bi bi-person-vcard"></i></div>
                    <h3 class="h5 text-white">Meu perfil</h3>
                    <p class="cyra-muted mb-4">Visualize os dados da conta atualmente autenticada.</p>
                    <span class="mt-auto cyra-accent">Acessar <i class="bi bi-arrow-right ms-2"></i></span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <a class="admin-card p-4" href="<?= site_url('admin/person/') ?>">
                    <div class="admin-icon mb-4"><i class="bi bi-people"></i></div>
                    <h3 class="h5 text-white">Administrar pessoas</h3>
                    <p class="cyra-muted mb-4">Consulte e importe registros da tabela Person.</p>
                    <span class="mt-auto cyra-accent">Acessar <i class="bi bi-arrow-right ms-2"></i></span>
                </a>
            </div>
        </div>
    </section>
</main>

<?= view('layout/footer', ['fluid' => true]) ?>
