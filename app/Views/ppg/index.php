<?php
/** @var array<int, array<string, mixed>> $programas */
$rows = $programas ?? [];
?>
<?= view('layout/header', [
    'title' => 'Programas de Pós-Graduação',
    'description' => 'Programas de pós-graduação cadastrados no CyraCRIS.',
]) ?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-mortarboard me-2"></i>Base acadêmica</p>
            <h1 class="cyra-heading text-white mb-2">Programas de Pós-Graduação</h1>
            <p class="cyra-muted mb-0">Programas avaliados e reconhecidos cadastrados no CyraCRIS.</p>
        </div>
        <span class="badge text-bg-info rounded-0 px-3 py-2 align-self-start align-self-md-auto">
            <?= count($rows) ?> <?= count($rows) === 1 ? 'programa' : 'programas' ?>
        </span>
    </div>

    <?php if ($rows === []) : ?>
        <div class="cyra-panel p-5 text-center">
            <i class="bi bi-inbox display-5 cyra-accent"></i>
            <h2 class="h4 text-white mt-3">Nenhum programa cadastrado</h2>
            <p class="cyra-muted mb-0">Execute a migration e o seeder para carregar os dados da Plataforma Sucupira.</p>
        </div>
    <?php else : ?>
        <div class="table-responsive cyra-panel">
            <table class="table table-dark table-hover align-middle mb-0">
                <caption class="visually-hidden">Programas de pós-graduação cadastrados</caption>
                <thead>
                    <tr>
                        <th scope="col">Programa</th>
                        <th scope="col">Instituição</th>
                        <th scope="col">CAPES</th>
                        <th scope="col">Nota</th>
                        <th scope="col">Modalidade</th>
                        <th scope="col">Situação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $programa) : ?>
                        <tr>
                            <td>
                                <a class="text-white text-decoration-none d-block" href="<?= site_url('ppg/' . $programa['id']) ?>"><strong><?= esc($programa['nome']) ?></strong> <i class="bi bi-box-arrow-up-right small cyra-accent"></i></a>
                                <small class="cyra-muted">Código <?= esc($programa['codigo_capes']) ?></small>
                            </td>
                            <td>
                                <span class="text-white"><?= esc($programa['instituicao_sigla'] ?? '') ?></span>
                                <small class="cyra-muted d-block"><?= esc($programa['instituicao_nome'] ?? '') ?></small>
                            </td>
                            <td><?= esc($programa['codigo_capes']) ?></td>
                            <td><span class="badge text-bg-info rounded-0">Nota <?= esc($programa['nota_capes'] ?? '-') ?></span></td>
                            <td><?= esc($programa['modalidade'] ?? '-') ?></td>
                            <td><span class="text-success"><i class="bi bi-check-circle me-1"></i><?= esc($programa['situacao'] ?? '-') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?= view('layout/footer') ?>
