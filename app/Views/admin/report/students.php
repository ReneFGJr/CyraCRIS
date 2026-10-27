<?= view('header/header', [
    'title' => $title,
    'description' => $title . ' do CyraCRIS.',
    'fluid' => true,
]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-file-earmark-bar-graph me-2"></i>Relatórios</p>
            <h1 class="display-6 cyra-heading text-white mb-2"><?= esc($title) ?></h1>
            <p class="cyra-muted mb-0"><?= count($estudantes) ?> <?= count($estudantes) === 1 ? 'estudante encontrado' : 'estudantes encontrados' ?>.</p>
        </div>
        <a class="btn btn-outline-info rounded-0" href="<?= site_url('admin/report') ?>"><i class="bi bi-arrow-left me-2"></i>Voltar aos relatórios</a>
    </header>

    <section class="cyra-panel p-3 p-lg-4">
        <?php if ($estudantes === []) : ?>
            <p class="cyra-muted mb-0">Nenhum estudante encontrado para este relatório.</p>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Estudante</th>
                            <th scope="col">Programa / linha de pesquisa</th>
                            <th scope="col">Modalidade</th>
                            <th scope="col">Situação</th>
                            <th scope="col">Orientador</th>
                            <th scope="col">Ano</th>
                            <th scope="col">Ingresso</th>
                            <th scope="col">Qualificação</th>
                            <th scope="col">Defesa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudantes as $estudante) : ?>
                            <?php
                            $formatarData = static function (mixed $data): string {
                                if (! is_string($data) || $data === '') {
                                    return '—';
                                }
                                $dataValida = \DateTimeImmutable::createFromFormat('!Y-m-d', $data);
                                return $dataValida !== false && $dataValida->format('Y-m-d') === $data
                                    ? $dataValida->format('d/m/Y')
                                    : '—';
                            };
                            ?>
                            <tr>
                                <td>
                                    <a class="cyra-accent" href="<?= site_url('person/' . (int) $estudante['id']) ?>"><?= esc($estudante['nome']) ?></a>
                                    <span class="d-block small cyra-muted"><?= esc(($estudante['cracha'] ?? '') ?: ($estudante['id_lattes'] ?? '') ?: '—') ?></span>
                                </td>
                                <td>
                                    <?= esc(($estudante['programa_nome'] ?? '') ?: '—') ?>
                                    <span class="d-block small cyra-muted"><?= esc(($estudante['linha_pesquisa_nome'] ?? '') ?: '—') ?></span>
                                </td>
                                <td><?= esc(match ($estudante['modalidade'] ?? '') { 'M' => 'Mestrado', 'D' => 'Doutorado', default => '—' }) ?></td>
                                <td><?= esc(($estudante['situacao'] ?? '') ?: 'Cursando') ?></td>
                                <td><?= esc(($estudante['orientador'] ?? '') ?: '—') ?></td>
                                <td><?= esc((string) (($estudante['ano'] ?? '') ?: '—')) ?></td>
                                <td class="text-nowrap"><?= esc($formatarData($estudante['data_entrada_programa'] ?? null)) ?></td>
                                <td class="text-nowrap"><?= esc($formatarData($estudante['data_qualificacao'] ?? null)) ?></td>
                                <td class="text-nowrap"><?= esc($formatarData($estudante['data_defesa'] ?? null)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?= view('header/footer', ['fluid' => true]) ?>
