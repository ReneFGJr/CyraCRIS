<?= view('header/header', [
    'title'       => $title ?? 'Importar pessoas',
    'description' => 'Importação de pessoas para o CyraCRIS.',
    'fluid'       => true,
]) ?>



<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2">
                <i class="bi bi-upload me-2"></i>Importação
            </p>
            <h1 class="display-6 cyra-heading text-white mb-2">Importar pessoas</h1>
            <p class="cyra-muted mb-0">Cole uma pessoa por linha usando a ordem indicada abaixo.</p>
        </div>
        <a class="btn btn-outline-info rounded-0 px-4" href="<?= site_url('admin/person') ?>">
            <i class="bi bi-arrow-left me-2"></i>Voltar para pessoas
        </a>
    </div>



        <div class="row g-4">
        <div class="col-xl-8">
            <section class="cyra-panel p-4 p-md-5">
                <form action="<?= site_url('admin/person/inport') ?>" method="post">
                    <?= csrf_field() ?>
                    <label class="form-label fw-semibold" for="data">Dados para importação</label>
                    <textarea class="form-control rounded-0 font-monospace" id="data" name="data" rows="16"
                        placeholder="Nome;ID Lattes;email&#10;Maria Silva;1234567890123456;maria@example.com"
                        aria-describedby="data-help" required></textarea>
                    <div class="form-text cyra-muted mt-2" id="data-help">
                        Separe as colunas com ponto e vírgula, tabulação real, o texto /tab ou vírgula. O cabeçalho é opcional.
                        Nome, ID Lattes e e-mail são obrigatórios. CPF e crachá são opcionais e podem ser omitidos.
                    </div>
                    <button class="btn btn-info rounded-0 px-4 mt-4" type="submit">
                        <i class="bi bi-database-add me-2"></i>Importar pessoas
                    </button>
                </form>
            </section>
        </div>
        <div class="col-xl-4">
            <aside class="cyra-panel p-4 h-100">
                <h2 class="h5 text-white mb-4">Formato das colunas</h2>
                <ol class="list-group list-group-numbered list-group-flush">
                    <li class="list-group-item bg-transparent text-white border-light border-opacity-10">Nome (obrigatório)</li>
                    <li class="list-group-item bg-transparent text-white border-light border-opacity-10">ID Lattes (obrigatório)</li>
                    <li class="list-group-item bg-transparent text-white border-light border-opacity-10">E-mail (obrigatório)</li>
                    <li class="list-group-item bg-transparent text-white border-light border-opacity-10">CPF (opcional)</li>
                    <li class="list-group-item bg-transparent text-white border-light border-opacity-10">Crachá (opcional)</li>
                </ol>
                <div class="mt-4 p-3 border border-info border-opacity-25">
                    <div class="small cyra-muted mb-2">Exemplo</div>
                    <code class="text-info text-break">Maria Silva;1234567890123456;maria@example.com</code>
                </div>
            </aside>
        </div>
    </div>
</main>

<?= view('header/footer', ['fluid' => true]) ?>
