<?php $containerClass = ($fluid ?? false) ? 'container-fluid px-3 px-md-4 px-xxl-5' : 'container'; ?>
<nav class="navbar navbar-expand-lg navbar-dark cyra-navbar border-bottom border-light border-opacity-10" aria-label="Navegação principal">
    <div class="<?= $containerClass ?> py-2">
        <a class="navbar-brand cyra-brand" href="<?= base_url() ?>" aria-label="CyraCRIS - início">
            <img class="cyra-logo" src="<?= base_url('assets/logo/logo_cyracris.png') ?>" alt="CyraCRIS">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Abrir menu">
            <i class="bi bi-list fs-3"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainNavigation">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <li class="nav-item"><a class="nav-link cyra-link" href="<?= site_url('abour') ?>">Sobre o sistema</a></li>
                <li class="nav-item"><a class="nav-link cyra-link" href="<?= site_url('ppg') ?>"><i class="bi bi-mortarboard me-1"></i>Programas PPG</a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-outline-info rounded-0 px-4" href="mailto:contato@cyracris.com.br"><i class="bi bi-envelope me-2"></i>Contato</a></li>
                <?= view('auth/navbar') ?>
            </ul>
        </div>
    </div>
</nav>
