<?php if (session()->get('auth_logged_in') === true) : ?>
    <li class="nav-item">
        <a class="btn btn-outline-info rounded-0 px-4" href="<?= site_url('profile') ?>">
            <i class="bi bi-person-circle me-2"></i><?= esc((string) session()->get('auth_givename')) ?>
        </a>
    </li>
<?php else : ?>
    <li class="nav-item">
        <a class="btn btn-info rounded-0 px-4" href="<?= site_url('login') ?>">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </a>
    </li>
<?php endif; ?>
