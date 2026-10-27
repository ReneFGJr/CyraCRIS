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

    <section class="cyra-panel p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h5 text-white mb-1"><i class="bi bi-search me-2 cyra-accent"></i>Buscar pessoa pelo Lattes</h2>
                <p class="small cyra-muted mb-0">Verifica cadastros existentes e tenta consultar os dados do currículo.</p>
            </div>
            <button class="btn btn-outline-info rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#lattes-search-panel" aria-expanded="<?= isset($lookupResult) && $lookupResult !== null ? 'true' : 'false' ?>" aria-controls="lattes-search-panel">
                <i class="bi bi-person-vcard me-2"></i>Buscar ID Lattes
            </button>
        </div>
        <div class="collapse <?= isset($lookupResult) && $lookupResult !== null ? 'show' : '' ?> mt-4" id="lattes-search-panel">
            <form class="row g-2 align-items-end" action="<?= site_url('admin/person/inport/search') ?>" method="post">
                <?= csrf_field() ?>
                <div class="col-md">
                    <label class="form-label" for="lattes-search-id">Número do ID Lattes</label>
                    <input class="form-control rounded-0" id="lattes-search-id" name="lattes_id" type="text" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" value="<?= esc($lattesId ?? '', 'attr') ?>" placeholder="16 dígitos" required>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-info rounded-0 w-100" type="submit"><i class="bi bi-search me-2"></i>Buscar</button>
                </div>
            </form>
            <p class="form-text cyra-muted mt-2 mb-0">A busca tenta o serviço BRAPCI de consulta ao Currículo Lattes (credencial <code>lattes.apiToken</code>). O CNPq não oferece uma API pública geral para essa consulta. Se necessário, tentamos a busca pública do Google Acadêmico; como não há API oficial que associe com segurança um ID Lattes a um perfil, seus resultados não preenchem o cadastro automaticamente.</p>

            <?php if (isset($lookupResult) && $lookupResult !== null) : ?>
                <?php
                $alertClass = match ($lookupResult['status']) {
                    'found' => 'success',
                    'duplicate' => 'warning',
                    default => 'info',
                };
                ?>
                <div class="alert alert-<?= esc($alertClass, 'attr') ?> rounded-0 mt-3 mb-0" role="status">
                    <p class="mb-2"><?= esc($lookupResult['message']) ?></p>
                    <?php if ($lookupResult['status'] === 'duplicate') : ?>
                        <p class="mb-0"><strong><?= esc($lookupResult['person']['nome']) ?></strong>
                            <a class="alert-link ms-2" href="<?= site_url('person/edit/' . (int) $lookupResult['person']['id']) ?>">Abrir cadastro para editar</a>
                        </p>
                    <?php elseif (in_array($lookupResult['status'], ['found', 'not_found'], true)) : ?>
                        <?php if (! empty($lookupResult['sources'])) : ?>
                            <p class="small mb-2">Fonte: <?= esc(implode(', ', $lookupResult['sources'])) ?>.</p>
                        <?php endif; ?>
                        <?php if (! empty($lookupResult['scholarMessage'])) : ?>
                            <p class="small mb-3"><?= esc($lookupResult['scholarMessage']) ?></p>
                        <?php endif; ?>
                        <form class="row g-3 align-items-end" action="<?= site_url('admin/person/inport/register') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="lattes_id" value="<?= esc($lattesId ?? '', 'attr') ?>">
                            <div class="col-md-6">
                                <label class="form-label mb-1" for="lookup-person-name">Nome</label>
                                <input class="form-control rounded-0" id="lookup-person-name" name="nome" type="text" maxlength="255" value="<?= esc($lookupResult['personData']['nome'] ?? '', 'attr') ?>" placeholder="Será completado na edição, se necessário">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" for="lookup-person-email">E-mail</label>
                                <input class="form-control rounded-0" id="lookup-person-email" name="email" type="email" maxlength="190" value="<?= esc($lookupResult['personData']['email'] ?? '', 'attr') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" for="lookup-person-orcid">ORCID</label>
                                <input class="form-control rounded-0" id="lookup-person-orcid" name="orcid" type="text" maxlength="19" value="<?= esc($lookupResult['personData']['orcid'] ?? '', 'attr') ?>" placeholder="0000-0000-0000-0000">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-info rounded-0" type="submit"><i class="bi bi-person-plus me-2"></i>Cadastrar e editar pessoa</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

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
                        Nomes ou IDs Lattes já cadastrados, inclusive repetidos neste lote, não serão importados.
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
