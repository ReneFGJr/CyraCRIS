<?php
/** @var array<string, mixed> $docente */
/** @var array<int, array<string, mixed>> $instituicoes */
/** @var array<int, array<string, mixed>> $linhas */
/** @var array<int, array<string, mixed>> $orientacoes */
/** @var array<int, array<string, mixed>> $orientadores */
/** @var array<int, array<string, mixed>> $producoes */
/** @var array<int, array<string, mixed>> $projetos */
/** @var array{nodes: array<int, array<string, mixed>>, links: array<int, array<string, mixed>>} $redeIndividual */
/** @var bool $coletaLattesHabilitada */
$genero = match ((int) ($docente['genero'] ?? 0)) { 1 => 'Masculino', 2 => 'Feminino', default => 'Não informado' };
$totalConcluidas = count(array_filter($orientacoes, static fn (array $item): bool => (int) $item['status'] === 1));
$totalAndamento = count($orientacoes) - $totalConcluidas;
$lattesAtualizadoEm = ! empty($docente['lattes_updated_at']) ? new DateTimeImmutable((string) $docente['lattes_updated_at']) : null;
$diasSemAtualizacao = $lattesAtualizadoEm instanceof DateTimeImmutable
    ? max(0, (int) $lattesAtualizadoEm->setTime(0, 0)->diff(new DateTimeImmutable('today'))->format('%r%a'))
    : null;
$orientacoesPorTipo = [];
foreach (['Pos-doc', 'Doutorado', 'Mestrado', 'Iniciação científica', 'TCC (Graduação)', 'Especialização', 'Outras'] as $tipo) {
    $orientacoesPorTipo[$tipo] = ['total' => 0, 'andamento' => 0, 'concluidas' => 0];
}
foreach ($orientacoes as $orientacao) {
    $tipo = (string) $orientacao['tipo'];
    $orientacoesPorTipo[$tipo] ??= ['total' => 0, 'andamento' => 0, 'concluidas' => 0];
    $orientacoesPorTipo[$tipo]['total']++;
    $orientacoesPorTipo[$tipo][(int) $orientacao['status'] === 1 ? 'concluidas' : 'andamento']++;
}
$producoesPorAba = [
    'artigos' => array_filter($producoes, static fn (array $item): bool => $item['grupo'] === 'ARTIGO'),
    'livros' => array_filter($producoes, static fn (array $item): bool => $item['grupo'] === 'LIVRO'),
    'eventos' => array_filter($producoes, static fn (array $item): bool => $item['grupo'] === 'EVENTO'),
    'tecnica' => array_filter($producoes, static fn (array $item): bool => $item['categoria'] === 'TECNICA'),
    'artistica' => array_filter($producoes, static fn (array $item): bool => $item['categoria'] === 'ARTISTICA'),
];
$projetosEmAndamento = array_filter($projetos, static fn (array $item): bool => $item['situacao'] === 'EM_ANDAMENTO');
$projetosConcluidos = array_filter($projetos, static fn (array $item): bool => $item['situacao'] === 'CONCLUIDO');
?>
<?= view('layout/header', ['title' => $docente['nome'], 'description' => 'Perfil acadêmico de ' . $docente['nome'] . '.']) ?>

<style>
    #rede-individual { width: 100%; min-height: 36rem; border: 1px solid rgba(151, 205, 225, .14); background: radial-gradient(circle at center, rgba(18, 102, 177, .18), rgba(5, 19, 40, .86)); }
</style>

<main class="container py-5">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
        <a class="btn btn-sm btn-outline-info rounded-0" href="javascript:history.back()"><i class="bi bi-arrow-left me-2"></i>Voltar</a>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-sm btn-outline-light rounded-0" type="button" data-bs-toggle="modal" data-bs-target="#editar-docente"><i class="bi bi-pencil-square me-2"></i>Editar dados</button>
            <form method="post" action="<?= site_url('docent/' . $docente['id'] . '/atualizar') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-info rounded-0" type="submit" <?= ! $coletaLattesHabilitada ? 'disabled' : '' ?> title="<?= ! $coletaLattesHabilitada ? 'Coleta Lattes temporariamente desabilitada' : 'Atualizar dados pelo Lattes' ?>"><i class="bi bi-arrow-clockwise me-2"></i><?= $coletaLattesHabilitada ? 'Atualizar dados do docente' : 'Coleta Lattes desabilitada' ?></button></form>
        </div>
    </div>
    <?php if (session('sucesso')) : ?><div class="alert alert-success rounded-0" role="alert"><?= esc(session('sucesso')) ?></div><?php endif; ?>
    <?php if (session('erro')) : ?><div class="alert alert-danger rounded-0" role="alert"><?= esc(session('erro')) ?></div><?php endif; ?>

    <header class="mb-4"><div class="d-flex align-items-center gap-3"><i class="bi bi-person-circle display-4 cyra-accent"></i><div><span class="badge text-bg-info rounded-0 mb-2">Perfil #<?= (int) $docente['id'] ?></span><h1 class="cyra-heading text-white mb-0"><?= esc($docente['nome']) ?></h1></div></div></header>

    <ul class="nav nav-tabs flex-nowrap overflow-x-auto border-secondary" id="perfil-tabs" role="tablist">
        <?php foreach ([['resumo','speedometer2','Resumo geral'],['dados','person-vcard','Dados do docente'],['vinculos','building','Instituições e linhas'],['orientacoes','mortarboard','Orientações / Orientadores'],['projetos','kanban','Projetos'],['rede','share','Rede'],['producao','collection','Produção']] as $indice => [$idAba,$icone,$rotulo]) : ?>
            <li class="nav-item" role="presentation"><button class="nav-link text-nowrap rounded-0 <?= $indice === 0 ? 'active' : '' ?>" id="<?= $idAba ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= $idAba ?>" type="button" role="tab" aria-controls="<?= $idAba ?>" aria-selected="<?= $indice === 0 ? 'true' : 'false' ?>"><i class="bi bi-<?= $icone ?> me-1"></i><?= esc($rotulo) ?></button></li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content cyra-panel border-top-0 p-4" id="perfil-tabs-content">
        <section class="tab-pane fade show active" id="resumo" role="tabpanel" aria-labelledby="resumo-tab" tabindex="0">
            <h2 class="h5 text-white mb-4">Resumo geral</h2>
            <div class="row g-3">
                <?php foreach ([['building',count($instituicoes),'Instituições'],['diagram-3',count($linhas),'Linhas de pesquisa'],['hourglass-split',$totalAndamento,'Orientações em andamento'],['check-circle',$totalConcluidas,'Orientações concluídas'],['person-check',count($orientadores),'Orientadores']] as [$icone,$valor,$rotulo]) : ?>
                    <div class="col-sm-6 col-lg"><div class="border border-light border-opacity-10 p-3 h-100"><i class="bi bi-<?= $icone ?> cyra-accent"></i><strong class="d-block display-6 text-white"><?= (int) $valor ?></strong><span class="cyra-muted small"><?= esc($rotulo) ?></span></div></div>
                <?php endforeach; ?>
                <div class="col-sm-6 col-lg">
                    <div class="border border-light border-opacity-10 p-3 h-100">
                        <i class="bi bi-clock-history cyra-accent"></i>
                        <?php if ($lattesAtualizadoEm instanceof DateTimeImmutable) : ?>
                            <strong class="d-block h5 text-white mt-3 mb-1"><?= esc($lattesAtualizadoEm->format('d/m/Y')) ?></strong>
                            <span class="small <?= $diasSemAtualizacao > 180 ? 'text-warning' : 'cyra-muted' ?>"><?= (int) $diasSemAtualizacao ?> <?= $diasSemAtualizacao === 1 ? 'dia' : 'dias' ?> sem atualização</span>
                        <?php else : ?>
                            <strong class="d-block h6 text-white mt-3 mb-1">Não informada</strong>
                            <span class="small cyra-muted">Última atualização Lattes</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="mt-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h3 class="h6 text-white mb-0"><i class="bi bi-mortarboard me-2 cyra-accent"></i>Orientações por tipo</h3>
                    <span class="small cyra-muted"><?= count($orientacoes) ?> no total</span>
                </div>
                <div class="row g-2">
                    <?php foreach ($orientacoesPorTipo as $tipo => $quantidades) : ?>
                        <div class="col-sm-6 col-lg-4 col-xl">
                            <div class="border border-light border-opacity-10 p-3 h-100">
                                <span class="small text-white fw-semibold"><?= esc($tipo) ?></span>
                                <div class="d-flex align-items-baseline gap-2 my-2" aria-label="<?= (int) $quantidades['andamento'] ?> em andamento e <?= (int) $quantidades['concluidas'] ?> concluídas">
                                    <strong class="h3 text-warning mb-0"><?= (int) $quantidades['andamento'] ?></strong>
                                    <span class="h4 cyra-muted mb-0">/</span>
                                    <strong class="h3 mb-0 <?= (int) $quantidades['concluidas'] === 0 ? 'cyra-muted' : 'text-success' ?>"><?= (int) $quantidades['concluidas'] ?></strong>
                                </div>
                                <small class="cyra-muted">Em andamento / Concluídas</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="tab-pane fade" id="dados" role="tabpanel" aria-labelledby="dados-tab" tabindex="0">
            <h2 class="h5 text-white mb-4">Dados do docente</h2>
            <dl class="row mb-0">
                <dt class="col-sm-3 cyra-muted">Gênero</dt><dd class="col-sm-9 text-white mb-3"><?= esc($genero) ?></dd>
                <dt class="col-sm-3 cyra-muted">E-mail</dt><dd class="col-sm-9 mb-3"><?php if (! empty($docente['email'])) : ?><a class="cyra-accent" href="mailto:<?= esc($docente['email'], 'attr') ?>"><?= esc($docente['email']) ?></a><?php else : ?><span class="text-white">Não informado</span><?php endif; ?></dd>
                <dt class="col-sm-3 cyra-muted">ID Lattes</dt><dd class="col-sm-9 mb-3"><?php if (! empty($docente['lattes_id'])) : ?><a class="cyra-accent" href="http://lattes.cnpq.br/<?= esc($docente['lattes_id'], 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($docente['lattes_id']) ?> <i class="bi bi-box-arrow-up-right ms-1"></i></a><?php else : ?><span class="text-white">Não informado</span><?php endif; ?></dd>
                <dt class="col-sm-3 cyra-muted">ORCID</dt><dd class="col-sm-9 mb-0"><?php if (! empty($docente['orcid'])) : ?><a class="cyra-accent" href="https://orcid.org/<?= esc($docente['orcid'], 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($docente['orcid']) ?></a><?php else : ?><span class="text-white">Não informado</span><?php endif; ?></dd>
            </dl>
        </section>

        <section class="tab-pane fade" id="vinculos" role="tabpanel" aria-labelledby="vinculos-tab" tabindex="0">
            <h2 class="h5 text-white mb-3">Instituições de vínculo</h2>
            <?php if ($instituicoes === []) : ?><p class="cyra-muted">Nenhuma instituição vinculada.</p><?php else : ?><ul class="list-unstyled mb-4"><?php foreach ($instituicoes as $instituicao) : ?><li class="text-white mb-3"><i class="bi bi-building me-2 cyra-accent"></i><?= esc($instituicao['nome']) ?><?php if (! empty($instituicao['sigla'])) : ?> <span class="cyra-muted">(<?= esc($instituicao['sigla']) ?>)</span><?php endif; ?><small class="d-block cyra-muted ms-4"><?= esc($instituicao['tipo_vinculo']) ?><?= (int) $instituicao['principal'] === 1 ? ' · Principal' : '' ?></small></li><?php endforeach; ?></ul><?php endif; ?>
            <h2 class="h5 text-white mb-3">Linhas de pesquisa</h2>
            <?php if ($linhas === []) : ?><p class="cyra-muted mb-0">Nenhuma linha de pesquisa vinculada.</p><?php else : ?><div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Programa</th><th>Linha de pesquisa</th><th>Área de concentração</th><th>Vínculo</th></tr></thead><tbody><?php foreach ($linhas as $linha) : ?><tr><td><a class="cyra-accent" href="<?= site_url('ppg/' . $linha['programa_id']) ?>"><?= esc($linha['programa_nome']) ?></a></td><td class="text-white"><?= esc($linha['nome']) ?></td><td class="cyra-muted"><?= esc($linha['area_concentracao'] ?? '-') ?></td><td class="cyra-muted"><?= esc($linha['tipo_vinculo']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>

        <section class="tab-pane fade" id="orientacoes" role="tabpanel" aria-labelledby="orientacoes-tab" tabindex="0">
            <h2 class="h5 text-white mb-3">Orientados</h2>
            <?php if ($orientacoes === []) : ?><p class="cyra-muted">Nenhum estudante orientado.</p><?php else : ?><div class="table-responsive mb-5"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Estudante</th><th>Tipo</th><th>Status</th><th>Início</th><th>Final</th><th>Título</th></tr></thead><tbody><?php foreach ($orientacoes as $item) : ?><tr><td><a class="cyra-accent" href="<?= site_url('docent/' . $item['estudante_id']) ?>"><?= esc($item['estudante_nome']) ?></a></td><td class="text-white"><?= esc($item['tipo']) ?></td><td><span class="badge rounded-0 <?= (int) $item['status'] === 1 ? 'text-bg-success' : 'text-bg-warning' ?>"><?= (int) $item['status'] === 1 ? 'Concluída' : 'Em andamento' ?></span></td><td class="cyra-muted"><?= esc($item['ano_inicio'] ?? '-') ?></td><td class="cyra-muted"><?= esc($item['ano_final'] ?? '-') ?></td><td class="cyra-muted"><?= esc($item['titulo'] ?: '-') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
            <h2 class="h5 text-white mb-3">Orientadores deste estudante</h2>
            <?php if ($orientadores === []) : ?><p class="cyra-muted mb-0">Nenhum orientador registrado para este indivíduo.</p><?php else : ?><div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Orientador</th><th>Tipo</th><th>Status</th><th>Período</th><th>Título</th></tr></thead><tbody><?php foreach ($orientadores as $item) : ?><tr><td><a class="cyra-accent" href="<?= site_url('docent/' . $item['orientador_id']) ?>"><?= esc($item['orientador_nome']) ?></a></td><td class="text-white"><?= esc($item['tipo']) ?></td><td class="cyra-muted"><?= (int) $item['status'] === 1 ? 'Concluída' : 'Em andamento' ?></td><td class="cyra-muted"><?= esc($item['ano_inicio'] ?? '-') ?> – <?= esc($item['ano_final'] ?? '-') ?></td><td class="cyra-muted"><?= esc($item['titulo'] ?: '-') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>

        <section class="tab-pane fade" id="projetos" role="tabpanel" aria-labelledby="projetos-tab" tabindex="0">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <h2 class="h5 text-white mb-0"><i class="bi bi-kanban me-2 cyra-accent"></i>Projetos</h2>
                <span class="small cyra-muted"><?= count($projetos) ?> no total</span>
            </div>
            <?php foreach ([['Em andamento', $projetosEmAndamento, 'warning'], ['Concluídos', $projetosConcluidos, 'success']] as [$tituloGrupo, $itens, $cor]) : ?>
                <h3 class="h6 text-white mt-4 mb-3"><?= esc($tituloGrupo) ?> <span class="badge text-bg-<?= $cor ?> rounded-0 ms-1"><?= count($itens) ?></span></h3>
                <?php if ($itens === []) : ?>
                    <p class="cyra-muted">Nenhum projeto <?= $tituloGrupo === 'Em andamento' ? 'em andamento' : 'concluído' ?>.</p>
                <?php else : ?>
                    <div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Período</th><th>Projeto</th><th>Natureza</th><th>Integrantes</th></tr></thead><tbody>
                    <?php foreach ($itens as $projeto) : ?><tr>
                        <td class="cyra-muted text-nowrap"><?= esc($projeto['ano_inicio'] ?? '-') ?> – <?= esc($projeto['ano_fim'] ?? ($projeto['situacao'] === 'EM_ANDAMENTO' ? 'Atual' : '-')) ?></td>
                        <td class="text-white"><strong><?= esc($projeto['titulo']) ?></strong><?php if (! empty($projeto['descricao'])) : ?><small class="d-block cyra-muted mt-1"><?= esc($projeto['descricao']) ?></small><?php endif; ?></td>
                        <td class="cyra-muted"><?= esc($projeto['natureza'] ?? '-') ?></td>
                        <td class="cyra-muted small"><?= esc($projeto['integrantes'] ?? '-') ?></td>
                    </tr><?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <section class="tab-pane fade" id="rede" role="tabpanel" aria-labelledby="rede-tab" tabindex="0">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div><h2 class="h5 text-white mb-1"><i class="bi bi-share me-2 cyra-accent"></i>Rede do indivíduo</h2><p class="small cyra-muted mb-0">Relações identificadas exclusivamente pelas coautorias nas produções importadas.</p></div>
                <div class="d-flex flex-wrap gap-3 small"><span class="cyra-accent"><i class="bi bi-circle-fill me-1"></i>Indivíduo</span><span class="text-info"><i class="bi bi-circle-fill me-1"></i>Coautor</span></div>
            </div>
            <svg id="rede-individual" role="img" aria-label="Rede de relacionamentos acadêmicos do indivíduo"></svg>
            <?php if (count($redeIndividual['nodes']) === 1) : ?><p class="cyra-muted text-center mt-3 mb-0">Nenhuma relação acadêmica identificada.</p><?php endif; ?>
        </section>

        <section class="tab-pane fade" id="producao" role="tabpanel" aria-labelledby="producao-tab" tabindex="0">
            <h2 class="h5 text-white mb-4"><i class="bi bi-collection me-2 cyra-accent"></i>Produção</h2>
            <?php $subAbas = ['artigos'=>['journal-text','Artigos'],'livros'=>['book','Livros'],'eventos'=>['calendar-event','Eventos'],'tecnica'=>['tools','Produção técnica'],'artistica'=>['palette','Produção artística']]; ?>
            <ul class="nav nav-pills flex-nowrap overflow-x-auto gap-2 mb-4" id="producao-tabs" role="tablist">
                <?php foreach ($subAbas as $indice => $subAba) : ?>
                    <?php [$icone, $titulo] = $subAba; ?>
                    <li class="nav-item" role="presentation"><button class="nav-link text-nowrap rounded-0 <?= $indice === 'artigos' ? 'active' : '' ?>" id="producao-<?= $indice ?>-tab" data-bs-toggle="tab" data-bs-target="#producao-<?= $indice ?>" type="button" role="tab" aria-controls="producao-<?= $indice ?>" aria-selected="<?= $indice === 'artigos' ? 'true' : 'false' ?>"><i class="bi bi-<?= $icone ?> me-1"></i><?= esc($titulo) ?> <span class="badge text-bg-dark ms-1"><?= count($producoesPorAba[$indice]) ?></span></button></li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content" id="producao-tabs-content">
                <?php foreach ($subAbas as $indice => $subAba) : ?>
                    <section class="tab-pane fade <?= $indice === 'artigos' ? 'show active' : '' ?>" id="producao-<?= $indice ?>" role="tabpanel" aria-labelledby="producao-<?= $indice ?>-tab" tabindex="0">
                        <?php if ($producoesPorAba[$indice] === []) : ?>
                            <div class="border border-light border-opacity-10 p-4 text-center"><i class="bi bi-inbox display-5 cyra-muted"></i><p class="cyra-muted mt-3 mb-0">Nenhuma produção cadastrada nesta categoria.</p></div>
                        <?php else : ?>
                            <div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Ano</th><th>Tipo</th><th>Título</th><th>Fonte</th><th>Autores</th></tr></thead><tbody>
                                <?php foreach ($producoesPorAba[$indice] as $item) : ?><tr><td class="cyra-muted"><?= esc($item['ano'] ?? '-') ?></td><td class="text-white"><?= esc($item['tipo']) ?></td><td class="text-white"><?= esc($item['titulo']) ?><?php if (! empty($item['doi'])) : ?><small class="d-block"><a class="cyra-accent" href="https://doi.org/<?= esc($item['doi'], 'attr') ?>" target="_blank" rel="noopener noreferrer">DOI: <?= esc($item['doi']) ?></a></small><?php endif; ?></td><td class="cyra-muted small"><?= esc($item['source_nome'] ?? '-') ?><?php if (! empty($item['source_issn'])) : ?><span class="d-block">ISSN <?= esc($item['source_issn']) ?></span><?php elseif (! empty($item['source_isbn'])) : ?><span class="d-block">ISBN <?= esc($item['source_isbn']) ?></span><?php endif; ?></td><td class="cyra-muted small"><?= esc($item['autores'] ?? '-') ?></td></tr><?php endforeach; ?>
                            </tbody></table></div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<div class="modal fade" id="editar-docente" tabindex="-1" aria-labelledby="editar-docente-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-0 bg-dark text-white border border-info border-opacity-25">
            <form method="post" action="<?= site_url('docent/' . $docente['id'] . '/editar') ?>">
                <?= csrf_field() ?>
                <div class="modal-header border-secondary"><h2 class="modal-title h5" id="editar-docente-titulo"><i class="bi bi-pencil-square me-2 cyra-accent"></i>Editar dados do docente</h2><button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                <div class="modal-body"><div class="row g-3">
                    <div class="col-12"><label class="form-label" for="editar-nome">Nome</label><input class="form-control rounded-0" id="editar-nome" name="nome" type="text" maxlength="255" value="<?= esc(old('nome', $docente['nome']), 'attr') ?>" required></div>
                    <div class="col-md-6"><label class="form-label" for="editar-genero">Gênero</label><select class="form-select rounded-0" id="editar-genero" name="genero" required><?php foreach ([0=>'Não informado',1=>'Masculino',2=>'Feminino'] as $valor => $rotulo) : ?><option value="<?= $valor ?>" <?= (int) old('genero', $docente['genero'] ?? 0) === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label" for="editar-email">E-mail</label><input class="form-control rounded-0" id="editar-email" name="email" type="email" maxlength="190" value="<?= esc(old('email', $docente['email'] ?? ''), 'attr') ?>"></div>
                    <div class="col-md-6"><label class="form-label" for="editar-lattes">ID Lattes</label><input class="form-control rounded-0" id="editar-lattes" name="lattes_id" type="text" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" value="<?= esc(old('lattes_id', $docente['lattes_id'] ?? ''), 'attr') ?>" placeholder="16 dígitos"></div>
                    <div class="col-md-6"><label class="form-label" for="editar-orcid">ORCID</label><input class="form-control rounded-0" id="editar-orcid" name="orcid" type="text" maxlength="19" pattern="[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9Xx]{4}" value="<?= esc(old('orcid', $docente['orcid'] ?? ''), 'attr') ?>" placeholder="0000-0000-0000-0000"></div>
                    <?php if ($linhas !== []) : ?>
                        <div class="col-12 mt-4">
                            <h3 class="h6 text-white border-bottom border-secondary pb-2 mb-3"><i class="bi bi-mortarboard me-2 cyra-accent"></i>Vínculos como docente</h3>
                            <div class="row g-3">
                                <?php foreach ($linhas as $linha) : ?>
                                    <div class="col-md-6">
                                        <label class="form-label small" for="vinculo-linha-<?= (int) $linha['id'] ?>"><?= esc($linha['nome']) ?><span class="d-block cyra-muted fw-normal"><?= esc($linha['programa_nome']) ?></span></label>
                                        <select class="form-select rounded-0" id="vinculo-linha-<?= (int) $linha['id'] ?>" name="vinculos[<?= (int) $linha['id'] ?>]">
                                            <option value="PERMANENTE" <?= old('vinculos.' . $linha['id'], $linha['tipo_vinculo']) === 'PERMANENTE' ? 'selected' : '' ?>>Permanente</option>
                                            <option value="COLABORADOR" <?= old('vinculos.' . $linha['id'], $linha['tipo_vinculo']) === 'COLABORADOR' ? 'selected' : '' ?>>Colaborador</option>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div></div>
                <div class="modal-footer border-secondary"><button class="btn btn-outline-light rounded-0" type="button" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-info rounded-0" type="submit"><i class="bi bi-check-lg me-2"></i>Salvar alterações</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const data = <?= json_encode($redeIndividual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const svg = document.getElementById('rede-individual');
    const render = () => {
        if (!svg || !data.nodes.length) return;
        const ns='http://www.w3.org/2000/svg', width=svg.clientWidth||900, height=576;
        svg.setAttribute('viewBox',`0 0 ${width} ${height}`); svg.replaceChildren();
        const center=data.nodes.find(node=>node.grupo==='central')||data.nodes[0];
        const outros=data.nodes.filter(node=>node.id!==center.id);
        const nodes=[{...center,x:width/2,y:height/2},...outros.map((node,index)=>({...node,x:width/2+Math.cos(index*Math.PI*2/Math.max(outros.length,1))*Math.min(width*.36,310),y:height/2+Math.sin(index*Math.PI*2/Math.max(outros.length,1))*height*.38}))];
        const byId=Object.fromEntries(nodes.map(node=>[node.id,node]));
        data.links.forEach(edge=>{const a=byId[edge.source],b=byId[edge.target];if(!a||!b)return;const line=document.createElementNS(ns,'line');line.setAttribute('x1',a.x);line.setAttribute('y1',a.y);line.setAttribute('x2',b.x);line.setAttribute('y2',b.y);line.setAttribute('stroke','rgba(23,189,197,.48)');line.setAttribute('stroke-width',Math.min(1+edge.peso,7));svg.appendChild(line)});
        const cores={central:'#17bdc5',coautor:'#0dcaf0'};
        nodes.forEach(node=>{const link=document.createElementNS(ns,'a');link.setAttribute('href',`<?= site_url('docent') ?>/${node.id}`);const circle=document.createElementNS(ns,'circle');circle.setAttribute('cx',node.x);circle.setAttribute('cy',node.y);circle.setAttribute('r',node.grupo==='central'?15:9);circle.setAttribute('fill',cores[node.grupo]||'#aac1d4');const title=document.createElementNS(ns,'title');title.textContent=node.nome;circle.appendChild(title);link.appendChild(circle);const text=document.createElementNS(ns,'text');text.setAttribute('x',node.x+14);text.setAttribute('y',node.y+4);text.setAttribute('fill','#edf7fb');text.setAttribute('font-size','11');text.textContent=node.nome.length>30?node.nome.slice(0,29)+'…':node.nome;link.appendChild(text);svg.appendChild(link)});
    };
    document.getElementById('rede-tab')?.addEventListener('shown.bs.tab', render, {once:true});
});
</script>

<?= view('layout/footer') ?>
