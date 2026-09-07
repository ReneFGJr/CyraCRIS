<?= view('layout/header', [
    'title' => 'Buscar indivíduos',
    'description' => 'Busca de indivíduos cadastrados no CyraCRIS.',
]) ?>

<main class="container py-5">
    <header class="mb-4">
        <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-people me-2"></i>Indivíduos</p>
        <h1 class="display-6 cyra-heading text-white mb-2">Buscar indivíduos</h1>
        <p class="cyra-muted mb-0">Consulte pessoas por nome, ID Lattes ou ORCID.</p>
    </header>

    <form class="cyra-panel p-3 p-lg-4 mb-4" method="get" action="<?= site_url('person/search') ?>" role="search">
        <label class="form-label text-white" for="person-search">Buscar indivíduo</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text rounded-0"><i class="bi bi-person-search"></i></span>
            <input class="form-control rounded-0" id="person-search" name="q" type="search" value="<?= esc($query, 'attr') ?>" placeholder="Digite o nome ou ID Lattes" required autofocus>
            <button class="btn btn-info rounded-0 px-4" type="submit"><i class="bi bi-search me-2"></i>Buscar</button>
        </div>
    </form>

    <?php if ($query !== '' && $pessoas === []) : ?>
        <div class="cyra-panel p-5 text-center">
            <i class="bi bi-person-x display-4 cyra-accent"></i>
            <h2 class="h4 text-white mt-3">Nenhum indivíduo encontrado</h2>
            <p class="cyra-muted mb-0">Tente pesquisar por outro nome ou identificador.</p>
        </div>
    <?php elseif ($pessoas !== []) : ?>
        <p class="cyra-muted mb-3"><?= count($pessoas) ?> <?= count($pessoas) === 1 ? 'resultado encontrado' : 'resultados encontrados' ?> para <strong class="text-white">“<?= esc($query) ?>”</strong>.</p>
        <div class="row g-3">
            <?php foreach ($pessoas as $pessoa) : ?>
                <div class="col-md-6 col-xl-4">
                    <a class="cyra-panel d-block h-100 p-4 text-decoration-none" href="<?= site_url('person/' . (int) $pessoa['id']) ?>">
                        <div class="d-flex gap-3 align-items-start">
                            <i class="bi bi-person-circle fs-2 cyra-accent"></i>
                            <div class="min-w-0">
                                <h2 class="h6 text-white mb-2"><?= esc($pessoa['nome']) ?></h2>
                                <?php if (! empty($pessoa['lattes_id'])) : ?><span class="small cyra-muted d-block">Lattes: <?= esc($pessoa['lattes_id']) ?></span><?php endif; ?>
                                <?php if (! empty($pessoa['orcid'])) : ?><span class="small cyra-muted d-block">ORCID: <?= esc($pessoa['orcid']) ?></span><?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?= view('layout/footer') ?>
