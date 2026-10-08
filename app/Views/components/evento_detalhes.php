<?php
$painelId = 'detalhes-' . $grupo . '-' . (int) $aluno['id'];
$dataPainel = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($aluno['data_' . $grupo] ?? ''));
$localPainel = trim((string) ($aluno['local_' . $grupo] ?? ''));
$linkLocal = filter_var($localPainel, FILTER_VALIDATE_URL) !== false
    && strtolower((string) parse_url($localPainel, PHP_URL_SCHEME)) === 'https';
?>
<aside class="offcanvas offcanvas-end cyra-event-details" tabindex="-1" id="<?= esc($painelId, 'attr') ?>" aria-labelledby="<?= esc($painelId . '-title', 'attr') ?>">
    <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h4 cyra-heading" id="<?= esc($painelId . '-title', 'attr') ?>">Detalhes da <?= esc($tituloGrupo) ?></h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar painel"></button>
    </div>
    <div class="offcanvas-body">
        <p class="fw-bold cyra-accent"><?= esc($aluno['nome'] ?? '') ?></p>
        <div>
            <h4 class="h6 cyra-muted mt-3">Título do trabalho</h4>
            <p class="text-break mb-4"><?= esc(($aluno['titulo_' . $grupo] ?? '') ?: '-') ?></p>
            <h4 class="h6 cyra-muted mt-3">Orientador</h4>
            <p><?= esc(($aluno['orientador'] ?? '') ?: '-') ?></p>
            <h4 class="h6 cyra-muted mt-3">Data</h4>
            <p><?= esc($dataPainel ? $dataPainel->format('d/m/Y') : '-') ?></p>
            <h4 class="h6 cyra-muted mt-3">Hora</h4>
            <p><?= esc(! empty($aluno['hora_' . $grupo]) ? substr((string) $aluno['hora_' . $grupo], 0, 5) : '-') ?></p>
            <h4 class="h6 cyra-muted mt-3">Local</h4>
            <p class="text-break">
                <?php if ($linkLocal) : ?>
                    <a class="home-link" href="<?= esc($localPainel, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($localPainel) ?></a>
                <?php else : ?>
                    <?= esc($localPainel !== '' ? $localPainel : '-') ?>
                <?php endif; ?>
            </p>
        </div>
        <?php if ($grupo === 'qualificacao') : ?>
            <section class="border-top pt-3" aria-label="Banca de qualificação">
                <h3 class="h5 cyra-heading">Banca</h3>
                <?php foreach (['avalidor_interno' => 'Membros internos', 'avalidor_externo' => 'Membros externos', 'suplente' => 'Suplentes'] as $prefix => $rotulo) : ?>
                    <h4 class="cyra-banca-group-title cyra-muted mt-3"><?= esc($rotulo) ?></h4>
                    <ul class="list-unstyled">
                        <?php $temMembro = false; ?>
                        <?php for ($numero = 1; $numero <= 3; $numero++) : ?>
                            <?php $membroId = $aluno[$prefix . '_' . $numero] ?? null; ?>
                            <?php if ($membroId !== null && $membroId !== '') : $temMembro = true; ?>
                                <li class="mb-1"><?= esc($bancaPessoas[$membroId] ?? ('ID ' . $membroId)) ?></li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if (! $temMembro) : ?><li>-</li><?php endif; ?>
                    </ul>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</aside>
