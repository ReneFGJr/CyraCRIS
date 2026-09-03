<?= view('layout/header', [
    'title'       => $title ?? 'Administrar pessoas',
    'description' => 'Administração da tabela Person.',
    'fluid'       => true,
]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-4 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2">
                <i class="bi bi-people me-2"></i>Administração
            </p>
            <h1 class="display-6 cyra-heading text-white mb-2">Pessoas</h1>
            <p class="cyra-muted mb-0"><?= (int) $total ?> registros cadastrados na tabela Person.</p>
        </div>
        <a class="btn btn-info rounded-0 px-4" href="<?= site_url('admin/person/inport') ?>">
            <i class="bi bi-upload me-2"></i>Importar dados
        </a>
    </header>

    <?php if ($persons === []) : ?>
        <div class="cyra-panel p-5 text-center">
            <i class="bi bi-person-x display-4 cyra-accent"></i>
            <h2 class="h4 text-white mt-3">Nenhuma pessoa cadastrada</h2>
            <p class="cyra-muted mb-0">Use o botão “Importar dados” para adicionar os primeiros registros.</p>
        </div>
    <?php else : ?>
        <div class="table-responsive cyra-panel">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3" scope="col">ID</th>
                        <th class="py-3" scope="col">Nome</th>
                        <th class="py-3" scope="col">ID Lattes</th>
                        <th class="py-3" scope="col">E-mail</th>
                        <th class="py-3" scope="col">Crachá</th>
                        <th class="px-4 py-3" scope="col">Cadastro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($persons as $person) : ?>
                        <tr>
                            <td class="px-4 cyra-muted"><?= (int) $person['id'] ?></td>
                            <td class="fw-semibold text-white"><?= esc($person['name']) ?></td>
                            <td><?= esc($person['lattes_id'] ?: '—') ?></td>
                            <td><?= esc($person['email'] ?: '—') ?></td>
                            <td><?= esc($person['cracha'] ?: '—') ?></td>
                            <td class="px-4 cyra-muted"><?= esc($person['created_at'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4"><?= $pager->links('persons', 'default_full') ?></div>
    <?php endif; ?>
</main>

<?= view('layout/footer', ['fluid' => true]) ?>
