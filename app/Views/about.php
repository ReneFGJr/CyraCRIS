<?= view('layout/header', [
    'title'       => 'Sobre o CyraCRIS',
    'description' => 'Conheça a identidade, a proposta e os fundamentos do CyraCRIS.',
    'fluid'       => true,
]) ?>

<style>
    .about-hero {
        position: relative;
        overflow: hidden;
        min-height: 28rem;
        border: 1px solid rgba(23, 189, 197, .28);
        background:
            radial-gradient(circle at 82% 28%, rgba(23, 189, 197, .2), transparent 22rem),
            linear-gradient(120deg, rgba(18, 102, 177, .34), rgba(5, 19, 40, .76));
    }
    .about-hero::after {
        position: absolute;
        width: 26rem;
        height: 26rem;
        right: -9rem;
        bottom: -17rem;
        border: 1px solid rgba(23, 189, 197, .24);
        border-radius: 50%;
        content: '';
    }
    .about-kicker {
        color: var(--cyra-cyan);
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }
    .about-intro { max-width: 58rem; }
    .about-section {
        height: 100%;
        border: 1px solid rgba(151, 205, 225, .18);
        background: rgba(5, 19, 40, .42);
    }
    .about-section p {
        color: var(--cyra-muted);
        font-size: 1.03rem;
        line-height: 1.8;
    }
    .about-section strong { color: var(--cyra-ice); }
    .identity-item {
        border-top: 1px solid rgba(151, 205, 225, .14);
    }
    .identity-letter {
        display: grid;
        width: 3.2rem;
        height: 3.2rem;
        flex: 0 0 3.2rem;
        place-items: center;
        border: 1px solid rgba(23, 189, 197, .36);
        color: var(--cyra-cyan);
        background: rgba(23, 189, 197, .08);
        font: 700 1.35rem Georgia, serif;
    }
    .about-closing {
        border: 1px solid rgba(23, 189, 197, .34);
        background: linear-gradient(135deg, rgba(18, 102, 177, .24), rgba(23, 189, 197, .09));
    }
</style>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-lg-5">
    <header class="about-hero d-flex align-items-center p-4 p-md-5 mb-4 mb-lg-5">
        <div class="position-relative" style="z-index: 1">
            <p class="about-kicker mb-3"><i class="bi bi-diagram-3 me-2"></i>Nossa identidade</p>
            <h1 class="display-3 cyra-heading text-white mb-4">Sobre o <span class="cyra-accent">CyraCRIS</span></h1>
            <h2 class="h3 cyra-heading text-white mb-3">Cyra nasceu para conectar o conhecimento</h2>
            <p class="lead cyra-muted about-intro mb-0">Em um Programa de Pós-Graduação, a pesquisa está em constante movimento. Pesquisadores desenvolvem projetos, estabelecem colaborações, publicam artigos, produzem dados, orientam dissertações e teses e constroem novas relações científicas.</p>
        </div>
    </header>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <section class="about-section p-4 p-md-5">
                <p>Entretanto, as informações que representam essa atividade frequentemente encontram-se dispersas em diferentes sistemas, currículos, repositórios e bases de dados.</p>
                <p>É nesse ecossistema que surge <strong>Cyra</strong>.</p>
                <p>Cyra representa uma inteligência concebida para <strong>reunir, conectar e dar sentido às informações sobre a pesquisa</strong>.</p>
                <p class="mb-0">Ela não observa uma publicação apenas como um registro isolado. Cyra busca compreender as relações existentes entre autores, instituições, programas de pós-graduação, projetos, produções científicas e os temas que constituem a pesquisa.</p>
            </section>
        </div>
        <div class="col-xl-6">
            <section class="about-section p-4 p-md-5">
                <p class="about-kicker mb-3">Conhecimento conectado</p>
                <h2 class="h2 cyra-heading text-white mb-4">O conhecimento como uma rede</h2>
                <p>O símbolo central de Cyra é a <strong>rede</strong>.</p>
                <p>Cada ponto representa uma entidade do ecossistema científico — uma pessoa, uma publicação, um projeto, um programa ou uma instituição. As conexões entre esses pontos revelam relações que ajudam a compreender como o conhecimento é produzido, compartilhado e transformado.</p>
                <p>Assim, registros que antes estavam dispersos passam a integrar uma estrutura de informação capaz de revelar trajetórias, aproximações temáticas, redes de colaboração e características da produção científica.</p>
                <p class="h5 cyra-accent mb-0"><strong>Cyra conecta os caminhos da pesquisa.</strong></p>
            </section>
        </div>
    </div>

    <section class="about-section p-4 p-md-5 mb-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-4">
                <p class="about-kicker mb-3">Sistema de informação</p>
                <h2 class="display-6 cyra-heading text-white mb-0">Cyra + CRIS</h2>
            </div>
            <div class="col-lg-8">
                <p>O nome <strong>CyraCRIS</strong> une essa identidade ao conceito internacional de <strong>CRIS — Current Research Information System</strong>.</p>
                <p>Os sistemas CRIS são destinados à organização e integração de informações relacionadas às atividades de pesquisa. No CyraCRIS, esse conceito é direcionado especialmente às necessidades dos <strong>Programas de Pós-Graduação</strong>, proporcionando uma visão integrada de seu ecossistema científico.</p>
                <p>Mais do que armazenar informações, o CyraCRIS busca estabelecer relações entre elas.</p>
                <p class="mb-0">Dessa forma, pessoas, projetos, publicações e instituições deixam de ser apenas registros isolados e passam a constituir uma <strong>rede de conhecimento</strong>.</p>
            </div>
        </div>
    </section>

    <section class="about-section p-4 p-md-5 mb-4">
        <p class="about-kicker mb-3">Significado</p>
        <h2 class="h2 cyra-heading text-white mb-4">A identidade de Cyra</h2>
        <p>O próprio nome <strong>CYRA</strong> traduz elementos que orientam essa proposta:</p>
        <div class="row g-0 mt-4">
            <?php foreach ([
                ['C', 'Conectar', 'Integrar informações e revelar relações existentes no ecossistema científico.'],
                ['Y', 'Convergência', 'O encontro entre diferentes caminhos do conhecimento. Graficamente, o “Y” representa caminhos que convergem e voltam a se expandir, assim como ocorre nas redes de pesquisa.'],
                ['R', 'Research', 'A pesquisa como elemento central de todo o ecossistema informacional.'],
                ['A', 'Analytics', 'Transformar dados e relações em indicadores, análises e informações que apoiem a compreensão e a gestão da pesquisa.'],
            ] as [$letter, $name, $copy]) : ?>
                <div class="col-12 col-lg-6 identity-item py-4 pe-lg-5">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="identity-letter"><?= esc($letter) ?></div>
                        <div><h3 class="h5 text-white mb-2"><?= esc($name) ?></h3><p class="mb-0"><?= esc($copy) ?></p></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="about-section p-4 p-md-5 mb-4">
        <p class="about-kicker mb-3">Propósito</p>
        <h2 class="h2 cyra-heading text-white mb-4">Da informação à inteligência sobre a pesquisa</h2>
        <p>O <strong>CyraCRIS</strong> não pretende ser apenas uma base de dados.</p>
        <p>Sua proposta é atuar como uma <strong>camada inteligente sobre o ecossistema de pesquisa</strong>, transformando registros dispersos em informação estruturada e conectada.</p>
        <p>Essa estrutura permite apoiar diferentes processos relacionados à pós-graduação e à pesquisa, como a descoberta da produção científica, o conhecimento das competências dos pesquisadores, a identificação de redes de colaboração, o acompanhamento de projetos e produções e a geração de indicadores para gestão e tomada de decisão.</p>
        <div class="row g-3 mt-4 text-center">
            <div class="col-md-4"><div class="border border-light border-opacity-10 p-4 h-100 text-white">No CyraCRIS, cada registro é um ponto.</div></div>
            <div class="col-md-4"><div class="border border-light border-opacity-10 p-4 h-100 text-white">Cada relação é um caminho.</div></div>
            <div class="col-md-4"><div class="border border-light border-opacity-10 p-4 h-100 text-white">E cada caminho ajuda a compreender melhor a pesquisa.</div></div>
        </div>
    </section>

    <footer class="about-closing p-4 p-md-5 text-center">
        <h2 class="display-5 cyra-heading text-white mb-3">CyraCRIS</h2>
        <p class="h4 cyra-accent mb-3"><strong>Conectando pesquisas. Revelando conhecimento.</strong></p>
        <p class="cyra-muted fst-italic mb-0">Current Research Information System para Programas de Pós-Graduação</p>
    </footer>
</main>

<?= view('layout/footer', ['fluid' => true]) ?>
