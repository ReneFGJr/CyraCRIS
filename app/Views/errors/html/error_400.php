<?= view('header/header', ['title' => 'Erro 400']) ?>
<main class="container py-5 text-center">
    <section class="cyra-panel p-4 p-md-5">
        <h1 class="cyra-heading">400</h1>
        <p class="cyra-muted">
            <?php if (ENVIRONMENT !== 'production') : ?>
                <?= nl2br(esc($message)) ?>
            <?php else : ?>
                <?= lang('Errors.sorryBadRequest') ?>
            <?php endif; ?>
        </p>
        <a class="btn btn-outline-info" href="<?= site_url('/') ?>">Voltar ao início</a>
    </section>
</main>
<?= view('header/footer') ?>