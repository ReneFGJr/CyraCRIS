<?= view('layout/header', [
    'title'       => $title ?? 'Editar pessoa',
    'description' => 'Edição dos dados da pessoa.',
]) ?>

<main class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2">
                <i class="bi bi-pencil-square me-2"></i>Cadastro
            </p>
            <h1 class="display-6 cyra-heading text-white mb-2">Editar pessoa</h1>
            <p class="cyra-muted mb-0">Atualize os dados de <?= esc($person['nome']) ?>.</p>
        </div>
        <a class="btn btn-outline-info rounded-0" href="<?= site_url('person/' . (int) $person['id']) ?>">
            <i class="bi bi-arrow-left me-2"></i>Voltar ao perfil
        </a>
    </div>

    <?php if (session()->getFlashdata('erro')) : ?>
        <div class="alert alert-danger rounded-0" role="alert"><?= esc(session()->getFlashdata('erro')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success rounded-0" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <section class="cyra-panel p-4 p-lg-5">
        <div class="d-flex justify-content-end mb-4">
            <a class="btn btn-outline-info rounded-0" href="<?= site_url('admin/person/join') . '?person_id=' . (int) $person['id'] ?>">
                <i class="bi bi-people-fill me-2"></i>Agrupar nomes
            </a>
        </div>
        <form class="row g-4" method="post" action="<?= site_url('person/edit/' . (int) $person['id']) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="return_to" value="edit">

            <div class="col-12">
                <label class="form-label" for="person-nome">Nome</label>
                <input class="form-control rounded-0" id="person-nome" name="nome" type="text" maxlength="255"
                       value="<?= esc(old('nome', $person['nome']), 'attr') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="person-genero">Gênero</label>
                <select class="form-select rounded-0" id="person-genero" name="genero" required>
                    <?php foreach ([0 => 'Não informado', 1 => 'Masculino', 2 => 'Feminino'] as $value => $label) : ?>
                        <option value="<?= $value ?>" <?= (int) old('genero', $person['genero'] ?? 0) === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="person-email">E-mail</label>
                <input class="form-control rounded-0" id="person-email" name="email" type="email" maxlength="190"
                       value="<?= esc(old('email', $person['email'] ?? ''), 'attr') ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="person-cpf">CPF</label>
                <input class="form-control rounded-0" id="person-cpf" name="cpf" type="text" maxlength="14"
                       value="<?= esc(old('cpf', $person['cpf'] ?? ''), 'attr') ?>" placeholder="000.000.000-00">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="person-cracha">Crachá</label>
                <input class="form-control rounded-0" id="person-cracha" name="cracha" type="text" maxlength="50"
                       value="<?= esc(old('cracha', $person['cracha'] ?? ''), 'attr') ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="person-lattes">ID Lattes</label>
                <input class="form-control rounded-0" id="person-lattes" name="lattes_id" type="text"
                       inputmode="numeric" pattern="[0-9]{16}" maxlength="16"
                       value="<?= esc(old('lattes_id', $person['lattes_id'] ?? ''), 'attr') ?>" placeholder="16 dígitos">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="person-orcid">ORCID</label>
                <input class="form-control rounded-0" id="person-orcid" name="orcid" type="text" maxlength="19"
                       pattern="[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9Xx]{4}"
                       value="<?= esc(old('orcid', $person['orcid'] ?? ''), 'attr') ?>" placeholder="0000-0000-0000-0000">
            </div>

            <div class="col-12 d-flex flex-wrap justify-content-end gap-2 pt-2">
                <a class="btn btn-outline-light rounded-0" href="<?= site_url('admin/person') ?>">Cancelar</a>
                <button class="btn btn-info rounded-0 px-4" type="submit">
                    <i class="bi bi-check-lg me-2"></i>Salvar alterações
                </button>
            </div>
        </form>
    </section>
</main>

<?= view('layout/footer') ?>
