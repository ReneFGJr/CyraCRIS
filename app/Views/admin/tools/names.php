<?= view('layout/header', [
    'title'       => $title ?? 'Padronizar nomes',
    'description' => 'Prévia da padronização dos nomes cadastrados.',
    'fluid'       => true,
]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-spellcheck me-2"></i>Manutenção do cadastro</p>
            <h1 class="display-6 cyra-heading text-white mb-2">Padronizar nomes</h1>
            <p class="cyra-muted mb-0">Comparação gerada por <code>nbr_autor(nome, 7)</code>. Confira os nomes antes de confirmar.</p>
        </div>
        <a class="btn btn-outline-info rounded-0" href="<?= site_url('admin/report') ?>"><i class="bi bi-arrow-left me-2"></i>Voltar aos relatórios</a>
    </header>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger rounded-0" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success rounded-0" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if ($changes === []) : ?>
        <section class="cyra-panel p-5 text-center">
            <i class="bi bi-check-circle display-4 cyra-accent"></i>
            <h2 class="h4 text-white mt-3">Nomes já padronizados</h2>
            <p class="cyra-muted mb-0">Nenhum nome difere do resultado produzido pelo helper.</p>
        </section>
    <?php else : ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <p class="cyra-muted mb-0"><strong class="text-white"><?= count($changes) ?></strong> alterações encontradas.</p>
            <span class="badge text-bg-warning rounded-0"><i class="bi bi-exclamation-triangle me-1"></i>Revise antes de aplicar</span>
        </div>

        <div class="table-responsive cyra-panel mb-4">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead><tr><th class="px-4 py-3">ID</th><th class="py-3">Antes</th><th class="py-3">Depois</th></tr></thead>
                <tbody>
                    <?php foreach ($changes as $change) : ?>
                        <tr>
                            <td class="px-4 cyra-muted"><?= (int) $change['id'] ?></td>
                            <td class="text-warning"><?= esc($change['before']) ?></td>
                            <td class="text-white fw-semibold"><?= esc($change['after']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <form class="cyra-panel p-4" method="post" action="<?= site_url('admin/tools/names') ?>" onsubmit="return confirm('Confirma a atualização de <?= count($changes) ?> nomes no banco de dados?');">
            <?= csrf_field() ?>
            <div class="form-check mb-3">
                <input class="form-check-input" id="confirm-names" name="confirm" type="checkbox" value="1" required>
                <label class="form-check-label text-white" for="confirm-names">Revisei a lista e confirmo a atualização de todos os nomes exibidos.</label>
            </div>
            <button class="btn btn-warning rounded-0 px-4" type="submit"><i class="bi bi-check2-all me-2"></i>Confirmar e atualizar nomes</button>
        </form>
    <?php endif; ?>
</main>

<?= view('layout/footer', ['fluid' => true]) ?>
