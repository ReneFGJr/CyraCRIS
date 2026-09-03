<?= view('layout/header', [
    'title'       => $title ?? 'Meu perfil',
    'description' => 'Dados do usuário autenticado no CyraCRIS.',
    'fluid'       => true,
]) ?>

<style>
    .profile-shell { width: 100%; }
    .profile-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(23, 189, 197, .28);
        background: linear-gradient(120deg, rgba(18, 102, 177, .34), rgba(5, 19, 40, .72));
        box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, .18);
    }
    .profile-hero::after {
        position: absolute;
        width: 22rem;
        height: 22rem;
        right: -7rem;
        bottom: -14rem;
        border-radius: 50%;
        background: rgba(23, 189, 197, .14);
        content: '';
    }
    .profile-avatar {
        display: grid;
        width: 6.5rem;
        height: 6.5rem;
        flex: 0 0 6.5rem;
        place-items: center;
        border: 2px solid rgba(255, 255, 255, .72);
        border-radius: 50%;
        color: #061328;
        background: linear-gradient(145deg, #edf7fb, #17bdc5);
        font: 700 2.5rem Georgia, serif;
        box-shadow: 0 .75rem 2rem rgba(0, 0, 0, .24);
    }
    .profile-status {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .4rem .75rem;
        border: 1px solid rgba(23, 189, 197, .38);
        color: #bdf7f9;
        background: rgba(23, 189, 197, .1);
    }
    .profile-status-dot {
        width: .5rem;
        height: .5rem;
        border-radius: 50%;
        background: #20e3b2;
        box-shadow: 0 0 .75rem #20e3b2;
    }
    .profile-card {
        height: 100%;
        border: 1px solid rgba(151, 205, 225, .18);
        background: rgba(5, 19, 40, .48);
        transition: border-color .2s ease, transform .2s ease;
    }
    .profile-card:hover {
        transform: translateY(-2px);
        border-color: rgba(23, 189, 197, .5);
    }
    .profile-card-icon {
        display: grid;
        width: 2.75rem;
        height: 2.75rem;
        flex: 0 0 2.75rem;
        place-items: center;
        border: 1px solid rgba(23, 189, 197, .28);
        color: var(--cyra-cyan);
        background: rgba(23, 189, 197, .08);
    }
    .profile-label {
        color: var(--cyra-muted);
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .profile-value { font-size: 1rem; line-height: 1.55; }
</style>

<?php
$initial = function_exists('mb_substr') ? mb_substr($givename, 0, 1) : substr($givename, 0, 1);
$formatLabel = static function (string $field): string {
    $parts = array_map(
        static fn (string $part): string => ucwords(str_replace(['_', '-'], ' ', $part)),
        explode(' / ', $field),
    );

    return implode(' · ', $parts);
};
?>

<main class="profile-shell container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <section class="profile-hero p-4 p-md-5 mb-4" aria-labelledby="profile-title">
        <div class="position-relative d-flex flex-column flex-md-row align-items-md-center gap-4" style="z-index: 1">
            <div class="profile-avatar" aria-hidden="true"><?= esc(mb_strtoupper($initial)) ?></div>
            <div class="flex-grow-1">
                <div class="profile-status small mb-3">
                    <span class="profile-status-dot"></span>Conta conectada
                </div>
                <h1 class="display-5 cyra-heading text-white mb-2" id="profile-title"><?= esc($givename) ?></h1>
                <p class="cyra-muted mb-0">
                    <i class="bi bi-person me-2"></i><?= esc($username) ?>
                    <?php if ($loginAt !== '') : ?>
                        <span class="mx-2 opacity-50">•</span>
                        <i class="bi bi-clock me-2"></i>Login em <?= esc($loginAt) ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-self-md-end">
                <a class="btn btn-outline-info rounded-0 px-4" href="<?= site_url('/') ?>">
                    <i class="bi bi-arrow-left me-2"></i>Voltar ao início
                </a>
                <form action="<?= site_url('logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-light rounded-0 px-4" type="submit">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section aria-labelledby="account-data-title">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="text-uppercase fw-bold small cyra-accent mb-2">Informações da conta</p>
                <h2 class="h3 cyra-heading text-white mb-0" id="account-data-title">Dados do usuário</h2>
            </div>
            <span class="small cyra-muted"><?= count($profileData) ?> campos disponíveis</span>
        </div>

        <?php if ($profileData === []) : ?>
            <div class="cyra-panel p-5 text-center">
                <i class="bi bi-person-vcard display-4 cyra-accent"></i>
                <p class="cyra-muted mt-3 mb-0">Não há outros dados de perfil disponíveis.</p>
            </div>
        <?php else : ?>
            <div class="row g-3 g-xl-4">
                <?php foreach ($profileData as $field => $value) : ?>
                    <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                        <article class="profile-card p-4">
                            <div class="d-flex gap-3 align-items-start">
                                <div class="profile-card-icon"><i class="bi bi-info-lg"></i></div>
                                <div class="min-w-0">
                                    <div class="profile-label mb-2"><?= esc($formatLabel($field)) ?></div>
                                    <div class="profile-value text-white text-break"><?= esc($value) ?></div>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?= view('layout/footer') ?>
