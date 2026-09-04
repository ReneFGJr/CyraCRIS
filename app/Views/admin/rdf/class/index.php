<?= view('layout/header', [
    'title'       => $title ?? 'Classes RDF',
    'description' => 'Administração das classes RDF.',
    'fluid'       => true,
]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-4 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-diagram-3 me-2"></i>RDF</p>
            <h1 class="display-6 cyra-heading text-white mb-2">Classes RDF</h1>
            <p class="cyra-muted mb-0">Cadastre e mantenha os dados da tabela <code>rdf_class</code>.</p>
        </div>
        <a class="btn btn-info rounded-0 px-4" href="<?= site_url('admin/rdf/class/new') ?>">
            <i class="bi bi-plus-lg me-2"></i>Nova classe
        </a>
    </header>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success rounded-0" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <nav class="mb-4" aria-label="Tipos de registros RDF">
        <div class="nav nav-tabs border-secondary">
            <a class="nav-link rounded-0 <?= $type === 'C' ? 'active' : 'text-white' ?>" href="<?= site_url('admin/rdf/class?type=C') ?>">
                <i class="bi bi-box me-2"></i>Classes
                <span class="badge text-bg-secondary ms-2"><?= (int) $totals['C'] ?></span>
            </a>
            <a class="nav-link rounded-0 <?= $type === 'P' ? 'active' : 'text-white' ?>" href="<?= site_url('admin/rdf/class?type=P') ?>">
                <i class="bi bi-link-45deg me-2"></i>Propriedades
                <span class="badge text-bg-secondary ms-2"><?= (int) $totals['P'] ?></span>
            </a>
        </div>
    </nav>

    <form class="cyra-panel p-3 p-lg-4 mb-4" method="get" action="<?= site_url('admin/rdf/class') ?>" role="search">
        <input type="hidden" name="type" value="<?= esc($type, 'attr') ?>">
        <div class="row g-3 align-items-end">
            <div class="col-md">
                <label class="form-label text-white" for="rdf-search">Buscar classe RDF</label>
                <div class="input-group">
                    <span class="input-group-text rounded-0"><i class="bi bi-search"></i></span>
                    <input class="form-control rounded-0" id="rdf-search" name="q" type="search"
                           value="<?= esc($query, 'attr') ?>" placeholder="Classe, descrição ou URL">
                </div>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button class="btn btn-info rounded-0" type="submit">Buscar</button>
                <?php if ($query !== '') : ?>
                    <a class="btn btn-outline-light rounded-0" href="<?= site_url('admin/rdf/class?type=' . $type) ?>" title="Limpar busca" aria-label="Limpar busca"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if ($classes === []) : ?>
        <div class="cyra-panel p-5 text-center">
            <i class="bi bi-diagram-3 display-4 cyra-accent"></i>
            <h2 class="h4 text-white mt-3">Nenhuma <?= $type === 'C' ? 'classe' : 'propriedade' ?> RDF encontrada</h2>
            <p class="cyra-muted mb-0"><?= $query !== '' ? 'Tente buscar por outro termo.' : 'Cadastre o primeiro registro deste tipo.' ?></p>
        </div>
    <?php else : ?>
        <div class="table-responsive cyra-panel">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="py-3">Classe</th>
                        <th class="py-3">Prefixo</th>
                        <th class="py-3">Equivalente</th>
                        <th class="py-3">Principal</th>
                        <th class="py-3">URL</th>
                        <th class="py-3">Atualização</th>
                        <th class="px-4 py-3 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $rdfClass) : ?>
                        <tr>
                            <td class="px-4 cyra-muted"><?= (int) $rdfClass['id_c'] ?></td>
                            <td><strong class="text-white"><?= esc($rdfClass['c_class']) ?></strong><small class="d-block cyra-muted text-truncate" style="max-width: 26rem"><?= esc($rdfClass['c_description']) ?></small></td>
                            <td><?= (int) $rdfClass['c_prefix'] ?></td>
                            <td><?= (int) $rdfClass['c_equivalent'] ?></td>
                            <td><?= (int) $rdfClass['c_class_main'] ?></td>
                            <td><?php if ($rdfClass['c_url'] !== '') : ?><a class="cyra-accent" href="<?= esc($rdfClass['c_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right"></i><span class="visually-hidden">Abrir URL</span></a><?php else : ?>—<?php endif; ?></td>
                            <td class="cyra-muted text-nowrap"><?= esc($rdfClass['c_url_update']) ?></td>
                            <td class="px-4 text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-info rounded-0" href="<?= site_url('admin/rdf/class/edit/' . (int) $rdfClass['id_c']) ?>" title="Editar" aria-label="Editar <?= esc($rdfClass['c_class'], 'attr') ?>"><i class="bi bi-pencil-square"></i></a>
                                <form class="d-inline" method="post" action="<?= site_url('admin/rdf/class/delete/' . (int) $rdfClass['id_c']) ?>" onsubmit="return confirm('Excluir esta classe RDF?');">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger rounded-0 ms-1" type="submit" title="Excluir" aria-label="Excluir <?= esc($rdfClass['c_class'], 'attr') ?>"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4"><?= $pager->links('rdf_classes', 'cyra_full') ?></div>
    <?php endif; ?>
</main>

<?= view('layout/footer', ['fluid' => true]) ?>
