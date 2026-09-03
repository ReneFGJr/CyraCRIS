<?= view('layout/header', [
    'title'       => $title ?? 'Entrar',
    'description' => 'Autenticação no CyraCRIS.',
]) ?>

<main class="container py-5">
    <div class="row justify-content-center py-lg-5">
        <div class="col-md-8 col-lg-5">
            <section class="cyra-panel p-4 p-md-5" aria-labelledby="login-title">
                <p class="text-uppercase fw-bold small cyra-accent mb-3">
                    <i class="bi bi-shield-lock me-2"></i>Área restrita
                </p>
                <h1 class="h2 cyra-heading text-white mb-4" id="login-title">Entrar no CyraCRIS</h1>

                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger rounded-0" role="alert">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('login') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="user">Usuário</label>
                        <input class="form-control rounded-0" id="user" name="user" type="text"
                            value="<?= esc((string) session()->getFlashdata('login_user'), 'attr') ?>"
                            autocomplete="username" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="pwd">Senha</label>
                        <input class="form-control rounded-0" id="pwd" name="pwd" type="password"
                            autocomplete="current-password" required>
                    </div>
                    <button class="btn btn-info rounded-0 px-4 w-100" type="submit">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                    </button>
                </form>
            </section>
        </div>
    </div>
</main>

<?= view('layout/footer') ?>
