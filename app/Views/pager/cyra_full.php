<?php

use CodeIgniter\Pager\PagerRenderer;

/** @var PagerRenderer $pager */
$pager->setSurroundCount(2);
?>

<nav aria-label="Navegação entre páginas">
    <ul class="pagination cyra-pagination justify-content-center flex-wrap mb-0">
        <li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
            <a class="page-link" href="<?= $pager->getFirst() ?? '#' ?>" aria-label="Primeira página" title="Primeira página">
                <i class="bi bi-chevron-bar-left" aria-hidden="true"></i>
            </a>
        </li>
        <li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
            <a class="page-link" href="<?= $pager->getPrevious() ?? '#' ?>" aria-label="Página anterior" title="Página anterior">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </a>
        </li>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>" <?= $link['active'] ? 'aria-current="page"' : '' ?>>
                <a class="page-link" href="<?= $link['uri'] ?>"><?= esc($link['title']) ?></a>
            </li>
        <?php endforeach; ?>

        <li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
            <a class="page-link" href="<?= $pager->getNext() ?? '#' ?>" aria-label="Próxima página" title="Próxima página">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </a>
        </li>
        <li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
            <a class="page-link" href="<?= $pager->getLast() ?? '#' ?>" aria-label="Última página" title="Última página">
                <i class="bi bi-chevron-bar-right" aria-hidden="true"></i>
            </a>
        </li>
    </ul>
</nav>
