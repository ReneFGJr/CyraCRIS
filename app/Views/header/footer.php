<?php
$statusMessages = [];
$statusSession = session();
foreach (['sucesso' => 'success', 'success' => 'success', 'erro' => 'error', 'error' => 'error', 'warning' => 'warning', 'aviso' => 'warning', 'info' => 'info', 'message' => 'info', 'mensagem' => 'info'] as $key => $type) {
    $value = $statusSession->getFlashdata($key);
    foreach (is_array($value) ? $value : [$value] as $message) {
        if (is_string($message) && trim($message) !== '') {
            $statusMessages[] = ['type' => $type, 'text' => $message];
        }
    }
}
$validationErrors = $statusSession->getFlashdata('errors');
if (is_array($validationErrors)) {
    foreach ($validationErrors as $message) {
        if (is_string($message)) {
            $statusMessages[] = ['type' => 'error', 'text' => $message];
        }
    }
}
$importResult = $statusSession->getFlashdata('import_result');
if (is_array($importResult)) {
    $importErrors = $importResult['erros'] ?? $importResult['errors'] ?? [];
    $imported = (int) ($importResult['importados'] ?? $importResult['imported'] ?? 0);
    $summary = $imported . ' registros importados.';
    if (isset($importResult['duplicados'])) {
        $summary .= ' ' . (int) $importResult['duplicados'] . ' duplicados ignorados.';
    }
    $statusMessages[] = ['type' => $importErrors === [] ? 'success' : 'warning', 'text' => $summary];
    foreach ($importErrors as $message) {
        $statusMessages[] = ['type' => 'error', 'text' => (string) $message];
    }
}
$statusIcons = ['success' => 'check-circle', 'error' => 'exclamation-circle', 'warning' => 'exclamation-triangle', 'info' => 'info-circle'];
?>
    <footer class="cyra-footer mt-auto py-4">
        <div class="<?= ($fluid ?? false) ? 'container-fluid px-3 px-md-4 px-xxl-5' : 'container' ?> cyra-footer-layout">
            <p class="mb-0 small cyra-muted">Current Research Information System</p>
            <section class="cyra-status" id="system-status" aria-labelledby="system-status-title">
                <h2 class="cyra-status-title" id="system-status-title"><i class="bi bi-activity" aria-hidden="true"></i> Status do sistema</h2>
                <div id="system-status-messages" role="status" aria-live="polite" aria-atomic="true">
                    <?php if ($statusMessages === []) : ?>
                        <p class="cyra-status-idle mb-0">Nenhuma mensagem no momento.</p>
                    <?php else : ?>
                        <?php foreach ($statusMessages as $message) : ?>
                            <p class="cyra-status-message cyra-status-<?= esc($message['type'], 'attr') ?>">
                                <i class="bi bi-<?= $statusIcons[$message['type']] ?>" aria-hidden="true"></i>
                                <span><?= esc($message['text']) ?></span>
                            </p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            <p class="mb-0 small cyra-muted cyra-footer-copyright">&copy; <?= date('Y') ?> CyraCRIS</p>
        </div>
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>