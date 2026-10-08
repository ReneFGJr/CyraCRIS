<?php if (session()->get('auth_logged_in') === true) : ?>
    <section class="cyra-panel p-4 mb-4" aria-labelledby="student-history-title">
        <h2 class="h5 cyra-heading mb-3" id="student-history-title">Histórico de solicitações</h2>
        <?php if ($solicitacoes === []) : ?>
            <p class="cyra-muted mb-0">Nenhuma solicitação registrada para este aluno.</p>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead><tr><th scope="col">Registrado em</th><th scope="col">Solicitação</th><th scope="col">Período</th><th scope="col">Observações</th><th scope="col">ID do usuário</th></tr></thead>
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
                                <td class="text-break"><?= nl2br(esc(($solicitacao['descricao'] ?? '') ?: '-')) ?></td>
                                <td><?= esc(($solicitacao['usuario'] ?? '') ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
