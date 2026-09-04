<?php
$editing = is_array($rdfClass);
$value = static fn (string $field, mixed $default = ''): mixed => old($field, $editing ? ($rdfClass[$field] ?? $default) : $default);
$errors = session()->getFlashdata('errors');
?>
<?= view('layout/header', [
    'title'       => $title ?? ($editing ? 'Editar classe RDF' : 'Nova classe RDF'),
    'description' => $editing ? 'Edição de classe RDF.' : 'Cadastro de classe RDF.',
]) ?>

<main class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-diagram-3 me-2"></i>RDF</p>
            <h1 class="display-6 cyra-heading text-white mb-0"><?= $editing ? 'Editar classe RDF' : 'Nova classe RDF' ?></h1>
        </div>
        <a class="btn btn-outline-info rounded-0" href="<?= site_url('admin/rdf/class') ?>"><i class="bi bi-arrow-left me-2"></i>Voltar</a>
    </div>

    <?php if (is_array($errors) && $errors !== []) : ?>
        <div class="alert alert-danger rounded-0" role="alert">
            <strong>Revise os dados informados:</strong>
            <ul class="mb-0 mt-2"><?php foreach ($errors as $error) : ?><li><?= esc($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <section class="cyra-panel p-4 p-lg-5">
        <form class="row g-4" method="post" action="<?= $editing ? site_url('admin/rdf/class/edit/' . (int) $rdfClass['id_c']) : site_url('admin/rdf/class') ?>">
            <?= csrf_field() ?>

            <div class="col-md-9">
                <label class="form-label" for="c-class">Classe</label>
                <input class="form-control rounded-0" id="c-class" name="c_class" type="text" maxlength="200" value="<?= esc($value('c_class'), 'attr') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="c-type">Tipo</label>
                <select class="form-select rounded-0" id="c-type" name="c_type" required>
                    <option value="C" <?= $value('c_type', 'C') === 'C' ? 'selected' : '' ?>>Classe (C)</option>
                    <option value="P" <?= $value('c_type', 'C') === 'P' ? 'selected' : '' ?>>Propriedade (P)</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="c-prefix">Prefixo</label>
                <select class="form-select rounded-0" id="c-prefix" name="c_prefix" required>
                    <option value="">Selecione um prefixo</option>
                    <?php foreach ($prefixes as $prefix) : ?>
                        <option value="<?= (int) $prefix['id_prefix'] ?>" <?= (int) $value('c_prefix', 0) === (int) $prefix['id_prefix'] ? 'selected' : '' ?>>
                            <?= esc($prefix['prefix_ref']) ?> — <?= esc($prefix['prefix_url']) ?><?= (int) $prefix['prefix_ativo'] === 1 ? '' : ' (inativo)' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="c-equivalent">Classe equivalente</label>
                <input class="form-control rounded-0" id="c-equivalent" name="c_equivalent" type="number" min="0" value="<?= (int) $value('c_equivalent', 0) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="c-main">Classe principal</label>
                <input class="form-control rounded-0" id="c-main" name="c_class_main" type="number" min="0" value="<?= (int) $value('c_class_main', 0) ?>" required>
            </div>

            <div class="col-md-8">
                <label class="form-label" for="c-url">URL</label>
                <input class="form-control rounded-0" id="c-url" name="c_url" type="text" maxlength="100" value="<?= esc($value('c_url'), 'attr') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="c-url-update">Atualização da URL</label>
                <input class="form-control rounded-0" id="c-url-update" name="c_url_update" type="date" value="<?= esc($value('c_url_update', '1900-01-01'), 'attr') ?>" required>
            </div>

            <div class="col-12">
                <label class="form-label" for="c-description">Descrição</label>
                <textarea class="form-control rounded-0" id="c-description" name="c_description" rows="6"><?= esc($value('c_description')) ?></textarea>
            </div>

            <?php if ($editing) : ?>
                <div class="col-12"><small class="cyra-muted">ID <?= (int) $rdfClass['id_c'] ?> · Criada em <?= esc($rdfClass['c_created']) ?></small></div>
            <?php endif; ?>

            <div class="col-12 d-flex justify-content-end gap-2">
                <a class="btn btn-outline-light rounded-0" href="<?= site_url('admin/rdf/class') ?>">Cancelar</a>
                <button class="btn btn-info rounded-0 px-4" type="submit"><i class="bi bi-check-lg me-2"></i>Salvar</button>
            </div>
        </form>
    </section>
</main>

<?= view('layout/footer') ?>
