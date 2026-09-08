<?= view('layout/header', [
    'title' => $title ?? 'Importar orientações',
    'description' => 'Importação temporária de orientações por arquivo CSV ou texto.',
    'fluid' => true,
]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-filetype-csv me-2"></i>Administração</p>
            <h1 class="display-6 cyra-heading text-white mb-2">Importar orientações</h1>
            <p class="cyra-muted mb-0">Carregue o arquivo CSV ou cole os registros no formato indicado.</p>
        </div>
        <div class="text-lg-end"><strong class="display-6 text-white d-block"><?= (int) $total ?></strong><span class="small cyra-muted">registros na tabela temporária</span></div>
    </header>

    <?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-0" role="alert"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php $resultado = session()->getFlashdata('import_result'); ?>
    <?php if (is_array($resultado)) : ?>
        <div class="alert alert-info rounded-0" role="status">
            <strong><?= (int) $resultado['importados'] ?> importados</strong> e <?= (int) $resultado['duplicados'] ?> duplicados ignorados.
            <?php if ($resultado['erros'] !== []) : ?><details class="mt-2"><summary><?= count($resultado['erros']) ?> linhas com erro</summary><ul class="mb-0 mt-2"><?php foreach ($resultado['erros'] as $erro) : ?><li><?= esc($erro) ?></li><?php endforeach; ?></ul></details><?php endif; ?>
        </div>
    <?php endif; ?>

    <section class="cyra-panel p-3 p-md-4 mb-4">
        <form method="post" action="<?= site_url('admin/csv') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-4">
                <div class="col-lg-4">
                    <label class="form-label text-white fw-semibold" for="csv-file">Arquivo CSV</label>
                    <input class="form-control rounded-0" id="csv-file" name="csv_file" type="file" accept=".csv,text/csv,text/plain">
                    <p class="small cyra-muted mt-2 mb-0">Separador: ponto e vírgula. Limite: 10 MB.</p>
                </div>
                <div class="col-lg-8">
                    <label class="form-label text-white fw-semibold" for="csv-data">Dados das orientações</label>
                    <textarea class="form-control font-monospace rounded-0" id="csv-data" name="data" rows="12" spellcheck="false" placeholder="NOME;IDLattes;YEAR;CRACHA;ORIENTADOR&#10;ALINE TRIERWEILER DE SOUSA;2708286320198156;2019;176185;RENE FAUSTINO GABRIEL JUNIOR"><?= esc(old('data') ?? '') ?></textarea>
                    <p class="small cyra-muted mt-2 mb-0">Cabeçalho opcional. Campos vazios são aceitos para ID Lattes, crachá e orientador.</p>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4"><button class="btn btn-info rounded-0 px-4" type="submit"><i class="bi bi-database-add me-2"></i>Importar registros</button></div>
        </form>
    </section>

    <section class="cyra-panel overflow-hidden">
        <div class="d-flex justify-content-between align-items-center gap-2 p-3 p-md-4 border-bottom border-light border-opacity-10"><h2 class="h5 text-white mb-0">Últimos registros importados</h2><span class="badge text-bg-secondary rounded-0">Até 100</span></div>
        <?php if ($registros === []) : ?><p class="cyra-muted p-4 mb-0">Nenhum registro importado.</p><?php else : ?>
            <div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th class="ps-4">Nome</th><th>ID Lattes</th><th>Ano</th><th>Crachá</th><th class="pe-4">Orientador</th></tr></thead><tbody><?php foreach ($registros as $registro) : ?><tr><td class="ps-4 text-white"><?= esc($registro['nome']) ?></td><td class="cyra-muted text-nowrap"><?= esc($registro['id_lattes'] ?: '—') ?></td><td><?= (int) $registro['ano'] ?></td><td class="cyra-muted"><?= esc($registro['cracha'] ?: '—') ?></td><td class="pe-4 text-white"><?= esc($registro['orientador'] ?: '—') ?></td></tr><?php endforeach; ?></tbody></table></div>
        <?php endif; ?>
    </section>
</main>

<?= view('layout/footer', ['fluid' => true]) ?>
