<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PersonModel;
use CodeIgniter\HTTP\RedirectResponse;

class Person extends BaseController
{
    public function index(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $query = trim((string) $this->request->getGet('q'));
        $field = (string) $this->request->getGet('field');

        $searchableFields = [
            'all'       => 'Todos os campos',
            'name'      => 'Nome',
            'email'     => 'E-mail',
            'lattes_id' => 'ID Lattes',
            'cracha'    => 'Crachá',
        ];

        if (! array_key_exists($field, $searchableFields)) {
            $field = 'all';
        }

        $model      = $this->applySearch(new PersonModel(), $query, $field);
        $countModel = $this->applySearch(new PersonModel(), $query, $field);
        $persons    = $model->orderBy('name', 'ASC')->paginate(25, 'persons');
        $pager      = $model->pager;
        $pager->only(['q', 'field']);

        return view('admin/person/index', [
            'title'            => 'Administrar pessoas',
            'persons'          => $persons,
            'pager'            => $pager,
            'total'            => $countModel->countAllResults(),
            'query'            => $query,
            'field'            => $field,
            'searchableFields' => $searchableFields,
        ]);
    }

    private function applySearch(PersonModel $model, string $query, string $field): PersonModel
    {
        if ($query === '') {
            return $model;
        }

        if ($field !== 'all') {
            return $model->like($field, $query);
        }

        return $model
            ->groupStart()
            ->like('name', $query)
            ->orLike('email', $query)
            ->orLike('lattes_id', $query)
            ->orLike('cracha', $query)
            ->groupEnd();
    }

    public function inport(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        return view('admin/person/inport', [
            'title' => 'Importar pessoas',
        ]);
    }

    public function processInport(): RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $rawData = trim((string) $this->request->getPost('data'));

        if ($rawData === '') {
            return redirect()->to(site_url('admin/person/inport'))
                ->with('error', 'Cole ao menos uma linha para importar.');
        }

        $model    = new PersonModel();
        $imported = 0;
        $errors   = [];
        $lines    = preg_split('/\R/u', $rawData) ?: [];

        foreach ($lines as $index => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $line      = str_ireplace('/tab', "\t", $line);
            $delimiter = str_contains($line, "\t") ? "\t" : (str_contains($line, ';') ? ';' : ',');
            $columns   = array_map('trim', str_getcsv($line, $delimiter));

            if ($index === 0 && $this->isHeader($columns)) {
                continue;
            }

            if (count($columns) < 4) {
                $errors[] = 'Linha ' . ($index + 1) . ': informe Nome, IDlattes, email e crachá.';
                continue;
            }

            [$name, $lattesId, $email, $cracha] = array_slice($columns, 0, 4);

            if ($model->insert([
                'name'      => $name,
                'lattes_id' => $lattesId !== '' ? $lattesId : null,
                'email'     => $email !== '' ? $email : null,
                'cracha'    => $cracha !== '' ? $cracha : null,
            ]) === false) {
                $errors[] = 'Linha ' . ($index + 1) . ': ' . implode(' ', $model->errors());
                continue;
            }

            $imported++;
        }

        return redirect()->to(site_url('admin/person/inport'))->with('import_result', [
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    private function requireLogin(): ?RedirectResponse
    {
        if (session()->get('auth_logged_in') === true) {
            return null;
        }

        return redirect()->to(site_url('login'))
            ->with('error', 'Faça login para acessar a administração de pessoas.');
    }

    private function isHeader(array $columns): bool
    {
        $firstColumn = mb_strtolower($columns[0] ?? '');
        $secondColumn = mb_strtolower($columns[1] ?? '');

        return $firstColumn === 'nome' && str_contains($secondColumn, 'lattes');
    }
}
