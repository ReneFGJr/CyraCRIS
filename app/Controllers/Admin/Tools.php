<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class Tools extends BaseController
{
    public function duplicateNames(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $people = db_connect()->table('individuo')
            ->select('id, nome, lattes_id, orcid')
            ->where('use', 0)
            ->orderBy('nome', 'ASC')
            ->get()
            ->getResultArray();
        $normalized = [];

        foreach ($people as $index => $person) {
            $normalized[$index] = $this->normalizeName((string) $person['nome']);
        }

        $duplicates = [];
        $total = count($people);

        for ($left = 0; $left < $total; $left++) {
            $leftLength = strlen($normalized[$left]);

            for ($right = $left + 1; $right < $total; $right++) {
                $rightLength = strlen($normalized[$right]);
                $maxLength = max($leftLength, $rightLength);

                if ($maxLength === 0 || abs($leftLength - $rightLength) / $maxLength >= 0.10) {
                    continue;
                }

                $similarity = (1 - levenshtein($normalized[$left], $normalized[$right]) / $maxLength) * 100;

                if ($similarity > 90) {
                    $duplicates[] = [
                        'left'       => $people[$left],
                        'right'      => $people[$right],
                        'similarity' => $similarity,
                    ];
                }
            }
        }

        usort($duplicates, static function (array $first, array $second): int {
            $comparison = strcasecmp((string) $first['left']['nome'], (string) $second['left']['nome']);

            return $comparison !== 0
                ? $comparison
                : strcasecmp((string) $first['right']['nome'], (string) $second['right']['nome']);
        });

        return view('admin/tools/names_duplicate', [
            'title'      => 'Possíveis nomes duplicados',
            'duplicates' => $duplicates,
        ]);
    }

    public function names(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        return view('admin/tools/names', [
            'title'   => 'Padronizar nomes',
            'changes' => $this->nameChanges(),
        ]);
    }

    public function updateNames(): RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        if ($this->request->getPost('confirm') !== '1') {
            return redirect()->to(site_url('admin/tools/names'))
                ->with('error', 'Confirme a atualização dos nomes antes de continuar.');
        }

        $changes = $this->nameChanges();

        if ($changes === []) {
            return redirect()->to(site_url('admin/tools/names'))
                ->with('success', 'Todos os nomes já estão padronizados.');
        }

        $db = db_connect();
        $db->transStart();

        foreach ($changes as $change) {
            $db->table('individuo')->where('id', $change['id'])->update([
                'nome'       => $change['after'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/tools/names'))
                ->with('error', 'Não foi possível atualizar os nomes. Nenhuma alteração foi confirmada.');
        }

        return redirect()->to(site_url('admin/tools/names'))
            ->with('success', count($changes) . ' nomes foram padronizados com sucesso.');
    }

    /** @return array<int, array{id: int, before: string, after: string}> */
    private function nameChanges(): array
    {
        helper('nbr');
        $people = db_connect()->table('individuo')
            ->select('id, nome')
            ->orderBy('nome', 'ASC')
            ->get()
            ->getResultArray();
        $changes = [];

        foreach ($people as $person) {
            $before = trim((string) $person['nome']);
            $after = nbr_autor($before, 7);

            if ($after !== '' && $after !== $before) {
                $changes[] = [
                    'id'     => (int) $person['id'],
                    'before' => $before,
                    'after'  => $after,
                ];
            }
        }

        return $changes;
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name), 'UTF-8');
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $name = $ascii === false ? $name : $ascii;
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name) ?? $name;

        return trim(preg_replace('/\s+/', ' ', $name) ?? $name);
    }

    private function requireLogin(): ?RedirectResponse
    {
        if (session()->get('auth_logged_in') === true) {
            return null;
        }

        return redirect()->to(site_url('login'))
            ->with('error', 'Faça login para acessar as ferramentas de manutenção.');
    }
}
