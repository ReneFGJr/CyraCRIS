<?php
/** @var array $aluno Registro do aluno em tmp_orientacoes. */
$situacaoAluno = ($aluno['situacao'] ?? null) ?: 'Cursando';
$modalidadeAluno = ['D' => 'Doutorado', 'M' => 'Mestrado'][$aluno['modalidade'] ?? ''] ?? '-';
$prazoAluno = \App\Libraries\PrazoOrientacao::calcular($aluno);
$orientadoresPrograma = $orientadoresPrograma ?? [];
$diasQualificacaoSolicitados = max(0, (int) ($aluno['dias_solicitacao_qualificacao'] ?? 0));
$diasDefesaSolicitados = max(0, (int) ($aluno['dias_solicitacao_defesa'] ?? 0));
$previsaoQualificacao = \App\Libraries\PrazoOrientacao::prever($aluno, 18);
$previsaoDefesa = \App\Libraries\PrazoOrientacao::prever($aluno, 24);
if ($previsaoQualificacao !== null && $diasQualificacaoSolicitados > 0) {
    $previsaoQualificacao = $previsaoQualificacao->modify('+' . $diasQualificacaoSolicitados . ' days');
}
if ($previsaoDefesa !== null && $diasDefesaSolicitados > 0) {
    $previsaoDefesa = $previsaoDefesa->modify('+' . $diasDefesaSolicitados . ' days');
}
?>
<div class="cyra-student-heading">
    <div class="cyra-student-identity">
    <h3 class="h4 cyra-accent mb-0">
        <?= esc($aluno['nome'] ?? '') ?>
        <?php if (preg_match('/^[0-9]{16}$/D', (string) ($aluno['id_lattes'] ?? '')) === 1) : ?>
            <a class="cyra-student-lattes ms-2" href="https://lattes.cnpq.br/<?= esc($aluno['id_lattes'], 'attr') ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= esc('Abrir currículo Lattes de ' . ($aluno['nome'] ?? '') . ' em nova aba', 'attr') ?>" title="Currículo Lattes"><i class="bi bi-journal-text" aria-hidden="true"></i></a>
        <?php endif; ?>
    </h3>
    <p class="small mb-0 mt-1"><?= esc((string) (($aluno['cracha'] ?? '') ?: '-')) ?></p>
    </div>
    <div class="cyra-student-highlights">
        <p class="cyra-student-situation cyra-student-modality"><strong>Modalidade:</strong> <span><?= esc($modalidadeAluno) ?></span></p>
        <p class="cyra-student-situation"><strong>Situação:</strong> <span><?= esc($situacaoAluno) ?></span></p>
    </div>
</div>
<div class="d-flex flex-wrap align-items-baseline gap-3 mb-2">
    <p class="mb-0"><strong>Programa:</strong> <?= esc(($aluno['programa_nome'] ?? '') ?: '-') ?></p>
    <p class="mb-0"><strong>Linha de pesquisa:</strong> <?= esc(($aluno['linha_pesquisa_nome'] ?? '') ?: '-') ?></p>
    <p class="mb-0"><strong>Orientador:</strong>
        <?php if ($orientadoresPrograma !== []) : ?>
            <?php foreach ($orientadoresPrograma as $indice => $orientacao) : ?>
                <?php if ($indice > 0) : ?>, <?php endif; ?>
                <a href="<?= site_url('person/' . (int) $orientacao['orientador_id']) ?>"><?= esc($orientacao['orientador_nome']) ?></a>
                <span class="small cyra-muted">(<?= ($orientacao['tipo_orientacao'] ?? '') === 'CO_ORIENTADOR' ? 'Coorientador' : 'Orientador' ?>)</span>
            <?php endforeach; ?>
        <?php else : ?>
            <?= esc(($aluno['orientador'] ?? '') ?: '-') ?>
        <?php endif; ?>
    </p>
    <p class="mb-0"><strong>Ano:</strong> <?= esc((string) (($aluno['ano'] ?? '') ?: '-')) ?></p>
</div>
<div class="row g-3 mt-2">
    <?php foreach (['entrada_programa' => 'Ingresso', 'qualificacao' => 'Qualificação', 'defesa' => 'Defesa'] as $grupo => $tituloGrupo) : ?>
        <?php $eventoMarcado = $grupo !== 'entrada_programa' && ! empty($aluno['data_' . $grupo]); ?>
        <div class="col-12 <?= $grupo === 'entrada_programa' ? 'cyra-student-ingresso-column' : 'cyra-student-event-column' ?>">
            <section class="border rounded p-3 h-100 d-flex flex-column <?= $eventoMarcado ? 'border-success border-3' : '' ?>">
                <?php if ($grupo === 'entrada_programa') : ?>
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <h4 class="h6 cyra-heading mb-0"><?= esc($tituloGrupo) ?></h4>
                        <span class="cyra-prazo cyra-prazo-<?= esc($prazoAluno['classe'], 'attr') ?>" title="<?= esc($prazoAluno['detalhe'], 'attr') ?>"><?= esc($prazoAluno['rotulo']) ?></span>
                    </div>
                <?php else : ?>
                    <h4 class="h6 cyra-heading mb-3"><?= esc($tituloGrupo) ?></h4>
                <?php endif; ?>
                <dl class="mb-0 <?= $grupo === 'entrada_programa' ? 'cyra-ingresso-datas' : '' ?>">
                    <?php if ($grupo !== 'entrada_programa') : ?>
                        <dt class="small cyra-muted">Título do trabalho</dt>
                        <dd class="text-break"><?= esc(($aluno['titulo_' . $grupo] ?? '') ?: '-') ?></dd>
                    <?php endif; ?>
                    <?php
                    $dataEvento = $aluno['data_' . $grupo] ?? null;
                    $dataFormatada = $dataEvento ? \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $dataEvento) : false;
                    ?>
                    <?php if ($grupo === 'entrada_programa') : ?>
                        <div>
                        <dt class="small cyra-muted">Data de ingresso</dt>
                        <dd><?= esc($dataFormatada ? $dataFormatada->format('d/m/Y') : '-') ?></dd>
                        </div>
                        <div>
                            <dt class="small cyra-muted">Previsão de qualificação</dt>
                            <dd><?= esc($previsaoQualificacao ? $previsaoQualificacao->format('d/m/Y') . ' (+' . $diasQualificacaoSolicitados . ' dias)' : '-') ?></dd>
                        </div>
                        <div>
                            <dt class="small cyra-muted">Previsão de defesa</dt>
                            <dd><?= esc($previsaoDefesa ? $previsaoDefesa->format('d/m/Y') . ' (+' . $diasDefesaSolicitados . ' dias)' : '-') ?></dd>
                        </div>
                    <?php else : ?>
                        <dt class="small cyra-muted">Data</dt>
                        <dd><?= esc($dataFormatada ? $dataFormatada->format('d/m/Y') : '-') ?></dd>
                    <?php endif; ?>
                    <?php if ($grupo !== 'entrada_programa') : ?>
                        <dt class="small cyra-muted">Hora</dt>
                        <dd><?= esc(! empty($aluno['hora_' . $grupo]) ? substr((string) $aluno['hora_' . $grupo], 0, 5) : '-') ?></dd>
                        <?php
                        $localEvento = trim((string) ($aluno['local_' . $grupo] ?? ''));
                        $localHttps = filter_var($localEvento, FILTER_VALIDATE_URL) !== false
                            && strtolower((string) parse_url($localEvento, PHP_URL_SCHEME)) === 'https';
                        ?>
                        <dt class="small cyra-muted">Local</dt>
                        <dd class="text-break mb-0">
                            <?php if ($localHttps) : ?>
                                <a class="home-link" href="<?= esc($localEvento, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($localEvento) ?></a>
                            <?php else : ?>
                                <?= esc($localEvento !== '' ? $localEvento : '-') ?>
                            <?php endif; ?>
                        </dd>
                    <?php endif; ?>
                </dl>
                <?php if ($grupo !== 'entrada_programa' && $dataFormatada !== false && $dataFormatada->format('Y-m-d') === $dataEvento) : ?>
                    <div class="text-end mt-auto pt-3">
                        <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="offcanvas" data-bs-target="#detalhes-<?= $grupo ?>-<?= (int) $aluno['id'] ?>" aria-controls="detalhes-<?= $grupo ?>-<?= (int) $aluno['id'] ?>" aria-label="<?= esc('Detalhes da ' . $tituloGrupo, 'attr') ?>">Detalhes <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></button>
                    </div>
                <?php endif; ?>
            </section>
            <?php if ($grupo !== 'entrada_programa' && $dataFormatada !== false && $dataFormatada->format('Y-m-d') === $dataEvento) : ?>
                <?= view('components/evento_detalhes', ['aluno' => $aluno, 'grupo' => $grupo, 'tituloGrupo' => $tituloGrupo, 'bancaPessoas' => $bancaPessoas ?? []], ['saveData' => false]) ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
