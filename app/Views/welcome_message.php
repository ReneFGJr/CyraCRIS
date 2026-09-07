<?= view('layout/header', [
	'title' => 'Início',
	'description' => 'CyraCRIS - Current Research Information System para programas de pós-graduação.',
	'fluid' => true,
]) ?>

<main class="container-fluid px-3 px-md-4 px-xxl-5 py-5" id="sobre">
	<section class="row align-items-center g-5 py-lg-5">
		<div class="col-lg-7">
			<p class="text-uppercase fw-bold small cyra-accent mb-4"><i class="bi bi-diagram-3 me-2"></i>Pesquisa que se conecta</p>
			<h1 class="display-1 cyra-heading text-white lh-1">O conhecimento<br>em <span class="cyra-accent">movimento.</span></h1>
			<p class="lead cyra-muted my-4 pe-lg-5">O CyraCRIS organiza, integra e dá visibilidade à produção científica dos programas de pós-graduação em um só lugar.</p>
			<form class="pe-lg-5" method="get" action="<?= site_url('person/search') ?>" role="search">
				<label class="form-label text-white fw-semibold" for="home-person-search">Buscar indivíduo</label>
				<div class="input-group input-group-lg">
					<span class="input-group-text rounded-0"><i class="bi bi-person-search"></i></span>
					<input class="form-control rounded-0" id="home-person-search" name="q" type="search" placeholder="Digite o nome ou ID Lattes" aria-label="Nome ou ID Lattes do indivíduo" required>
					<button class="btn btn-info rounded-0 px-4" type="submit"><i class="bi bi-search me-2"></i>Buscar</button>
				</div>
			</form>
		</div>

		<div class="col-lg-5" id="recursos">
			<div class="cyra-panel p-4 p-md-5">
				<img class="img-fluid d-block mx-auto mb-4" src="<?= base_url('assets/logo/logo_cyracris_full.png') ?>" alt="CyraCRIS" style="max-height: 120px;">
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

<?= view('layout/footer', ['fluid' => true]) ?>
