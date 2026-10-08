<?= view('header/header', ['title' => 'Programas de pós-graduação', 'fluid' => true]) ?>
<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="mb-4">
        <p class="small text-uppercase fw-bold cyra-accent mb-2">Pesquisa que se conecta</p>
        <h1 class="home-heading display-5 home-title">Programas de <span>pós-graduação</span></h1>
        <p class="cyra-muted">Explore os programas, suas linhas de pesquisa e as pessoas que fazem a ciência acontecer.</p>
        <form class="d-flex gap-2 mt-3" method="get" action="<?= site_url('person/search') ?>" role="search" style="max-width: 36rem">
            <label class="visually-hidden" for="home-person-search">Buscar indivíduo por nome ou ID Lattes</label>
            <input class="form-control" id="home-person-search" name="q" type="search" placeholder="Buscar por nome ou ID Lattes" required>
            <button class="btn btn-outline-info" type="submit">Buscar</button>
        </form>
    </header>
    <div class="home-workspace">
        <aside class="home-panel home-programs" aria-labelledby="programs-title">
            <h2 class="h5 home-heading" id="programs-title">Programas <span class="small cyra-muted">(<?= count($programas) ?>)</span></h2>
            <p class="small cyra-muted">Selecione um PPG para consultar os detalhes.</p>
            <nav aria-label="Selecionar programa de pós-graduação">
                <?php foreach ($programas as $programa) : ?>
                    <a class="home-program" href="<?= site_url('/') . '?ppg=' . (int) $programa['id'] ?>" <?= (int) $programa['id'] === (int) ($selecionado['id'] ?? 0) ? 'aria-current="true"' : '' ?>>
                        <span><strong><?= esc($programa['nome']) ?></strong><small><?= esc(($programa['instituicao_sigla'] ?? '') ?: ($programa['instituicao_nome'] ?? '')) ?></small></span>
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </a>
                <?php endforeach; ?>
                <?php if ($programas === []) : ?><p class="cyra-muted mb-0">Nenhum programa cadastrado.</p><?php endif; ?>
            </nav>
        </aside>
        <section class="home-details" aria-labelledby="selected-title">
            <?php if ($selecionado !== null) : ?>
                <header class="home-panel p-4 mb-3">
                    <p class="small cyra-accent mb-2"><?= esc($selecionado['instituicao_nome'] ?? '') ?></p>
                    <h2 class="home-heading h2" id="selected-title"><?= esc($selecionado['nome']) ?></h2>
                    <p class="cyra-muted mb-3"><?= esc($selecionado['modalidade'] ?? '') ?><?php if (! empty($selecionado['situacao'])) : ?> · <?= esc($selecionado['situacao']) ?><?php endif; ?></p>
                    <a class="home-link" href="<?= site_url('ppg/' . (int) $selecionado['id']) ?>">Ver página completa do programa <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                </header>
                <div class="home-stats mb-3" aria-label="Indicadores do programa">
                    <?php foreach ([['Linhas de pesquisa', count($linhas), 'diagram-3'], ['Docentes', count($docentes), 'people'], ['Nota CAPES', $selecionado['nota_capes'] ?? 'Não informada', 'award']] as [$rotulo, $valor, $icone]) : ?>
                        <article class="home-stat"><i class="bi bi-<?= $icone ?> cyra-accent" aria-hidden="true"></i><strong><?= esc((string) $valor) ?></strong><span class="small cyra-muted"><?= esc($rotulo) ?></span></article>
                    <?php endforeach; ?>
                </div>
                <section class="home-panel p-4 mb-3" aria-labelledby="research-title">
                    <h3 class="home-heading h4 mb-3" id="research-title">Linhas de pesquisa</h3>
                    <?php foreach ($linhas as $linha) : ?>
                        <article class="home-entry"><h4 class="h6 mb-1"><?= esc($linha['nome']) ?></h4><?php if (! empty($linha['area_concentracao'])) : ?><p class="small cyra-muted mb-0"><?= esc($linha['area_concentracao']) ?></p><?php endif; ?></article>
                    <?php endforeach; ?>
                    <?php if ($linhas === []) : ?><p class="cyra-muted mb-0">Nenhuma linha de pesquisa cadastrada para este programa.</p><?php endif; ?>
                </section>
                <section class="home-panel p-4" aria-labelledby="teachers-title">
                    <h3 class="home-heading h4 mb-3" id="teachers-title">Docentes</h3>
                    <div class="home-teachers">
                        <?php foreach ($docentes as $docente) : ?>
                            <a class="home-teacher" href="<?= site_url('person/' . (int) $docente['id']) ?>"><span class="home-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($docente['nome'], 0, 1))) ?></span><span><?= esc($docente['nome']) ?></span></a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($docentes === []) : ?><p class="cyra-muted mb-0">Nenhum docente vinculado às linhas deste programa.</p><?php endif; ?>
                </section>
            <?php else : ?>
                <div class="home-panel p-4"><h2 class="home-heading h4" id="selected-title">Explore os programas</h2><p class="cyra-muted mb-0">Os indicadores aparecerão aqui quando houver programas cadastrados.</p></div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?= view('header/footer', ['fluid' => true]) ?>