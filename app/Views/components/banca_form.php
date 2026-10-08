<fieldset class="border rounded p-3 p-md-4 mb-4" id="banca-form" data-search-url="<?= site_url('admin/banca/pessoas') ?>">
    <legend class="float-none w-auto px-2 h5 cyra-heading">Cadastrar banca</legend>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <p class="cyra-muted mb-0">Busque por nome ou ID Lattes e selecione a pessoa para cada posição.</p>
        <a class="btn btn-sm btn-outline-info" href="<?= site_url('admin/person/inport') ?>" target="_blank" rel="noopener noreferrer">Cadastrar nova pessoa <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i></a>
    </div>
    <div class="row g-4">
        <?php foreach (['avalidor_interno' => 'Membro interno (do PPG)', 'avalidor_externo' => 'Membro externo ao PPG', 'suplente' => 'Suplente'] as $prefix => $label) : ?>
            <div class="col-12 col-lg-4">
                <h3 class="h6 cyra-heading"><?= esc($label) ?></h3>
                <?php for ($number = 1; $number <= 3; $number++) : ?>
                    <?php $field = $prefix . '_' . $number; $personId = (string) old($field, $aluno[$field] ?? ''); ?>
                    <div class="cyra-banca-person mb-3">
                        <label class="form-label" for="search-<?= $field ?>"><?= esc($label) ?> <?= $number ?></label>
                        <input type="hidden" name="<?= $field ?>" value="<?= esc($personId, 'attr') ?>" class="banca-person-id">
                        <div class="input-group">
                            <input class="form-control banca-search" id="search-<?= $field ?>" type="search" autocomplete="off" placeholder="Digite ao menos 2 caracteres" value="<?= esc($bancaPessoas[$personId] ?? '', 'attr') ?>" aria-describedby="feedback-<?= $field ?>" maxlength="150">
                            <button class="btn btn-outline-secondary banca-clear" type="button" aria-label="<?= esc('Limpar ' . $label . ' ' . $number, 'attr') ?>"><i class="bi bi-x" aria-hidden="true"></i></button>
                        </div>
                        <div class="banca-feedback cyra-muted mt-1" id="feedback-<?= $field ?>" role="status" aria-live="polite"></div>
                        <div class="banca-results list-group mt-1"></div>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endforeach; ?>
    </div>
</fieldset>
<script>
(() => {
    const form = document.getElementById('banca-form');
    form.querySelectorAll('.cyra-banca-person').forEach(container => {
        const input = container.querySelector('.banca-search');
        const id = container.querySelector('.banca-person-id');
        const results = container.querySelector('.banca-results');
        const feedback = container.querySelector('.banca-feedback');
        let timer, request, version = 0;
        function clearResults() { results.replaceChildren(); feedback.textContent = ''; }
        input.addEventListener('input', () => {
            clearTimeout(timer); request?.abort(); const current = ++version;
            id.value = ''; clearResults();
            const query = input.value.trim();
            if (query.length < 2) return;
            timer = setTimeout(async () => {
                request = new AbortController(); feedback.textContent = 'Buscando…';
                try {
                    const url = new URL(form.dataset.searchUrl, window.location.origin);
                    url.searchParams.set('q', query);
                    const response = await fetch(url, { signal: request.signal, headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('search');
                    const data = await response.json();
                    if (current !== version) return;
                    clearResults();
                    if (!data.pessoas.length) feedback.textContent = 'Nenhuma pessoa encontrada.';
                    data.pessoas.forEach(person => {
                        const button = document.createElement('button');
                        button.type = 'button'; button.className = 'list-group-item list-group-item-action';
                        button.textContent = person.nome + (person.lattes_id ? ' · Lattes ' + person.lattes_id : '');
                        button.addEventListener('click', () => { id.value = person.id; input.value = person.nome; clearResults(); feedback.textContent = 'Pessoa selecionada.'; });
                        results.appendChild(button);
                    });
                } catch (error) {
                    if (error.name !== 'AbortError' && current === version) { clearResults(); feedback.textContent = 'Não foi possível buscar. Tente novamente.'; }
                }
            }, 300);
        });
        container.querySelector('.banca-clear').addEventListener('click', () => {
            clearTimeout(timer); request?.abort(); version++; id.value = ''; input.value = ''; clearResults(); input.focus();
        });
    });
    form.closest('form').addEventListener('submit', event => {
        for (const container of form.querySelectorAll('.cyra-banca-person')) {
            if (container.querySelector('.banca-search').value.trim() && !container.querySelector('.banca-person-id').value) {
                event.preventDefault(); container.querySelector('.banca-feedback').textContent = 'Selecione uma pessoa nos resultados.';
                container.querySelector('.banca-search').focus(); break;
            }
        }
    });
})();
</script>
