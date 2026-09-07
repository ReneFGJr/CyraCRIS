<?php

namespace App\Controllers;

use App\Services\LattesProductionImporter;
use App\Services\LattesProjectImporter;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use DateTimeImmutable;
use RuntimeException;
use SimpleXMLElement;
use Throwable;
use ZipArchive;

class Docent extends BaseController
{
    private const LATTES_API = 'https://brapci.inf.br/ws/api/';

    public function search(): string
    {
        $query = trim((string) $this->request->getGet('q'));
        $pessoas = [];

        if ($query !== '') {
            $pessoas = db_connect()->table('individuo')
                ->select('id, nome, lattes_id, orcid, email')
                ->where('use', 0)
                ->groupStart()
                    ->like('nome', $query, 'both', null, true)
                    ->orLike('lattes_id', $query, 'both', null, true)
                    ->orLike('orcid', $query, 'both', null, true)
                ->groupEnd()
                ->orderBy('nome', 'ASC')
                ->limit(50)
                ->get()
                ->getResultArray();
        }

        return view('person/search', [
            'query'   => $query,
            'pessoas' => $pessoas,
        ]);
    }

    public function show(int $id): string
    {
        $db = db_connect();
        $docente = $db->table('individuo')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($docente === null) {
            throw PageNotFoundException::forPageNotFound('Docente não encontrado.');
        }

        $instituicoes = $db->table('individuo_instituicao ii')
            ->select('inst.nome, inst.sigla, ii.tipo_vinculo, ii.principal')
            ->join('instituicao inst', 'inst.id = ii.instituicao_id')
            ->where('ii.individuo_id', $id)
            ->orderBy('ii.principal', 'DESC')
            ->orderBy('inst.nome', 'ASC')
            ->get()
            ->getResultArray();

        $linhas = $db->table('docentes_linhas_pesquisa dlp')
            ->select('lp.id, lp.nome, lp.area_concentracao, p.id AS programa_id, p.nome AS programa_nome, dlp.tipo_vinculo')
            ->join('linhas_pesquisa lp', 'lp.id = dlp.linha_pesquisa_id')
            ->join('programas_pos_graduacao p', 'p.id = lp.programa_id')
            ->where('dlp.docente_id', $id)
            ->orderBy('p.nome', 'ASC')
            ->orderBy('lp.nome', 'ASC')
            ->get()
            ->getResultArray();

        $orientacoes = $db->table('orientacoes o')
            ->select('o.tipo, o.tipo_orientacao, o.status, o.ano_inicio, o.ano_final, o.titulo, e.id AS estudante_id, e.nome AS estudante_nome, e.lattes_id AS estudante_lattes_id, p.id AS programa_id, p.nome AS programa_nome, inst.id AS instituicao_id, inst.nome AS instituicao_nome')
            ->join('individuo e', 'e.id = o.estudante_id')
            ->join('programas_pos_graduacao p', 'p.id = o.programa_id', 'left')
            ->join('instituicao inst', 'inst.id = o.instituicao_id', 'left')
            ->where('o.orientador_id', $id)
            ->orderBy('o.status', 'ASC')
            ->orderBy('o.ano_final', 'DESC')
            ->orderBy('o.ano_inicio', 'DESC')
            ->get()
            ->getResultArray();

        $orientadores = $db->table('orientacoes o')
            ->select('o.tipo, o.tipo_orientacao, o.status, o.ano_inicio, o.ano_final, o.titulo, i.id AS orientador_id, i.nome AS orientador_nome, p.id AS programa_id, p.nome AS programa_nome, inst.id AS instituicao_id, inst.nome AS instituicao_nome')
            ->join('individuo i', 'i.id = o.orientador_id')
            ->join('programas_pos_graduacao p', 'p.id = o.programa_id', 'left')
            ->join('instituicao inst', 'inst.id = o.instituicao_id', 'left')
            ->where('o.estudante_id', $id)
            ->orderBy('o.status', 'ASC')
            ->orderBy('o.ano_final', 'DESC')
            ->orderBy('o.ano_inicio', 'DESC')
            ->get()
            ->getResultArray();

        $producoes = $db->table('producoes p')
            ->select('p.*, s.tipo AS source_tipo, s.nome AS source_nome, s.issn AS source_issn, s.isbn AS source_isbn, s.editora AS source_editora')
            ->join('source s', 's.id = p.source_id', 'left')
            ->where('p.pesquisador_id', $id)
            ->orderBy('p.ano', 'DESC')
            ->orderBy('p.titulo', 'ASC')
            ->get()
            ->getResultArray();

        $redeIndividual = $this->montarRedeIndividual($docente, $producoes);

        $projetos = $db->table('projetos')->where('pesquisador_id', $id)
            ->orderBy('situacao', 'DESC')->orderBy('ano_inicio', 'DESC')->orderBy('titulo', 'ASC')
            ->get()->getResultArray();

        $remissivas = $db->table('individuo')
            ->select('id, nome, lattes_id, orcid')
            ->where('use', $id)
            ->where('id !=', $id)
            ->orderBy('nome', 'ASC')
            ->get()
            ->getResultArray();

        $rdfDados = [];
        $rdfClasses = [];

        if (session()->get('auth_logged_in') === true) {
            $rdfDados = $db->table('rdf_data d')
                ->select('d.id_d, d.d_update, literal.n_name AS valor, classe.c_class AS classe, propriedade.c_class AS propriedade')
                ->join('rdf_literal literal', 'literal.id_n = d.d_literal', 'left')
                ->join('rdf_class classe', 'classe.id_c = d.d_c2', 'left')
                ->join('rdf_class propriedade', 'propriedade.id_c = d.d_p', 'left')
                ->where('d.d_individuo', $id)
                ->orderBy('classe.c_class', 'ASC')
                ->orderBy('d.id_d', 'DESC')
                ->get()
                ->getResultArray();
            $rdfClasses = $db->table('rdf_class')
                ->select('id_c, c_class, c_description')
                ->where('c_type', 'C')
                ->orderBy('c_class', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('docent/show', [
            'docente'      => $docente,
            'instituicoes' => $instituicoes,
            'linhas'       => $linhas,
            'orientacoes'  => $orientacoes,
            'orientadores' => $orientadores,
            'producoes'    => $producoes,
            'projetos'     => $projetos,
            'remissivas'   => $remissivas,
            'rdfDados'     => $rdfDados,
            'rdfClasses'   => $rdfClasses,
            'redeIndividual' => $redeIndividual,
            'coletaLattesHabilitada' => filter_var(env('lattes.collectionEnabled', false), FILTER_VALIDATE_BOOL),
        ]);
    }

    public function foto(int $id): ResponseInterface
    {
        $docente = db_connect()->table('individuo')->select('lattes_id')->where('id', $id)->get()->getRowArray();
        $lattesId = preg_replace('/\D/', '', (string) ($docente['lattes_id'] ?? ''));
        $arquivo = FCPATH . '_repository' . DIRECTORY_SEPARATOR . 'foto' . DIRECTORY_SEPARATOR . $lattesId . '.jpg';

        if ($docente === null || strlen($lattesId) !== 16 || ! is_file($arquivo)) {
            throw PageNotFoundException::forPageNotFound('Foto não encontrada.');
        }

        $imagem = @getimagesize($arquivo);

        if ($imagem === false || ! str_starts_with((string) ($imagem['mime'] ?? ''), 'image/')) {
            throw PageNotFoundException::forPageNotFound('Foto inválida.');
        }

        return $this->response
            ->setContentType((string) $imagem['mime'])
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody((string) file_get_contents($arquivo));
    }

    public function atualizar(int $id): RedirectResponse
    {
        if (! filter_var(env('lattes.collectionEnabled', false), FILTER_VALIDATE_BOOL)) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'A coleta de dados do Lattes está temporariamente desabilitada.');
        }

        $db = db_connect();
        $cadastroAcessado = $db->table('individuo')->where('id', $id)->get()->getRowArray();

        if ($cadastroAcessado === null) {
            throw PageNotFoundException::forPageNotFound('Docente não encontrado.');
        }

        $idAcessado = $id;
        $useId = (int) ($cadastroAcessado['use'] ?? 0);
        $id = $useId !== 0 ? $useId : $idAcessado;
        $docente = $id === $idAcessado
            ? $cadastroAcessado
            : $db->table('individuo')->where('id', $id)->get()->getRowArray();

        if ($docente === null) {
            return redirect()->to(site_url('person/' . $idAcessado))
                ->with('erro', 'O cadastro principal indicado pelo campo use não existe.');
        }

        $lattesId = trim((string) ($docente['lattes_id'] ?? ''));

        if (preg_match('/^\d{16}$/', $lattesId) !== 1) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'O docente não possui um ID Lattes válido para atualização.');
        }

        $arquivoTemporario = null;

        try {
            $diretorioRepositorio = FCPATH . '_repository';

            if (! is_dir($diretorioRepositorio) && ! mkdir($diretorioRepositorio, 0775, true) && ! is_dir($diretorioRepositorio)) {
                throw new RuntimeException('Não foi possível criar o diretório de arquivos Lattes.');
            }

            $arquivoDestino = $diretorioRepositorio . DIRECTORY_SEPARATOR . $lattesId . '.zip';
            $arquivoSemExtensao = $diretorioRepositorio . DIRECTORY_SEPARATOR . $lattesId;
            $arquivoFonte = is_file($arquivoDestino)
                ? $arquivoDestino
                : (is_file($arquivoSemExtensao) ? $arquivoSemExtensao : null);

            if ($arquivoFonte === null) {
                $token = trim((string) env('lattes.apiToken'));

                if ($token === '') {
                    throw new RuntimeException('O token do serviço Lattes não está configurado.');
                }

                $verificarSsl = filter_var(env('lattes.verifySsl', true), FILTER_VALIDATE_BOOL);
                $caBundle = trim((string) env('lattes.caBundle'));

                if ($verificarSsl && ($caBundle === '' || ! is_file($caBundle))) {
                    throw new RuntimeException('O pacote de certificados SSL do serviço Lattes não está configurado.');
                }

                $response = service('curlrequest')->get(self::LATTES_API, [
                    'query' => [
                        'verb'  => 'lattes',
                        'q'     => $lattesId,
                        'token' => $token,
                    ],
                    'connect_timeout' => 15,
                    'timeout'         => 90,
                    'http_errors'     => false,
                    'verify'          => $verificarSsl ? $caBundle : false,
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new RuntimeException('O serviço Lattes respondeu com HTTP ' . $response->getStatusCode() . '.');
                }

                $arquivoTemporario = tempnam(WRITEPATH, 'lattes_');

                if ($arquivoTemporario === false || file_put_contents($arquivoTemporario, $response->getBody(), LOCK_EX) === false) {
                    throw new RuntimeException('Não foi possível salvar o arquivo temporário.');
                }

                // Só guarda a resposta do serviço depois de confirmar que é um ZIP Lattes válido.
                $this->lerXmlDoZip($arquivoTemporario, $lattesId);

                if (! copy($arquivoTemporario, $arquivoDestino)) {
                    throw new RuntimeException('Não foi possível armazenar o ZIP no repositório.');
                }

                $arquivoFonte = $arquivoDestino;
            }

            [$xml, $nomeArquivoXml] = $this->lerXmlDoZip($arquivoFonte, $lattesId, true);
            $dados = $this->extrairDados($xml);

            $db->transStart();
            $this->limparDadosAcademicos(array_values(array_unique([$idAcessado, $id])));
            $db->table('individuo')->where('id', $id)->update(array_merge($dados['docente'], [
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
            $this->atualizarInstituicao($id, $dados['instituicao']);
            $this->atualizarOrientacoes($id, $xml);
            (new LattesProductionImporter())->importar($id, $xml);
            (new LattesProjectImporter())->importar($id, $xml);
            $db->transComplete();

            if (! $db->transStatus()) {
                throw new RuntimeException('Não foi possível gravar os dados importados.');
            }

            log_message('info', 'Lattes {lattesId} atualizado pelo arquivo {arquivo}.', [
                'lattesId' => $lattesId,
                'arquivo'  => $nomeArquivoXml,
            ]);

            return redirect()->to(site_url('person/' . $id))
                ->with('sucesso', 'Informações pessoais e acadêmicas atualizadas pelo currículo Lattes.');
        } catch (Throwable $e) {
            log_message('error', 'Falha ao atualizar o docente {id}: {erro}', ['id' => $id, 'erro' => $e->getMessage()]);

            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'Não foi possível atualizar o docente: ' . $e->getMessage());
        } finally {
            if ($arquivoTemporario !== null && is_file($arquivoTemporario)) {
                unlink($arquivoTemporario);
            }
        }
    }

    public function uploadFoto(int $id): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('erro', 'Faça login como administrador para alterar a foto.');
        }

        $docente = db_connect()->table('individuo')->where('id', $id)->get()->getRowArray();

        if ($docente === null) {
            throw PageNotFoundException::forPageNotFound('Docente não encontrado.');
        }

        $lattesId = preg_replace('/\D/', '', (string) ($docente['lattes_id'] ?? ''));

        if (strlen($lattesId) !== 16) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'Informe um ID Lattes válido antes de enviar a foto.');
        }

        $foto = $this->request->getFile('foto');

        if ($foto === null || ! $foto->isValid()) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'Selecione uma imagem válida para o perfil.');
        }

        if ($foto->getSize() > 5 * 1024 * 1024) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'A foto deve ter no máximo 5 MB.');
        }

        $imagem = @getimagesize($foto->getTempName());
        $mimesPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if ($imagem === false || ! in_array((string) ($imagem['mime'] ?? ''), $mimesPermitidos, true)) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'Envie uma imagem JPEG, PNG, GIF ou WebP.');
        }

        $diretorio = FCPATH . '_repository' . DIRECTORY_SEPARATOR . 'foto';

        if (! is_dir($diretorio) && ! mkdir($diretorio, 0775, true) && ! is_dir($diretorio)) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'Não foi possível preparar o diretório de fotos.');
        }

        try {
            if (! $this->salvarImagemComoJpeg($foto->getTempName(), $diretorio . DIRECTORY_SEPARATOR . $lattesId . '.jpg')) {
                throw new RuntimeException('Falha ao converter a imagem para JPEG.');
            }
        } catch (Throwable $e) {
            log_message('error', 'Falha ao salvar foto do docente {id}: {erro}', ['id' => $id, 'erro' => $e->getMessage()]);

            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'Não foi possível salvar a foto enviada.');
        }

        return redirect()->to(site_url('person/' . $id))
            ->with('sucesso', 'Foto do perfil atualizada.');
    }

    public function extrairFotoLattes(int $id): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('erro', 'Faça login como administrador para extrair a foto.');
        }

        $docente = db_connect()->table('individuo')->where('id', $id)->get()->getRowArray();

        if ($docente === null) {
            throw PageNotFoundException::forPageNotFound('Docente não encontrado.');
        }

        $lattesId = preg_replace('/\D/', '', (string) ($docente['lattes_id'] ?? ''));

        if (strlen($lattesId) !== 16) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'Informe um ID Lattes válido antes de extrair a foto.');
        }

        $arquivoTemporario = null;

        try {
            $cliente = service('curlrequest');
            $curriculo = $cliente->get('http://lattes.cnpq.br/' . $lattesId, [
                'connect_timeout' => 15,
                'timeout'         => 30,
                'http_errors'     => false,
                'allow_redirects' => ['max' => 5],
            ]);

            if ($curriculo->getStatusCode() !== 200
                || preg_match('/name=["\']id["\'][^>]*value=["\']([A-Z]\d+[A-Z]\d+)["\']/i', $curriculo->getBody(), $resultado) !== 1) {
                throw new RuntimeException('Não foi possível localizar o currículo na Plataforma Lattes.');
            }

            $identificadorInterno = $resultado[1];
            $respostaFoto = $cliente->get('http://servicosweb.cnpq.br/wspessoa/servletrecuperafoto', [
                'query' => ['id' => $identificadorInterno],
                'connect_timeout' => 15,
                'timeout'         => 30,
                'http_errors'     => false,
            ]);

            if ($respostaFoto->getStatusCode() !== 200 || $respostaFoto->getBody() === '') {
                throw new RuntimeException('O currículo não possui uma foto disponível.');
            }

            $diretorio = FCPATH . '_repository' . DIRECTORY_SEPARATOR . 'foto';

            if (! is_dir($diretorio) && ! mkdir($diretorio, 0775, true) && ! is_dir($diretorio)) {
                throw new RuntimeException('Não foi possível preparar o diretório de fotos.');
            }

            $arquivoTemporario = tempnam($diretorio, 'foto_');

            if ($arquivoTemporario === false
                || file_put_contents($arquivoTemporario, $respostaFoto->getBody(), LOCK_EX) === false) {
                throw new RuntimeException('Não foi possível armazenar a foto temporária.');
            }

            $imagem = @getimagesize($arquivoTemporario);
            $mimesPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if ($imagem === false || ! in_array((string) ($imagem['mime'] ?? ''), $mimesPermitidos, true)) {
                throw new RuntimeException('A Plataforma Lattes não retornou uma imagem válida.');
            }

            if (! $this->salvarImagemComoJpeg($arquivoTemporario, $diretorio . DIRECTORY_SEPARATOR . $lattesId . '.jpg')) {
                throw new RuntimeException('Não foi possível converter e salvar a foto extraída.');
            }

            return redirect()->to(site_url('person/' . $id))
                ->with('sucesso', 'Foto extraída da Plataforma Lattes.');
        } catch (Throwable $e) {
            log_message('error', 'Falha ao extrair foto Lattes do docente {id}: {erro}', ['id' => $id, 'erro' => $e->getMessage()]);

            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'Não foi possível extrair a foto: ' . $e->getMessage());
        } finally {
            if ($arquivoTemporario !== null && is_file($arquivoTemporario)) {
                unlink($arquivoTemporario);
            }
        }
    }

    public function adicionarRdfData(int $id): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('erro', 'Faça login como administrador para incluir dados RDF.');
        }

        $db = db_connect();

        if ($db->table('individuo')->where('id', $id)->countAllResults() === 0) {
            throw PageNotFoundException::forPageNotFound('Indivíduo não encontrado.');
        }

        $classeId = (int) $this->request->getPost('rdf_class_id');
        $valor = trim((string) $this->request->getPost('rdf_value'));
        $classe = $db->table('rdf_class')
            ->select('id_c, c_class')
            ->where('id_c', $classeId)
            ->where('c_type', 'C')
            ->get()
            ->getRowArray();

        if ($classe === null || $valor === '') {
            return redirect()->to(site_url('person/' . $id) . '#dados')
                ->with('erro', 'Selecione uma classe RDF e informe o valor textual.');
        }

        if (mb_strlen($valor) > 5000) {
            return redirect()->to(site_url('person/' . $id) . '#dados')
                ->with('erro', 'O valor RDF deve ter no máximo 5.000 caracteres.');
        }

        $propriedade = $db->table('rdf_class')
            ->select('id_c')
            ->where('c_type', 'P')
            ->where('c_class', 'has' . (string) $classe['c_class'])
            ->get()
            ->getRowArray();
        $md5 = md5($valor);
        $literal = $db->table('rdf_literal')
            ->select('id_n')
            ->where('n_md5', $md5)
            ->where('n_name', $valor)
            ->get()
            ->getRowArray();

        if ($literal === null) {
            $db->table('rdf_literal')->insert([
                'n_name'    => $valor,
                'n_lang'    => 'pt_BR',
                'n_md5'     => $md5,
                'n_charset' => 'UTF-8',
            ]);
            $literalId = (int) $db->insertID();
        } else {
            $literalId = (int) $literal['id_n'];
        }

        $duplicado = $db->table('rdf_data')
            ->where('d_individuo', $id)
            ->where('d_c2', $classeId)
            ->where('d_literal', $literalId)
            ->countAllResults() > 0;

        if ($duplicado) {
            return redirect()->to(site_url('person/' . $id) . '#dados')
                ->with('erro', 'Este dado RDF já está vinculado ao indivíduo.');
        }

        $salvo = $db->table('rdf_data')->insert([
            'd_individuo' => $id,
            'd_r1'        => 0,
            'd_p'         => (int) ($propriedade['id_c'] ?? 0),
            'd_r2'        => 0,
            'd_literal'   => $literalId,
            'd_c1'        => 0,
            'd_c2'        => $classeId,
            'd_update'    => date('Y-m-d H:i:s'),
        ]);

        if (! $salvo) {
            return redirect()->to(site_url('person/' . $id) . '#dados')
                ->with('erro', 'Não foi possível salvar o dado RDF.');
        }

        return redirect()->to(site_url('person/' . $id) . '#dados')
            ->with('sucesso', 'Dado RDF incluído com sucesso.');
    }

    private function salvarImagemComoJpeg(string $origem, string $destino): bool
    {
        $conteudo = file_get_contents($origem);

        if ($conteudo === false || ($imagemOriginal = @imagecreatefromstring($conteudo)) === false) {
            return false;
        }

        $largura = imagesx($imagemOriginal);
        $altura = imagesy($imagemOriginal);
        $imagemJpeg = imagecreatetruecolor($largura, $altura);

        if ($imagemJpeg === false) {
            imagedestroy($imagemOriginal);

            return false;
        }

        $branco = imagecolorallocate($imagemJpeg, 255, 255, 255);
        imagefill($imagemJpeg, 0, 0, $branco);
        imagecopy($imagemJpeg, $imagemOriginal, 0, 0, 0, 0, $largura, $altura);
        $salvo = imagejpeg($imagemJpeg, $destino, 90);
        imagedestroy($imagemOriginal);
        imagedestroy($imagemJpeg);

        return $salvo;
    }

    /** @param list<int> $individuoIds */
    private function limparDadosAcademicos(array $individuoIds): void
    {
        $db = db_connect();

        $db->table('orientacoes')
            ->groupStart()
            ->whereIn('orientador_id', $individuoIds)
            ->orWhereIn('estudante_id', $individuoIds)
            ->groupEnd()
            ->delete();
        $db->table('producoes')->whereIn('pesquisador_id', $individuoIds)->delete();
        $db->table('projetos')->whereIn('pesquisador_id', $individuoIds)->delete();
    }

    public function edit(int $id): string|RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login para editar os dados da pessoa.');
        }

        $person = db_connect()->table('individuo')->where('id', $id)->get()->getRowArray();

        if ($person === null) {
            throw PageNotFoundException::forPageNotFound('Pessoa não encontrada.');
        }

        return view('person/edit', [
            'title'  => 'Editar pessoa',
            'person' => $person,
        ]);
    }

    public function editar(int $id): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login para editar os dados da pessoa.');
        }

        $db = db_connect();

        if ($db->table('individuo')->where('id', $id)->countAllResults() === 0) {
            throw PageNotFoundException::forPageNotFound('Docente não encontrado.');
        }

        $nome = trim((string) $this->request->getPost('nome'));
        $genero = (int) $this->request->getPost('genero');
        $email = trim((string) $this->request->getPost('email'));
        $cpf = trim((string) $this->request->getPost('cpf'));
        $cracha = trim((string) $this->request->getPost('cracha'));
        $lattesId = trim((string) $this->request->getPost('lattes_id'));
        $orcid = trim((string) $this->request->getPost('orcid'));
        $vinculos = $this->request->getPost('vinculos');
        $vinculos = is_array($vinculos) ? $vinculos : [];

        $erros = [];
        if ($nome === '' || mb_strlen($nome) > 255) {
            $erros[] = 'Informe um nome válido com até 255 caracteres.';
        }
        if (! in_array($genero, [0, 1, 2], true)) {
            $erros[] = 'Selecione um gênero válido.';
        }
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $erros[] = 'Informe um e-mail válido.';
        }
        if (mb_strlen($cpf) > 14) {
            $erros[] = 'O CPF deve ter no máximo 14 caracteres.';
        }
        if (mb_strlen($cracha) > 50) {
            $erros[] = 'O crachá deve ter no máximo 50 caracteres.';
        }
        if ($lattesId !== '' && preg_match('/^\d{16}$/', $lattesId) !== 1) {
            $erros[] = 'O ID Lattes deve conter 16 dígitos.';
        }
        if ($orcid !== '' && preg_match('/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/i', $orcid) !== 1) {
            $erros[] = 'Informe o ORCID no formato 0000-0000-0000-0000.';
        }
        foreach ($vinculos as $tipoVinculo) {
            if (! in_array($tipoVinculo, ['PERMANENTE', 'COLABORADOR'], true)) {
                $erros[] = 'Selecione um tipo de vínculo docente válido.';
                break;
            }
        }

        if ($erros !== []) {
            $errorTarget = $this->request->getPost('return_to') === 'edit'
                ? site_url('person/edit/' . $id)
                : site_url('person/' . $id);

            return redirect()->to($errorTarget)
                ->with('erro', implode(' ', $erros))
                ->withInput();
        }

        $db->transStart();
        $db->table('individuo')->where('id', $id)->update([
            'nome'       => $nome,
            'genero'     => $genero,
            'email'      => $email !== '' ? $email : null,
            'cpf'        => $cpf !== '' ? $cpf : null,
            'cracha'     => $cracha !== '' ? $cracha : null,
            'lattes_id'  => $lattesId !== '' ? $lattesId : null,
            'orcid'      => $orcid !== '' ? strtoupper($orcid) : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        foreach ($vinculos as $linhaId => $tipoVinculo) {
            if (! ctype_digit((string) $linhaId)) {
                continue;
            }
            $db->table('docentes_linhas_pesquisa')
                ->where('docente_id', $id)
                ->where('linha_pesquisa_id', (int) $linhaId)
                ->update(['tipo_vinculo' => $tipoVinculo]);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('person/' . $id))->with('erro', 'Não foi possível salvar as alterações.');
        }

        return redirect()->to(site_url('person/' . $id))->with('sucesso', 'Informações pessoais e acadêmicas atualizadas manualmente.');
    }

    public function deleteReference(int $id, int $referenceId): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login para excluir uma remissiva.');
        }

        $db = db_connect();
        $reference = $db->table('individuo')
            ->select('id, nome, use')
            ->where('id', $referenceId)
            ->where('use', $id)
            ->get()
            ->getRowArray();

        if ($reference === null) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'A remissiva informada não foi encontrada.');
        }

        if ($db->table('individuo')->where('id', $referenceId)->update(['use' => 0]) === false) {
            return redirect()->to(site_url('person/' . $id))
                ->with('erro', 'Não foi possível excluir a remissiva.');
        }

        return redirect()->to(site_url('person/' . $id))
            ->with('sucesso', 'Remissiva de ' . $reference['nome'] . ' excluída com sucesso.');
    }

    /** @return array{0: SimpleXMLElement, 1: string} */
    private function lerXmlDoZip(string $arquivoZip, string $lattesId, bool $salvarNoRepositorio = false): array
    {
        $zip = new ZipArchive();

        if ($zip->open($arquivoZip) !== true) {
            throw new RuntimeException('O serviço não retornou um arquivo ZIP válido.');
        }

        $indiceXml = $zip->locateName($lattesId . '.xml', ZipArchive::FL_NOCASE);

        if ($indiceXml === false) {
            for ($indice = 0; $indice < $zip->numFiles; $indice++) {
                $nome = (string) $zip->getNameIndex($indice);

                if (str_ends_with(strtolower($nome), '.xml')) {
                    $indiceXml = $indice;
                    break;
                }
            }
        }

        if ($indiceXml === false) {
            $zip->close();
            throw new RuntimeException('O ZIP não contém um currículo em XML.');
        }

        $nomeArquivoXml = (string) $zip->getNameIndex($indiceXml);
        $conteudoXml = $zip->getFromIndex($indiceXml);
        $zip->close();

        if ($conteudoXml === false) {
            throw new RuntimeException('Não foi possível descompactar o XML do currículo.');
        }

        $xml = simplexml_load_string($conteudoXml);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('O XML do currículo é inválido.');
        }

        if ((string) $xml['NUMERO-IDENTIFICADOR'] !== $lattesId) {
            throw new RuntimeException('O currículo retornado não corresponde ao ID Lattes do docente.');
        }

        if ($salvarNoRepositorio) {
            $diretorioLattes = FCPATH . '_repository' . DIRECTORY_SEPARATOR . 'lattes';

            if (! is_dir($diretorioLattes) && ! mkdir($diretorioLattes, 0775, true) && ! is_dir($diretorioLattes)) {
                throw new RuntimeException('Não foi possível criar o diretório de XMLs Lattes.');
            }

            $arquivoXml = $diretorioLattes . DIRECTORY_SEPARATOR . $lattesId . '.xml';

            if (file_put_contents($arquivoXml, $conteudoXml, LOCK_EX) === false) {
                throw new RuntimeException('Não foi possível salvar o XML descompactado do currículo.');
            }
        }

        return [$xml, $nomeArquivoXml];
    }

    /** @return array{docente: array<string, string>, instituicao: array<string, string>} */
    private function extrairDados(SimpleXMLElement $xml): array
    {
        $dadosGerais = $xml->{'DADOS-GERAIS'};

        if (! $dadosGerais instanceof SimpleXMLElement) {
            throw new RuntimeException('O currículo não contém dados gerais.');
        }

        $docente = ['nome' => trim((string) $dadosGerais['NOME-COMPLETO'])];
        $dataAtualizacao = trim((string) $xml['DATA-ATUALIZACAO']);
        $horaAtualizacao = trim((string) $xml['HORA-ATUALIZACAO']);
        $atualizacaoLattes = DateTimeImmutable::createFromFormat('!dmY His', $dataAtualizacao . ' ' . str_pad($horaAtualizacao, 6, '0', STR_PAD_LEFT));

        if ($atualizacaoLattes instanceof DateTimeImmutable) {
            $docente['lattes_updated_at'] = $atualizacaoLattes->format('Y-m-d H:i:s');
        }
        $orcid = preg_replace('#^https?://orcid\.org/#i', '', trim((string) $dadosGerais['ORCID-ID']));

        if ($orcid !== '' && preg_match('/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/i', $orcid) === 1) {
            $docente['orcid'] = strtoupper($orcid);
        }

        $endereco = $dadosGerais->ENDERECO;
        $eletronico = trim((string) ($endereco['ELETRONICO'] ?? ''));

        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $eletronico, $email) === 1) {
            $docente['email'] = $email[0];
        }

        $profissional = $endereco->{'ENDERECO-PROFISSIONAL'};
        $instituicao = [];

        if ($profissional instanceof SimpleXMLElement) {
            $instituicao = [
                'nome'     => trim((string) $profissional['NOME-INSTITUICAO-EMPRESA']),
                'telefone' => trim(implode(' ', array_filter([
                    (string) $profissional['DDD'],
                    (string) $profissional['TELEFONE'],
                ]))),
                'website'  => trim((string) $profissional['HOME-PAGE']),
            ];
        }

        return ['docente' => array_filter($docente, static fn ($valor) => $valor !== ''), 'instituicao' => $instituicao];
    }

    /** @param array<string, string> $dados */
    private function atualizarInstituicao(int $docenteId, array $dados): void
    {
        if (($dados['nome'] ?? '') === '') {
            return;
        }

        $db = db_connect();
        $instituicao = $db->table('instituicao')->select('id')->where('nome', $dados['nome'])->get()->getRowArray();

        if ($instituicao === null) {
            $agora = date('Y-m-d H:i:s');
            $db->table('instituicao')->insert(array_merge($dados, ['created_at' => $agora, 'updated_at' => $agora]));
            $instituicaoId = (int) $db->insertID();
        } else {
            $instituicaoId = (int) $instituicao['id'];
            $db->table('instituicao')->where('id', $instituicaoId)->update(array_merge($dados, ['updated_at' => date('Y-m-d H:i:s')]));
        }

        $db->table('individuo')->where('id', $docenteId)->update(['instituicao' => $instituicaoId]);
        $vinculoExiste = $db->table('individuo_instituicao')
            ->where('individuo_id', $docenteId)
            ->where('instituicao_id', $instituicaoId)
            ->countAllResults() > 0;

        if (! $vinculoExiste) {
            $db->table('individuo_instituicao')->insert([
                'individuo_id'  => $docenteId,
                'instituicao_id'=> $instituicaoId,
                'tipo_vinculo'  => 'VINCULADO',
                'principal'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function atualizarOrientacoes(int $orientadorId, SimpleXMLElement $xml): void
    {
        $db = db_connect();
        $programas = $db->table('programas_pos_graduacao')
            ->select('id, nome, instituicao_codigo, instituicao_nome, instituicao_sigla, graus')
            ->get()
            ->getResultArray();
        $instituicoes = $db->table('instituicao')
            ->select('id, codigo_externo, nome, sigla')
            ->get()
            ->getResultArray();

        foreach ([0 => '//ORIENTACOES-EM-ANDAMENTO/*', 1 => '//ORIENTACOES-CONCLUIDAS/*'] as $status => $xpath) {
            foreach ($xml->xpath($xpath) ?: [] as $orientacaoXml) {
                $elementos = $orientacaoXml->children();
                $dadosBasicos = $elementos[0] ?? null;
                $detalhamento = $elementos[1] ?? null;

                if (! $dadosBasicos instanceof SimpleXMLElement || ! $detalhamento instanceof SimpleXMLElement) {
                    continue;
                }

                $nome = trim((string) ($detalhamento['NOME-DO-ORIENTADO'] ?: $detalhamento['NOME-DO-ORIENTANDO']));

                if ($nome === '') {
                    continue;
                }

                $lattesId = trim((string) $detalhamento['NUMERO-ID-ORIENTADO']);
                $lattesId = preg_match('/^\d{16}$/', $lattesId) === 1 ? $lattesId : null;
                $estudanteId = $this->localizarOuCriarEstudante($nome, $lattesId);

                if ($estudanteId === $orientadorId) {
                    log_message('warning', 'Orientação própria ignorada para o indivíduo {id}: {titulo}', [
                        'id'     => $orientadorId,
                        'titulo' => (string) ($dadosBasicos['TITULO'] ?: $dadosBasicos['TITULO-DO-TRABALHO']),
                    ]);
                    continue;
                }

                $tipo = $this->classificarOrientacao($orientacaoXml->getName(), (string) $dadosBasicos['NATUREZA']);
                $tipoOrientacaoXml = str_replace(['-', ' '], '_', strtoupper(trim((string) $detalhamento['TIPO-DE-ORIENTACAO'])));
                $tipoOrientacao = $tipoOrientacaoXml === 'CO_ORIENTADOR' ? 'CO_ORIENTADOR' : 'ORIENTADOR';
                $programaId = in_array($tipo, ['Mestrado', 'Doutorado'], true)
                    ? $this->localizarProgramaOrientacao($tipo, $detalhamento, $programas)
                    : null;
                $instituicaoId = $this->localizarInstituicaoOrientacao($detalhamento, $instituicoes, $programaId, $programas);
                $ano = (int) $dadosBasicos['ANO'];
                $agora = date('Y-m-d H:i:s');
                $dados = [
                    'programa_id'   => $programaId,
                    'instituicao_id' => $instituicaoId,
                    'tipo_orientacao' => $tipoOrientacao,
                    'status'     => $status,
                    'ano_inicio' => $status === 0 && $ano > 0 ? $ano : null,
                    'ano_final'  => $status === 1 && $ano > 0 ? $ano : null,
                    'titulo'     => trim((string) ($dadosBasicos['TITULO'] ?: $dadosBasicos['TITULO-DO-TRABALHO'])),
                    'updated_at' => $agora,
                ];

                $existente = db_connect()->table('orientacoes')
                    ->select('id, ano_inicio')
                    ->where('orientador_id', $orientadorId)
                    ->where('estudante_id', $estudanteId)
                    ->where('tipo', $tipo)
                    ->get()
                    ->getRowArray();

                if ($existente === null) {
                    db_connect()->table('orientacoes')->insert(array_merge($dados, [
                        'orientador_id' => $orientadorId,
                        'estudante_id'  => $estudanteId,
                        'tipo'          => $tipo,
                        'created_at'    => $agora,
                    ]));
                } else {
                    if ($status === 1 && $dados['ano_inicio'] === null && ! empty($existente['ano_inicio'])) {
                        $dados['ano_inicio'] = (int) $existente['ano_inicio'];
                    }

                    db_connect()->table('orientacoes')->where('id', $existente['id'])->update($dados);
                }
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $programas
     */
    private function localizarProgramaOrientacao(string $tipo, SimpleXMLElement $detalhamento, array $programas): ?int
    {
        $curso = $this->normalizarNomePrograma((string) $detalhamento['NOME-CURSO']);
        $instituicao = $this->normalizarNome((string) $detalhamento['NOME-INSTITUICAO']);
        $melhorId = null;
        $melhorPontuacao = 0;

        if ($curso === '') {
            return null;
        }

        foreach ($programas as $programa) {
            $graus = json_decode((string) ($programa['graus'] ?? '[]'), true);

            if (is_array($graus) && $graus !== [] && ! in_array($tipo, $graus, true)) {
                continue;
            }

            $nomeInstituicaoPrograma = $this->normalizarNome((string) ($programa['instituicao_nome'] ?? ''));

            // Cursos homônimos existem em instituições diferentes. Sem a mesma
            // instituição, a orientação não pode ser atribuída a este PPG.
            if ($instituicao === '' || $nomeInstituicaoPrograma === '' || $nomeInstituicaoPrograma !== $instituicao) {
                continue;
            }

            $nomePrograma = $this->normalizarNomePrograma((string) $programa['nome']);
            $pontuacao = $curso === $nomePrograma
                ? 10
                : ((str_contains($curso, $nomePrograma) || str_contains($nomePrograma, $curso)) ? 6 : 0);

            if ($pontuacao === 0) {
                continue;
            }

            $pontuacao += 5;

            if ($pontuacao > $melhorPontuacao) {
                $melhorPontuacao = $pontuacao;
                $melhorId = (int) $programa['id'];
            }
        }

        return $melhorId;
    }

    /**
     * @param array<int, array<string, mixed>> $instituicoes
     * @param array<int, array<string, mixed>> $programas
     */
    private function localizarInstituicaoOrientacao(SimpleXMLElement $detalhamento, array &$instituicoes, ?int $programaId, array $programas): ?int
    {
        $nomeOriginal = trim((string) $detalhamento['NOME-INSTITUICAO']);
        $nome = $this->normalizarNome($nomeOriginal);
        $codigo = preg_replace('/\D/', '', (string) $detalhamento['CODIGO-INSTITUICAO']) ?? '';
        $codigoSemZeros = ltrim($codigo, '0');
        $codigoExterno = $codigoSemZeros !== '' ? (int) $codigoSemZeros : null;

        foreach ($instituicoes as $instituicao) {
            if ($nome !== '' && $this->normalizarNome((string) $instituicao['nome']) === $nome) {
                return (int) $instituicao['id'];
            }

            if ($codigoExterno !== null && (int) ($instituicao['codigo_externo'] ?? 0) === $codigoExterno) {
                return (int) $instituicao['id'];
            }
        }

        if ($programaId !== null) {
            foreach ($programas as $programa) {
                if ((int) $programa['id'] !== $programaId) {
                    continue;
                }

                foreach ($instituicoes as $instituicao) {
                    if (! empty($programa['instituicao_codigo'])
                        && (int) $instituicao['codigo_externo'] === (int) $programa['instituicao_codigo']) {
                        return (int) $instituicao['id'];
                    }
                }
            }
        }

        if ($nomeOriginal === '') {
            return null;
        }

        $agora = date('Y-m-d H:i:s');
        $novaInstituicao = [
            'codigo_externo' => $codigoExterno,
            'nome'           => $nomeOriginal,
            'sigla'          => null,
            'created_at'     => $agora,
            'updated_at'     => $agora,
        ];
        $db = db_connect();

        if (! $db->table('instituicao')->insert($novaInstituicao)) {
            log_message('warning', 'Não foi possível cadastrar a instituição da orientação: {instituicao}.', [
                'instituicao' => $nomeOriginal,
            ]);

            return null;
        }

        $novaInstituicao['id'] = (int) $db->insertID();
        $instituicoes[] = $novaInstituicao;

        return (int) $novaInstituicao['id'];
    }

    private function normalizarNomePrograma(string $nome): string
    {
        $nome = $this->normalizarNome($nome);

        return trim((string) preg_replace('/^(programa de )?pos graduacao (em |de )?/', '', $nome));
    }

    private function localizarOuCriarEstudante(string $nome, ?string $lattesId): int
    {
        $db = db_connect();
        $estudante = $db->table('individuo')
            ->select('id, use')
            ->like('nome', trim($nome), 'none', null, true)
            ->orderBy('use', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if ($estudante !== null) {
            $useId = (int) ($estudante['use'] ?? 0);

            return $useId !== 0 ? $useId : (int) $estudante['id'];
        }

        $agora = date('Y-m-d H:i:s');
        $db->table('individuo')->insert([
            'nome'       => $nome,
            'lattes_id'  => $lattesId,
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);

        return (int) $db->insertID();
    }

    private function classificarOrientacao(string $elemento, string $natureza): string
    {
        $valor = strtoupper($elemento . ' ' . $natureza);

        return match (true) {
            str_contains($valor, 'POS-DOUTORADO'),
            str_contains($valor, 'POS_DOUTORADO') => 'Pós-doc',
            str_contains($valor, 'DOUTORADO') => 'Doutorado',
            str_contains($valor, 'MESTRADO') => 'Mestrado',
            str_contains($valor, 'INICIACAO-CIENTIFICA'),
            str_contains($valor, 'INICIACAO_CIENTIFICA') => 'Iniciação científica',
            str_contains($valor, 'GRADUACAO') => 'TCC (Graduação)',
            str_contains($valor, 'ESPECIALIZACAO'),
            str_contains($valor, 'APERFEICOAMENTO') => 'Especialização',
            default => 'Outras',
        };
    }

    /**
     * @param array<string, mixed> $docente
     * @param array<int, array<string, mixed>> $producoes
     * @return array{nodes: array<int, array<string, mixed>>, links: array<int, array<string, mixed>>}
     */
    private function montarRedeIndividual(array $docente, array $producoes): array
    {
        $idCentral = (int) $docente['id'];
        $nomeCentral = $this->normalizarNome((string) $docente['nome']);
        $nodes = [$idCentral => ['id' => $idCentral, 'nome' => $docente['nome'], 'grupo' => 'central']];
        $links = [];

        $individuos = db_connect()->table('individuo')->select('id, nome')->get()->getResultArray();
        $porNome = [];
        foreach ($individuos as $individuo) {
            $porNome[$this->normalizarNome((string) $individuo['nome'])] = $individuo;
        }
        $porNome[$nomeCentral] = ['id' => $idCentral, 'nome' => $docente['nome']];
        foreach ($producoes as $producao) {
            foreach (explode(';', (string) ($producao['autores'] ?? '')) as $autor) {
                $nome = $this->normalizarNome($autor);
                if (! isset($porNome[$nome])) {
                    continue;
                }
                $coautor = $porNome[$nome];
                $id = (int) $coautor['id'];
                if ($id === $idCentral) {
                    continue;
                }
                $nodes[$id] ??= ['id' => $id, 'nome' => $coautor['nome'], 'grupo' => 'coautor'];
                $chave = 'producao-' . $id;
                $links[$chave] = ['source' => $idCentral, 'target' => $id, 'peso' => ($links[$chave]['peso'] ?? 0) + 1, 'tipo' => 'producao'];
            }
        }

        return ['nodes' => array_values($nodes), 'links' => array_values($links)];
    }

    private function normalizarNome(string $nome): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($nome));
        return preg_replace('/[^a-z0-9]+/', ' ', strtolower($ascii !== false ? $ascii : $nome)) ?? '';
    }
}
