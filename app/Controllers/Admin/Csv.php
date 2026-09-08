<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class Csv extends BaseController
{
    public function index(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $db = db_connect();

        return view('admin/csv/index', [
            'title' => 'Importar orientações',
            'total' => $db->table('tmp_orientacoes')->countAllResults(),
            'registros' => $db->table('tmp_orientacoes')->orderBy('id', 'DESC')->limit(100)->get()->getResultArray(),
        ]);
    }

    public function import(): RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $fontes = [];
        $texto = trim((string) $this->request->getPost('data'));
        if ($texto !== '') {
            $fontes[] = $texto;
        }

        $arquivo = $this->request->getFile('csv_file');
        if ($arquivo !== null && $arquivo->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! $arquivo->isValid() || $arquivo->getSize() > 10 * 1024 * 1024) {
                return redirect()->to(site_url('admin/csv'))->with('error', 'Envie um arquivo CSV válido com até 10 MB.')->withInput();
            }

            $conteudo = file_get_contents($arquivo->getTempName());
            if ($conteudo === false) {
                return redirect()->to(site_url('admin/csv'))->with('error', 'Não foi possível ler o arquivo enviado.')->withInput();
            }
            $fontes[] = $conteudo;
        }

        if ($fontes === []) {
            return redirect()->to(site_url('admin/csv'))->with('error', 'Selecione um CSV ou cole os registros no textarea.')->withInput();
        }

        $db = db_connect();
        $importados = 0;
        $duplicados = 0;
        $erros = [];

        foreach ($fontes as $fonte) {
            $linhas = preg_split('/\R/u', preg_replace('/^\xEF\xBB\xBF/', '', $fonte) ?? $fonte) ?: [];
            foreach ($linhas as $indice => $linha) {
                if (trim($linha) === '') {
                    continue;
                }

                $colunas = array_map('trim', str_getcsv($linha, ';'));
                if ($this->isHeader($colunas)) {
                    continue;
                }
                if (count($colunas) < 5) {
                    $erros[] = 'Linha ' . ($indice + 1) . ': são necessárias 5 colunas separadas por ponto e vírgula.';
                    continue;
                }

                [$nome, $idLattes, $ano, $cracha, $orientador] = array_slice($colunas, 0, 5);
                $idLattes = preg_replace('/\D/', '', $idLattes) ?? '';
                if ($nome === '' || preg_match('/^\d{4}$/', $ano) !== 1 || ($idLattes !== '' && strlen($idLattes) !== 16)) {
                    $erros[] = 'Linha ' . ($indice + 1) . ': nome, ano ou ID Lattes inválido.';
                    continue;
                }

                $hash = hash('sha256', implode('|', [
                    $this->normalizar($nome), $idLattes, $ano, $this->normalizar($cracha), $this->normalizar($orientador),
                ]));
                if ($db->table('tmp_orientacoes')->where('hash_registro', $hash)->countAllResults() > 0) {
                    $duplicados++;
                    continue;
                }

                $db->table('tmp_orientacoes')->insert([
                    'nome' => $nome,
                    'id_lattes' => $idLattes,
                    'ano' => (int) $ano,
                    'cracha' => $cracha,
                    'orientador' => $orientador,
                    'hash_registro' => $hash,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $importados++;
            }
        }

        return redirect()->to(site_url('admin/csv'))->with('import_result', compact('importados', 'duplicados', 'erros'));
    }

    private function isHeader(array $colunas): bool
    {
        return isset($colunas[0]) && mb_strtoupper(trim($colunas[0])) === 'NOME'
            && isset($colunas[1]) && mb_strtoupper(trim($colunas[1])) === 'IDLATTES';
    }

    private function normalizar(string $valor): string
    {
        return mb_strtoupper(preg_replace('/\s+/u', ' ', trim($valor)) ?? trim($valor));
    }

    private function requireLogin(): ?RedirectResponse
    {
        return session()->get('auth_logged_in') === true
            ? null
            : redirect()->to(site_url('login'))->with('error', 'Faça login para acessar esta área.');
    }
}
