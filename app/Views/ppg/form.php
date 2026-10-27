<?php
$value = static fn (string $field): string => (string) old($field, '');
?>
<?= view('header/header', [
    'title'       => $title ?? 'Novo programa',
    'description' => 'Cadastro de programa de pós-graduação.',
]) ?>

<main class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase fw-bold small cyra-accent mb-2"><i class="bi bi-mortarboard me-2"></i>Base acadêmica</p>
            <h1 class="display-6 cyra-heading text-white mb-0">Novo programa</h1>
        </div>
        <a class="btn btn-outline-info rounded-0" href="<?= site_url('ppg') ?>"><i class="bi bi-arrow-left me-2"></i>Voltar</a>
    </div>

    <div class="mb-4">
        <button class="btn btn-outline-info rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#painel-importacao-capes" aria-expanded="false" aria-controls="painel-importacao-capes">
            <i class="bi bi-cloud-download me-2"></i>Importar dados da CAPES
        </button>
        <section class="collapse mt-3" id="painel-importacao-capes">
            <div class="cyra-panel p-4">
                <h2 class="h5 text-white">Importar programa da CAPES</h2>
                <p class="cyra-muted">Informe o código do programa para buscar os dados atualizados na Plataforma Sucupira.</p>
                <form id="form-importacao-capes" method="post" action="<?= site_url('ppg/import') ?>">
                    <?= csrf_field() ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label" for="codigo-capes-importacao">Código do programa</label>
                            <input class="form-control rounded-0" id="codigo-capes-importacao" name="codigo_capes" type="text" maxlength="13" placeholder="Ex.: 42001013173P0" pattern="[0-9]{11}[Pp][0-9]" required>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info rounded-0 w-100" id="botao-importar-capes" type="submit">
                                <i class="bi bi-search me-2"></i>Importar
                            </button>
                        </div>
                    </div>
                    <div class="form-text cyra-muted" id="ajuda-codigo-capes">Exemplo de código CAPES: 42001013173P0.</div>
                    <div class="small mt-3" id="resultado-importacao-capes" role="status" aria-live="polite"></div>
                </form>
            </div>
        </section>
    </div>

    <section class="cyra-panel p-4 p-lg-5">
        <form id="form-cadastro-programa" class="row g-4" method="post" action="<?= site_url('ppg') ?>">
            <?= csrf_field() ?>

            <div class="col-md-4">
                <label class="form-label" for="codigo-capes">Código CAPES</label>
                <input class="form-control rounded-0" id="codigo-capes" name="codigo_capes" type="text" maxlength="20" value="<?= esc($value('codigo_capes'), 'attr') ?>" required>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="nome">Nome do programa</label>
                <input class="form-control rounded-0" id="nome" name="nome" type="text" maxlength="255" value="<?= esc($value('nome'), 'attr') ?>" required>
            </div>

            <div class="col-md-8">
                <label class="form-label" for="instituicao-nome">Instituição</label>
                <input class="form-control rounded-0" id="instituicao-nome" name="instituicao_nome" type="text" maxlength="255" value="<?= esc($value('instituicao_nome'), 'attr') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="instituicao-sigla">Sigla</label>
                <input class="form-control rounded-0" id="instituicao-sigla" name="instituicao_sigla" type="text" maxlength="30" value="<?= esc($value('instituicao_sigla'), 'attr') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="instituicao-codigo">Código da instituição</label>
                <input class="form-control rounded-0" id="instituicao-codigo" name="instituicao_codigo" type="number" min="0" value="<?= esc($value('instituicao_codigo'), 'attr') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="telefone">Telefone</label>
                <input class="form-control rounded-0" id="telefone" name="telefone" type="tel" maxlength="30" value="<?= esc($value('telefone'), 'attr') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control rounded-0" id="email" name="email" type="email" maxlength="190" value="<?= esc($value('email'), 'attr') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="website">Website</label>
                <input class="form-control rounded-0" id="website" name="website" type="url" maxlength="255" value="<?= esc($value('website'), 'attr') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label" for="ano-inicio">Ano de início</label>
                <input class="form-control rounded-0" id="ano-inicio" name="ano_inicio" type="number" min="1900" max="2100" value="<?= esc($value('ano_inicio'), 'attr') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="nota-capes">Nota CAPES</label>
                <input class="form-control rounded-0" id="nota-capes" name="nota_capes" type="text" maxlength="1" pattern="[1-7Aa]" value="<?= esc($value('nota_capes'), 'attr') ?>">
                <div class="form-text cyra-muted">Informe de 1 a 7 ou A.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="modalidade">Modalidade</label>
                <input class="form-control rounded-0" id="modalidade" name="modalidade" type="text" maxlength="30" value="<?= esc($value('modalidade'), 'attr') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="situacao">Situação</label>
                <input class="form-control rounded-0" id="situacao" name="situacao" type="text" maxlength="50" value="<?= esc($value('situacao'), 'attr') ?>">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
                <a class="btn btn-outline-light rounded-0" href="<?= site_url('ppg') ?>">Cancelar</a>
                <button class="btn btn-info rounded-0 px-4" type="submit"><i class="bi bi-check-lg me-2"></i>Cadastrar programa</button>
            </div>
        </form>
    </section>
</main>

<?= view('header/footer') ?>
<script>
    document.getElementById('form-importacao-capes').addEventListener('submit', async (event) => {
        event.preventDefault();

        const form = event.currentTarget;
        const registrationForm = document.getElementById('form-cadastro-programa');
        const button = document.getElementById('botao-importar-capes');
        const result = document.getElementById('resultado-importacao-capes');
        const csrfInputs = document.querySelectorAll('input[name="<?= esc(csrf_token(), 'js') ?>"]');

        button.disabled = true;
        result.className = 'small mt-3 cyra-muted';
        result.textContent = 'Consultando a CAPES...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            const payload = await response.json();

            if (payload.csrf) {
                csrfInputs.forEach((input) => {
                    if (input.name === payload.csrf.name) {
                        input.value = payload.csrf.hash;
                    }
                });
            }

            if (!response.ok) {
                result.className = 'small mt-3 text-danger';
                result.textContent = payload.message || 'Não foi possível importar os dados.';
                return;
            }

            Object.entries(payload.programa).forEach(([name, value]) => {
                const input = registrationForm.elements.namedItem(name);
                if (input) {
                    input.value = value;
                }
            });

            result.className = `small mt-3 ${payload.warning ? 'text-warning' : 'text-success'}`;
            result.textContent = payload.message;
        } catch {
            result.className = 'small mt-3 text-danger';
            result.textContent = 'Não foi possível consultar a CAPES. Tente novamente mais tarde.';
        } finally {
            button.disabled = false;
        }
    });
</script>
