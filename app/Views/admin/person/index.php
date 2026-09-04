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

    <form class="cyra-panel p-3 p-lg-4 mb-4" method="get" action="<?= site_url('admin/person') ?>" role="search">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-7">
                <label class="form-label text-white" for="person-search">Buscar pessoa</label>
                <div class="input-group">
                    <span class="input-group-text rounded-0"><i class="bi bi-search"></i></span>
                    <input class="form-control rounded-0" id="person-search" name="q" type="search"
                           value="<?= esc($query) ?>" placeholder="Digite nome, e-mail, ID Lattes ou crachá">
                </div>
            </div>
            <div class="col-12 col-sm-7 col-lg-3">
                <label class="form-label text-white" for="person-search-field">Filtrar por</label>
                <select class="form-select rounded-0" id="person-search-field" name="field">
                    <?php foreach ($searchableFields as $value => $label) : ?>
                        <option value="<?= esc($value) ?>" <?= $field === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-5 col-lg-2 d-flex gap-2">
                <button class="btn btn-info rounded-0 flex-grow-1" type="submit">Buscar</button>
                <?php if ($query !== '') : ?>
                    <a class="btn btn-outline-light rounded-0" href="<?= site_url('admin/person') ?>"
                       title="Limpar busca" aria-label="Limpar busca"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if ($persons === []) : ?>
        <div class="cyra-panel p-5 text-center">
            <i class="bi bi-person-x display-4 cyra-accent"></i>
            <?php if ($query !== '') : ?>
                <h2 class="h4 text-white mt-3">Nenhuma pessoa encontrada</h2>
                <p class="cyra-muted mb-3">Tente outro termo ou pesquise em todos os campos.</p>
                <a class="btn btn-outline-info rounded-0" href="<?= site_url('admin/person') ?>">Limpar busca</a>
            <?php else : ?>
                <h2 class="h4 text-white mt-3">Nenhuma pessoa cadastrada</h2>
                <p class="cyra-muted mb-0">Use o botão “Importar dados” para adicionar os primeiros registros.</p>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <?php if ($query !== '') : ?>
            <p class="cyra-muted mb-3">
                <?= (int) $total ?> <?= (int) $total === 1 ? 'resultado encontrado' : 'resultados encontrados' ?>
                para <strong class="text-white">“<?= esc($query) ?>”</strong>.
            </p>
        <?php endif; ?>
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
