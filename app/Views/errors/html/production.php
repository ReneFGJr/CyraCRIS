<?= view('header/header', ['title' => lang('Errors.whoops')]) ?>
<main class="container py-5 text-center">
    <section class="cyra-panel p-4 p-md-5">
        <h1 class="cyra-heading"><?= lang('Errors.whoops') ?></h1>
        <p class="cyra-muted"><?= lang('Errors.weHitASnag') ?></p>
        <a class="btn btn-outline-info" href="<?= site_url('/') ?>">Voltar ao início</a>
    </section>
</main>
<?= view('header/footer') ?>