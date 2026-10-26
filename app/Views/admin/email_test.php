<?= view('header/header', ['title' => $title, 'fluid' => true]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="cyra-heading h2 mb-0"><i class="bi bi-envelope-check me-2" aria-hidden="true"></i><?= esc($title) ?></h1>
        <a class="btn btn-outline-info" href="<?= site_url('admin') ?>">Voltar à administração</a>
    </header>

    <?php if ($message = session()->getFlashdata('success')): ?>
        <div class="alert alert-success" role="status"><?= esc($message) ?></div>
    <?php endif ?>
    <?php if ($message = session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" role="alert"><?= esc($message) ?></div>
    <?php endif ?>

    <section class="cyra-panel p-4">
        <p>Envie uma mensagem de teste usando as configurações de e-mail do .env.</p>
        <dl class="row">
            <dt class="col-sm-3">Protocolo</dt><dd class="col-sm-9"><?= esc($protocol) ?></dd>
            <dt class="col-sm-3">Remetente</dt><dd class="col-sm-9"><?= esc($fromEmail ?: 'Não configurado') ?></dd>
            <?php if ($protocol === 'smtp'): ?>
                <dt class="col-sm-3">Servidor SMTP</dt><dd class="col-sm-9"><?= esc($host ?: 'Não configurado') ?> (porta <?= esc((string) $port) ?>)</dd>
            <?php endif ?>
        </dl>
        <?php if ($protocol === 'sendmail'): ?>
            <p class="cyra-muted">O protocolo sendmail utiliza o programa de envio instalado no servidor. As configurações SMTP são utilizadas quando EMAIL_TYPE é smtp.</p>
        <?php endif ?>
        <form method="post" action="<?= site_url('admin/email_test') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="recipient">E-mail de destino</label>
                <input class="form-control" type="email" id="recipient" name="recipient" value="<?= esc(old('recipient', ''), 'attr') ?>" maxlength="254" required autocomplete="email">
            </div>
            <button class="btn btn-info" type="submit"><i class="bi bi-send me-2" aria-hidden="true"></i>Enviar e-mail de teste</button>
        </form>
    </section>
</main>

<?= view('header/footer', ['fluid' => true]) ?>
