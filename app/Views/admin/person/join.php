<?= view('layout/header', [
    'title'       => $title ?? 'Agrupar nomes',
    'description' => 'Agrupamento de registros de pessoas com nomes semelhantes.',
    'fluid'       => true,
]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-people-fill me-2"></i>Pessoas</p>
            <div class="h5 fw-semibold text-white mb-2">Agrupar nomes</div>
            <h1 class="display-6 cyra-heading text-white mb-2"><?= esc($person['nome']) ?></h1>
            <p class="cyra-muted mb-0">ID <?= (int) $person['id'] ?> é o cadastro principal. Selecione os nomes que devem remeter para ele.</p>
        </div>
        <a class="btn btn-outline-info rounded-0" href="<?= site_url('person/edit/' . (int) $person['id']) ?>"><i class="bi bi-arrow-left me-2"></i>Voltar à edição</a>
    </header>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger rounded-0" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success rounded-0" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <section class="cyra-panel p-3 p-md-4 mb-4">
        <span class="cyra-muted me-2">Palavras pesquisadas:</span>
        <?php foreach ($words as $word) : ?><span class="badge text-bg-info rounded-0 me-1"><?= esc($word) ?></span><?php endforeach; ?>
    </section>

    <?php if ($matches === []) : ?>
        <section class="cyra-panel p-5 text-center">
            <i class="bi bi-person-x display-4 cyra-accent"></i>
            <h2 class="h4 text-white mt-3">Nenhum nome correspondente</h2>
            <p class="cyra-muted mb-0">Não foram encontrados outros registros contendo as palavras pesquisadas.</p>
        </section>
    <?php else : ?>
        <div class="table-responsive cyra-panel">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead><tr><th class="px-4 py-3">ID</th><th class="py-3">Nome</th><th class="py-3">Similaridade</th><th class="py-3">ID Lattes</th><th class="py-3">ORCID</th><th class="py-3">Agrupado com</th><th class="px-4 py-3 text-end">Ação</th></tr></thead>
                <tbody>
                    <?php foreach ($matches as $match) : ?>
                        <tr>
                            <td class="px-4 cyra-muted"><?= (int) $match['id'] ?></td>
                            <td class="fw-semibold text-white"><?= esc($match['nome']) ?></td>
                            <td><span class="badge text-bg-info rounded-0"><?= number_format((float) $match['similarity'], 2, ',', '.') ?>%</span></td>
                            <td><?= esc($match['lattes_id'] ?: '—') ?></td>
                            <td><?= esc($match['orcid'] ?: '—') ?></td>
                            <td><?= (int) $match['use'] > 0 ? (int) $match['use'] : '—' ?></td>
                            <td class="px-4 text-end">
                                <form method="post" action="<?= site_url('admin/person/join') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="person_id" value="<?= (int) $person['id'] ?>">
                                    <input type="hidden" name="use_id" value="<?= (int) $match['id'] ?>">
                                    <button class="btn btn-sm btn-info rounded-0" type="submit" onclick="return confirm('Fazer <?= esc($match['nome'], 'attr') ?> remeter ao cadastro principal ID <?= (int) $person['id'] ?>?');">
                                        <i class="bi bi-link-45deg me-1"></i>Join
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?= view('layout/footer', ['fluid' => true]) ?>
