<?= view('layout/header', [
	'title' => 'Início',
	'description' => 'CyraCRIS - Current Research Information System para programas de pós-graduação.',
]) ?>
<?= view('layout/navbar') ?>

<main class="container py-5" id="sobre">
	<section class="row align-items-center g-5 py-lg-5">
		<div class="col-lg-7">
			<p class="text-uppercase fw-bold small cyra-accent mb-4"><i class="bi bi-diagram-3 me-2"></i>Pesquisa que se conecta</p>
			<h1 class="display-1 cyra-heading text-white lh-1">O conhecimento<br>em <span class="cyra-accent">movimento.</span></h1>
			<p class="lead cyra-muted my-4 pe-lg-5">O CyraCRIS organiza, integra e dá visibilidade à produção científica dos programas de pós-graduação em um só lugar.</p>
			<div class="d-flex flex-wrap gap-3">
				<a class="btn btn-info btn-lg rounded-0 px-4" href="#recursos">Explorar o CyraCRIS <i class="bi bi-arrow-right ms-2"></i></a>
				<a class="btn btn-outline-light btn-lg rounded-0 px-4" href="mailto:contato@cyracris.com.br">Fale conosco</a>
			</div>
		</div>

		<div class="col-lg-5" id="recursos">
			<div class="cyra-panel p-4 p-md-5">
				<p class="text-uppercase small cyra-muted mb-4">Um sistema para cada etapa</p>
				<div class="border-top border-light border-opacity-10 py-3 d-flex gap-3">
					<i class="bi bi-database-check fs-4 cyra-accent"></i>
					<div><h2 class="h5 text-white">Dados organizados</h2><p class="small cyra-muted mb-0">Informações acadêmicas acessíveis e estruturadas.</p></div>
				</div>
				<div class="border-top border-light border-opacity-10 py-3 d-flex gap-3">
					<i class="bi bi-share fs-4 cyra-accent"></i>
					<div><h2 class="h5 text-white">Pesquisa conectada</h2><p class="small cyra-muted mb-0">Conecte pessoas, projetos e resultados.</p></div>
				</div>
				<div class="border-top border-bottom border-light border-opacity-10 py-3 d-flex gap-3">
					<i class="bi bi-graph-up-arrow fs-4 cyra-accent"></i>
					<div><h2 class="h5 text-white">Decisões melhores</h2><p class="small cyra-muted mb-0">Indicadores claros para acompanhar a evolução.</p></div>
				</div>
			</div>
		</div>
	</section>
</main>

<?= view('layout/footer') ?>
<!doctype html>
<html lang="pt-BR">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#071a36">
	<meta name="description" content="CyraCRIS - Current Research Information System para programas de pós-graduação.">
	<title>CyraCRIS | Current Research Information System</title>
	<style>
		:root {
			--navy-950: #061328;
			--navy-900: #091d3b;
			--navy-800: #102f59;
			--blue: #1671c9;
			--cyan: #17bdc5;
			--ice: #edf7fb;
			--muted: #aac1d4;
			--line: rgba(151, 205, 225, 0.2);
			--white: #ffffff;
		}

		* { box-sizing: border-box; }

		html { scroll-behavior: smooth; }

		body {
			min-width: 320px;
			margin: 0;
			color: var(--ice);
			background: var(--navy-950);
			font-family: "Trebuchet MS", "Segoe UI", sans-serif;
		}

		a { color: inherit; text-decoration: none; }

		.page-shell {
			min-height: 100vh;
			overflow: hidden;
			background:
				radial-gradient(circle at 86% 12%, rgba(23, 189, 197, 0.2), transparent 28rem),
				linear-gradient(135deg, var(--navy-950) 0%, var(--navy-900) 52%, #0b426b 100%);
		}

		.topbar {
			display: flex;
			align-items: center;
			justify-content: space-between;
			max-width: 1180px;
			margin: 0 auto;
			padding: 30px 32px;
		}

		.brand-mark {
			width: min(210px, 48vw);
			height: auto;
		}

		.topbar-link {
			display: inline-flex;
			align-items: center;
			gap: 10px;
			color: var(--muted);
			font-size: 0.82rem;
			letter-spacing: 0.12em;
			text-transform: uppercase;
		}

		.topbar-link::after {
			width: 7px;
			height: 7px;
			content: "";
			border-top: 1px solid var(--cyan);
			border-right: 1px solid var(--cyan);
			transform: rotate(45deg);
		}

		.hero {
			display: grid;
			grid-template-columns: minmax(0, 1.12fr) minmax(280px, 0.88fr);
			align-items: center;
			gap: 76px;
			max-width: 1180px;
			min-height: calc(100vh - 112px);
			margin: 0 auto;
			padding: 36px 32px 88px;
		}

		.eyebrow {
			display: flex;
			align-items: center;
			gap: 12px;
			margin: 0 0 24px;
			color: var(--cyan);
			font-size: 0.78rem;
			font-weight: 700;
			letter-spacing: 0.2em;
			text-transform: uppercase;
		}

		.eyebrow::before {
			width: 38px;
			height: 1px;
			content: "";
			background: var(--cyan);
		}

		h1 {
			max-width: 680px;
			margin: 0;
			color: var(--white);
			font-family: Georgia, "Times New Roman", serif;
			font-size: clamp(3.2rem, 7vw, 6.6rem);
			font-weight: 400;
			letter-spacing: 0;
			line-height: 0.94;
		}

		h1 span { color: var(--cyan); }

		.intro {
			max-width: 570px;
			margin: 30px 0 36px;
			color: var(--muted);
			font-size: 1.08rem;
			line-height: 1.75;
		}

		.actions { display: flex; flex-wrap: wrap; gap: 14px; }

		.button {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-height: 50px;
			padding: 0 23px;
			border: 1px solid transparent;
			border-radius: 2px;
			font-size: 0.9rem;
			font-weight: 700;
			letter-spacing: 0.05em;
			transition: transform 180ms ease, background 180ms ease, border-color 180ms ease;
		}

		.button:hover { transform: translateY(-3px); }

		.button-primary { color: var(--navy-950); background: var(--cyan); }
		.button-primary:hover { background: #67d9d6; }
		.button-ghost { color: var(--ice); border-color: var(--line); }
		.button-ghost:hover { border-color: var(--cyan); background: rgba(23, 189, 197, 0.08); }

		.signal-panel {
			position: relative;
			padding: 34px;
			border: 1px solid var(--line);
			background: rgba(5, 19, 40, 0.42);
			box-shadow: 22px 24px 0 rgba(3, 12, 27, 0.2);
		}

		.signal-panel::before {
			position: absolute;
			top: -1px;
			left: -1px;
			width: 74px;
			height: 3px;
			content: "";
			background: var(--cyan);
		}

		.panel-kicker {
			margin: 0 0 34px;
			color: var(--muted);
			font-size: 0.74rem;
			letter-spacing: 0.16em;
			text-transform: uppercase;
		}

		.signal-list { margin: 0; padding: 0; list-style: none; }

		.signal-list li {
			display: flex;
			align-items: flex-start;
			gap: 17px;
			padding: 19px 0;
			border-top: 1px solid var(--line);
		}

		.signal-list li:last-child { border-bottom: 1px solid var(--line); }

		.signal-number { color: var(--cyan); font-size: 0.78rem; letter-spacing: 0.08em; }
		.signal-copy strong { display: block; margin-bottom: 5px; color: var(--white); font-size: 1rem; }
		.signal-copy span { color: var(--muted); font-size: 0.86rem; line-height: 1.45; }

		.footer-note {
			position: absolute;
			right: 32px;
			bottom: 24px;
			color: rgba(237, 247, 251, 0.5);
			font-size: 0.72rem;
			letter-spacing: 0.1em;
			text-transform: uppercase;
		}

		@media (max-width: 760px) {
			.topbar { padding: 24px 22px; }
			.topbar-link { font-size: 0; }
			.topbar-link::after { width: 10px; height: 10px; }
			.hero { display: block; min-height: auto; padding: 70px 22px 100px; }
			h1 { font-size: clamp(3.35rem, 17vw, 5.4rem); }
			.intro { margin-top: 24px; font-size: 1rem; }
			.signal-panel { margin-top: 72px; padding: 26px 22px; }
			.footer-note { position: static; margin: -66px 22px 22px; }
		}

		@media (prefers-reduced-motion: reduce) {
			html { scroll-behavior: auto; }
			.button { transition: none; }
		}
	</style>
</head>
<body>
	<main class="page-shell">
		<header class="topbar">
			<a href="<?= base_url() ?>" aria-label="CyraCRIS - início">
				<img class="brand-mark" src="<?= base_url('assets/logo/logo_cyracris.png') ?>" alt="CyraCRIS">
			</a>
			<a class="topbar-link" href="#sobre">Conheça o sistema</a>
		</header>

		<section class="hero" id="sobre">
			<div class="hero-copy">
				<p class="eyebrow">Pesquisa que se conecta</p>
				<h1>O conhecimento<br>em <span>movimento.</span></h1>
				<p class="intro">O CyraCRIS organiza, integra e dá visibilidade à produção científica dos programas de pós-graduação em um só lugar.</p>
				<div class="actions">
					<a class="button button-primary" href="#recursos">Explorar o CyraCRIS</a>
					<a class="button button-ghost" href="mailto:contato@cyracris.com.br">Fale conosco</a>
				</div>
			</div>

			<aside class="signal-panel" id="recursos" aria-label="Recursos do CyraCRIS">
				<p class="panel-kicker">Um sistema para cada etapa</p>
				<ul class="signal-list">
					<li>
						<span class="signal-number">01</span>
						<span class="signal-copy"><strong>Dados organizados</strong><span>Informações acadêmicas acessíveis e estruturadas.</span></span>
					</li>
					<li>
						<span class="signal-number">02</span>
						<span class="signal-copy"><strong>Pesquisa conectada</strong><span>Conecte pessoas, projetos e resultados.</span></span>
					</li>
					<li>
						<span class="signal-number">03</span>
						<span class="signal-copy"><strong>Decisões melhores</strong><span>Indicadores claros para acompanhar a evolução.</span></span>
					</li>
				</ul>
			</aside>
		</section>

		<p class="footer-note">Current Research Information System</p>
	</main>
</body>
</html>
