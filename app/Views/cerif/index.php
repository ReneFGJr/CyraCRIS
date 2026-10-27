<?php
/** @var array<int, array<string, mixed>> $pageItems */
$pageItems = $pageItems ?? [];
$type = $type ?? 'entidades';
$query = $query ?? '';
$baseQuery = ['tipo' => $type];
if ($query !== '') {
    $baseQuery['q'] = $query;
}
$pageUrl = static function (int $number) use ($baseQuery): string {
    return site_url('cerif') . '?' . http_build_query($baseQuery + ['page' => $number]);
};
?>
<?= view('header/header', [
    'title'       => $title ?? 'Modelo CERIF',
    'description' => 'Navegue pelo modelo de dados CERIF mantido pelo EuroCRIS.',
    'fluid'       => true,
]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="cyra-panel p-4 p-lg-5 mb-4">
        <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-diagram-3 me-2"></i>Modelo de dados</p>
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="display-5 cyra-heading text-white mb-2">CERIF</h1>
                <p class="cyra-muted mb-0">Explore entidades, atributos e relacionamentos do modelo de informação para pesquisa.</p>
            </div>
            <?php if ($version !== '') : ?>
                <span class="badge text-bg-info rounded-0 px-3 py-2"><?= esc($version) ?></span>
            <?php endif; ?>
        </div>
        <div class="row g-3 mt-3">
            <?php foreach ([
                ['entities', 'Entidades', 'bi-boxes'],
                ['relations', 'Relacionamentos', 'bi-diagram-2'],
                ['attributes', 'Atributos', 'bi-list-ul'],
                ['categories', 'Categorias', 'bi-tags'],
                ['workspaces', 'Áreas de trabalho', 'bi-grid'],
            ] as [$key, $label, $icon]) : ?>
                <div class="col-6 col-xl-3">
                    <div class="cyra-panel p-3 h-100">
                        <span class="small cyra-muted d-block"><i class="bi <?= esc($icon, 'attr') ?> me-2 cyra-accent"></i><?= esc($label) ?></span>
                        <strong class="h3 text-white mb-0"><?= number_format((int) ($counts[$key] ?? 0), 0, ',', '.') ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </header>

    <?php if (isset($error) && $error !== null) : ?>
        <div class="alert alert-warning rounded-0" role="alert"><?= esc($error) ?></div>
    <?php else : ?>
        <section class="cyra-panel p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div class="btn-group" role="group" aria-label="Tipo de dados CERIF">
                    <a class="btn rounded-0 <?= $type === 'entidades' ? 'btn-info' : 'btn-outline-info' ?>" href="<?= site_url('cerif') ?>?<?= esc(http_build_query(array_filter(['q' => $query])), 'attr') ?>">Entidades</a>
                    <a class="btn rounded-0 <?= $type === 'relacoes' ? 'btn-info' : 'btn-outline-info' ?>" href="<?= site_url('cerif') ?>?<?= esc(http_build_query(array_filter(['tipo' => 'relacoes', 'q' => $query])), 'attr') ?>">Relacionamentos</a>
                </div>
                <span class="small cyra-muted"><?= number_format($total, 0, ',', '.') ?> <?= $type === 'entidades' ? 'entidades' : 'relacionamentos' ?></span>
            </div>

            <form class="row g-2 mb-4" method="get" action="<?= site_url('cerif') ?>">
                <input type="hidden" name="tipo" value="<?= esc($type, 'attr') ?>">
                <div class="col-md">
                    <label class="visually-hidden" for="cerif-busca">Buscar no modelo</label>
                    <input class="form-control rounded-0" id="cerif-busca" name="q" type="search" value="<?= esc($query, 'attr') ?>" maxlength="120" placeholder="Buscar por nome, descrição ou entidade relacionada">
                </div>
                <div class="col-md-auto"><button class="btn btn-info rounded-0 w-100" type="submit"><i class="bi bi-search me-2"></i>Buscar</button></div>
                <?php if ($query !== '') : ?>
                    <div class="col-md-auto"><a class="btn btn-outline-light rounded-0 w-100" href="<?= site_url('cerif') ?>?<?= esc(http_build_query(['tipo' => $type]), 'attr') ?>">Limpar</a></div>
                <?php endif; ?>
            </form>

            <?php if ($pageItems === []) : ?>
                <p class="cyra-muted mb-0">Nenhum resultado encontrado para esta busca.</p>
            <?php elseif ($type === 'entidades') : ?>
                <div class="row g-3">
                    <?php foreach ($pageItems as $index => $entity) : ?>
                        <?php $detailsId = 'cerif-entidade-' . $index; ?>
                        <div class="col-12">
                            <article class="cyra-panel p-3 p-lg-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                    <div>
                                        <h2 class="h5 text-white mb-1"><?= esc($entity['caption'] !== '' ? $entity['caption'] : $entity['name']) ?></h2>
                                        <code class="cyra-accent"><?= esc($entity['name']) ?></code>
                                        <?php if ($entity['category'] !== '') : ?>
                                            <span class="small cyra-muted ms-2"><?= esc($entity['category']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="badge border border-info text-info rounded-0"><?= count($entity['attributes']) ?> atributos</span>
                                        <span class="badge border border-light border-opacity-25 text-light rounded-0"><?= count($entity['relations']) ?> relações</span>
                                    </div>
                                </div>
                                <details class="mt-3">
                                    <summary class="small cyra-accent" id="<?= esc($detailsId, 'attr') ?>">Ver atributos e relacionamentos</summary>
                                    <?php if ($entity['description'] !== '') : ?>
                                        <p class="small cyra-muted mt-3 mb-3"><?= nl2br(esc($entity['description'])) ?></p>
                                    <?php endif; ?>
                                    <h3 class="h6 text-white mt-3">Atributos</h3>
                                    <?php if ($entity['attributes'] === []) : ?>
                                        <p class="small cyra-muted">Nenhum atributo listado.</p>
                                    <?php else : ?>
                                        <div class="table-responsive">
                                            <table class="table table-dark table-sm align-middle mb-3">
                                                <thead><tr><th>Campo</th><th>Descrição</th><th>Domínio</th><th>Obrigatório</th></tr></thead>
                                                <tbody>
                                                    <?php foreach ($entity['attributes'] as $attribute) : ?>
                                                        <tr>
                                                            <td><code class="text-info"><?= esc($attribute['name']) ?></code></td>
                                                            <td><?= esc($attribute['caption']) ?></td>
                                                            <td><?= esc($attribute['domain'] !== '' ? $attribute['domain'] : '-') ?></td>
                                                            <td><?= $attribute['required'] ? 'Sim' : 'Não' ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                    <h3 class="h6 text-white mt-3">Relacionamentos</h3>
                                    <?php if ($entity['relations'] === []) : ?>
                                        <p class="small cyra-muted mb-0">Nenhum relacionamento listado.</p>
                                    <?php else : ?>
                                        <ul class="small cyra-muted mb-0">
                                            <?php foreach ($entity['relations'] as $relation) : ?>
                                                <li><span class="text-white"><?= esc($relation['name']) ?></span>: <?= esc($relation['source']) ?> <i class="bi bi-arrow-right cyra-accent"></i> <?= esc($relation['target']) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </details>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="row g-3">
                    <?php foreach ($pageItems as $relation) : ?>
                        <div class="col-12 col-xl-6">
                            <article class="cyra-panel p-3 h-100">
                                <h2 class="h6 text-white mb-1"><?= esc($relation['caption'] !== '' ? $relation['caption'] : $relation['name']) ?></h2>
                                <code class="small cyra-accent"><?= esc($relation['name']) ?></code>
                                <p class="my-3 text-white"><?= esc($relation['source']) ?> <i class="bi bi-arrow-right cyra-accent mx-2" aria-hidden="true"></i><?= esc($relation['target']) ?></p>
                                <p class="small cyra-muted mb-0">Obrigatório na origem: <?= $relation['parent_required'] ? 'sim' : 'não' ?> · Obrigatório no destino: <?= $relation['child_required'] ? 'sim' : 'não' ?></p>
                                <?php if ($relation['description'] !== '') : ?>
                                    <details>
                                        <summary class="small cyra-accent">Descrição</summary>
                                        <p class="small cyra-muted mt-2 mb-0"><?= nl2br(esc($relation['description'])) ?></p>
                                    </details>
                                <?php endif; ?>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($pageCount > 1) : ?>
                <nav class="d-flex justify-content-between align-items-center mt-4" aria-label="Paginação do modelo CERIF">
                    <?php if ($page > 1) : ?>
                        <a class="btn btn-outline-info rounded-0" href="<?= esc($pageUrl($page - 1), 'attr') ?>"><i class="bi bi-arrow-left me-2"></i>Anterior</a>
                    <?php else : ?>
                        <span></span>
                    <?php endif; ?>
                    <span class="small cyra-muted">Página <?= $page ?> de <?= $pageCount ?></span>
                    <?php if ($page < $pageCount) : ?>
                        <a class="btn btn-outline-info rounded-0" href="<?= esc($pageUrl($page + 1), 'attr') ?>">Próxima<i class="bi bi-arrow-right ms-2"></i></a>
                    <?php else : ?>
                        <span></span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <footer class="small cyra-muted mt-4">
        <p class="mb-1">Modelo CERIF mantido por <a class="cyra-accent" href="https://www.eurocris.org/" target="_blank" rel="noopener noreferrer">euroCRIS</a>, versão <?= esc($version !== '' ? $version : 'atual') ?>.</p>
        <p class="mb-0">Fonte: <a class="cyra-accent" href="https://github.com/EuroCRIS/CERIF-DataModel" target="_blank" rel="noopener noreferrer">EuroCRIS/CERIF-DataModel</a> · Licença <a class="cyra-accent" href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">Creative Commons Atribuição 4.0 Internacional (CC BY 4.0)</a>.</p>
    </footer>
</main>

<?= view('header/footer', ['fluid' => true]) ?>
