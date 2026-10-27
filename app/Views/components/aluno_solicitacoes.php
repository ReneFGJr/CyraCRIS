<?php
$administradorLogado = session()->get('auth_logged_in') === true;
$historicoPublico = $historicoPublico ?? false;
?>
<?php if ($administradorLogado || $historicoPublico) : ?>
    <section class="cyra-panel p-4 mb-4" aria-labelledby="student-history-title">
        <h2 class="h5 cyra-heading mb-3" id="student-history-title">Histórico de solicitações</h2>
        <?php if ($solicitacoes === []) : ?>
            <p class="cyra-muted mb-0">Nenhuma solicitação registrada para este aluno.</p>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead><tr><th scope="col">Registrado em</th><th scope="col">Solicitação</th><th scope="col">Período</th><th scope="col">Dias à qualificação</th><th scope="col">Dias à defesa</th><?php if (! $historicoPublico) : ?><th scope="col">Observações</th><th scope="col">ID do usuário</th><?php endif; ?><?php if ($administradorLogado) : ?><th scope="col">Ação</th><?php endif; ?></tr></thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao) : ?>
                            <?php
                            $dados = json_decode((string) ($solicitacao['dados_novos'] ?? ''), true);
                            $dados = is_array($dados) ? $dados : [];
                            $formatarData = static function (mixed $valor): string {
                                $data = is_string($valor) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $valor) : false;
                                return $data !== false && $data->format('Y-m-d') === $valor ? $data->format('d/m/Y') : '-';
                            };
                            $registro = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) ($solicitacao['created_at'] ?? ''), new \DateTimeZone(config('App')->appTimezone));
                            ?>
                            <tr>
                                <td class="text-nowrap"><?= esc($registro ? $registro->setTimezone(new \DateTimeZone('America/Sao_Paulo'))->format('d/m/Y H:i') : '-') ?></td>
                                <td><?= esc($solicitacao['evento']) ?></td>
                                <td class="text-nowrap"><?= esc($formatarData($dados['data_inicio'] ?? null)) ?> até <?= esc($formatarData($dados['data_fim'] ?? null)) ?></td>
                                <td><?= (int) ($dados['dias_qualificacao'] ?? 0) ?></td>
                                <td><?= (int) ($dados['dias_defesa'] ?? 0) ?></td>
                                <?php if (! $historicoPublico) : ?>
                                    <td class="text-break"><?= nl2br(esc(($solicitacao['descricao'] ?? '') ?: '-')) ?></td>
                                    <td><?= esc(($solicitacao['usuario'] ?? '') ?: '-') ?></td>
                                <?php endif; ?>
                                <?php if ($administradorLogado) : ?>
                                    <td>
                                        <?php if (in_array($solicitacao['evento'], ['Licença saúde', 'Licença maternidade'], true)) : ?>
                                            <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#edit-solicitacao-<?= (int) $solicitacao['id'] ?>" aria-expanded="false" aria-controls="edit-solicitacao-<?= (int) $solicitacao['id'] ?>">Editar</button>
                                        <?php else : ?>
                                            <span class="cyra-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php if ($administradorLogado && in_array($solicitacao['evento'], ['Licença saúde', 'Licença maternidade'], true)) : ?>
                                <tr class="collapse" id="edit-solicitacao-<?= (int) $solicitacao['id'] ?>">
                                    <td colspan="<?= 5 + ($historicoPublico ? 0 : 2) + ($administradorLogado ? 1 : 0) ?>">
                                        <form class="row g-3 align-items-end" method="post" action="<?= site_url('admin/qualificacao/solicitacao/' . (int) $solicitacao['id']) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="aluno_id" value="<?= (int) $solicitacao['orientacao_id'] ?>">
                                            <input type="hidden" name="q" value="<?= esc((string) ($query ?? ''), 'attr') ?>">
                                            <div class="col-md-3">
                                                <label class="form-label" for="tipo-licenca-<?= (int) $solicitacao['id'] ?>">Tipo</label>
                                                <select class="form-select" id="tipo-licenca-<?= (int) $solicitacao['id'] ?>" name="licenca_tipo" required>
                                                    <option value="saude" <?= ($dados['licenca_tipo'] ?? '') === 'saude' ? 'selected' : '' ?>>Licença saúde</option>
                                                    <option value="maternidade" <?= ($dados['licenca_tipo'] ?? '') === 'maternidade' ? 'selected' : '' ?>>Licença maternidade</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label" for="data-inicio-<?= (int) $solicitacao['id'] ?>">Data inicial</label>
                                                <input class="form-control" id="data-inicio-<?= (int) $solicitacao['id'] ?>" type="date" name="data_inicio" value="<?= esc((string) ($dados['data_inicio'] ?? ''), 'attr') ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label" for="data-fim-<?= (int) $solicitacao['id'] ?>">Data final</label>
                                                <input class="form-control" id="data-fim-<?= (int) $solicitacao['id'] ?>" type="date" name="data_fim" value="<?= esc((string) ($dados['data_fim'] ?? ''), 'attr') ?>" required>
                                            </div>
                                            <div class="col-md">
                                                <label class="form-label" for="observacoes-<?= (int) $solicitacao['id'] ?>">Observações</label>
                                                <textarea class="form-control" id="observacoes-<?= (int) $solicitacao['id'] ?>" name="observacoes" maxlength="2000" rows="2"><?= esc((string) ($solicitacao['descricao'] ?? '')) ?></textarea>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="edit-dias-qualificacao-<?= (int) $solicitacao['id'] ?>">Dias à qualificação</label>
                                                <select class="form-select form-select-sm w-auto" id="edit-dias-qualificacao-<?= (int) $solicitacao['id'] ?>" name="dias_qualificacao" required>
                                                    <?php foreach ([0, 30, 60, 90, 120] as $dias) : ?>
                                                        <option value="<?= $dias ?>" <?= (int) ($dados['dias_qualificacao'] ?? 0) === $dias ? 'selected' : '' ?>><?= $dias ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="edit-dias-defesa-<?= (int) $solicitacao['id'] ?>">Dias à defesa</label>
                                                <select class="form-select form-select-sm w-auto" id="edit-dias-defesa-<?= (int) $solicitacao['id'] ?>" name="dias_defesa" required>
                                                    <?php foreach ([0, 30, 60, 90, 120] as $dias) : ?>
                                                        <option value="<?= $dias ?>" <?= (int) ($dados['dias_defesa'] ?? 0) === $dias ? 'selected' : '' ?>><?= $dias ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12 d-flex justify-content-end">
                                                <button class="btn btn-info" type="submit">Salvar alterações</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
