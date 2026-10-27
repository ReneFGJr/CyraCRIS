<?php $reabrir = (string) service('request')->getGet('licenca'); ?>
<?php foreach (['saude' => 'Licença saúde', 'maternidade' => 'Licença maternidade'] as $tipoLicenca => $nomeLicenca) : ?>
    <?php $restaurar = old('licenca_tipo') === $tipoLicenca; ?>
    <div class="modal fade" id="licenca-<?= $tipoLicenca ?>" tabindex="-1" aria-labelledby="licenca-title-<?= $tipoLicenca ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="<?= site_url('admin/qualificacao/' . (int) $aluno['id'] . '/licenca') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="q" value="<?= esc($query, 'attr') ?>">
                    <input type="hidden" name="licenca_tipo" value="<?= $tipoLicenca ?>">
                    <div class="modal-header">
                        <h2 class="modal-title h5 cyra-heading" id="licenca-title-<?= $tipoLicenca ?>"><?= esc($nomeLicenca) ?></h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fw-bold cyra-accent"><?= esc($aluno['nome']) ?></p>
                        <div class="row g-3 mb-3">
                            <?php foreach (['data_inicio' => 'Data inicial', 'data_fim' => 'Data final'] as $campo => $rotulo) : ?>
                                <div class="col-sm-6">
                                    <label class="form-label" for="<?= $tipoLicenca . '-' . $campo ?>"><?= esc($rotulo) ?></label>
                                    <input class="form-control" type="date" name="<?= $campo ?>" id="<?= $tipoLicenca . '-' . $campo ?>" value="<?= esc($restaurar ? (string) old($campo, '') : '', 'attr') ?>" required>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="row g-3 mb-3">
                            <?php foreach (['dias_qualificacao' => 'Dias à qualificação', 'dias_defesa' => 'Dias à defesa'] as $campo => $rotulo) : ?>
                                <div class="col-sm-6">
                                    <label class="form-label" for="<?= $tipoLicenca . '-' . $campo ?>"><?= esc($rotulo) ?></label>
                                    <input class="form-control" type="number" min="0" step="1" name="<?= $campo ?>" id="<?= $tipoLicenca . '-' . $campo ?>" value="<?= esc($restaurar ? (string) old($campo, '0') : '0', 'attr') ?>" required>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <label class="form-label" for="<?= $tipoLicenca ?>-observacoes">Observações (opcional)</label>
                        <textarea class="form-control" name="observacoes" id="<?= $tipoLicenca ?>-observacoes" rows="3" maxlength="2000"><?= esc($restaurar ? (string) old('observacoes', '') : '') ?></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-info" type="button" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-info" type="submit">Registrar solicitação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php if (in_array($reabrir, ['saude', 'maternidade'], true)) : ?>
    <script>
        window.addEventListener('load', function () {
            bootstrap.Modal.getOrCreateInstance(document.getElementById(<?= json_encode('licenca-' . $reabrir) ?>)).show();
        });
    </script>
<?php endif; ?>
