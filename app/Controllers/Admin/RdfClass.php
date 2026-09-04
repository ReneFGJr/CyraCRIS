<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RdfClassModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class RdfClass extends BaseController
{
    public function index(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $query = trim((string) $this->request->getGet('q'));
        $type = strtoupper(trim((string) $this->request->getGet('type')));

        if (! in_array($type, ['C', 'P'], true)) {
            $type = 'C';
        }

        $model = new RdfClassModel();
        $model->where('c_type', $type);

        if ($query !== '') {
            $model->groupStart()
                ->like('c_class', $query)
                ->orLike('c_description', $query)
                ->orLike('c_url', $query)
                ->groupEnd();
        }

        $classes = $model->orderBy('c_class', 'ASC')->paginate(25, 'rdf_classes');
        $pager = $model->pager;
        $pager->only(['q', 'type']);

        $totals = [
            'C' => db_connect()->table('rdf_class')->where('c_type', 'C')->countAllResults(),
            'P' => db_connect()->table('rdf_class')->where('c_type', 'P')->countAllResults(),
        ];

        return view('admin/rdf/class/index', [
            'title'   => 'Classes RDF',
            'classes' => $classes,
            'pager'   => $pager,
            'query'   => $query,
            'type'    => $type,
            'totals'  => $totals,
        ]);
    }

    public function new(): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        return view('admin/rdf/class/form', [
            'title'    => 'Nova classe RDF',
            'rdfClass' => null,
            'prefixes' => $this->prefixes(),
        ]);
    }

    public function create(): RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $model = new RdfClassModel();
        $data = $this->formData();

        if (! $this->prefixExists($data['c_prefix'])) {
            return redirect()->back()->withInput()->with('errors', [
                'Selecione um prefixo RDF válido.',
            ]);
        }

        if ($this->classExists($data['c_class'], $data['c_prefix'])) {
            return redirect()->back()->withInput()->with('errors', [
                'Já existe uma classe com este nome e prefixo.',
            ]);
        }

        if ($model->insert($data) === false) {
            return redirect()->back()->withInput()->with('errors', array_values($model->errors()));
        }

        return redirect()->to(site_url('admin/rdf/class'))
            ->with('success', 'Classe RDF criada com sucesso.');
    }

    public function edit(int $id): string|RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        return view('admin/rdf/class/form', [
            'title'    => 'Editar classe RDF',
            'rdfClass' => $this->findOrFail($id),
            'prefixes' => $this->prefixes(),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $this->findOrFail($id);
        $model = new RdfClassModel();
        $data = $this->formData();

        if (! $this->prefixExists($data['c_prefix'])) {
            return redirect()->back()->withInput()->with('errors', [
                'Selecione um prefixo RDF válido.',
            ]);
        }

        if ($this->classExists($data['c_class'], $data['c_prefix'], $id)) {
            return redirect()->back()->withInput()->with('errors', [
                'Já existe uma classe com este nome e prefixo.',
            ]);
        }

        if ($model->update($id, $data) === false) {
            return redirect()->back()->withInput()->with('errors', array_values($model->errors()));
        }

        return redirect()->to(site_url('admin/rdf/class'))
            ->with('success', 'Classe RDF atualizada com sucesso.');
    }

    public function delete(int $id): RedirectResponse
    {
        if (($redirect = $this->requireLogin()) !== null) {
            return $redirect;
        }

        $this->findOrFail($id);
        (new RdfClassModel())->delete($id);

        return redirect()->to(site_url('admin/rdf/class'))
            ->with('success', 'Classe RDF excluída com sucesso.');
    }

    /** @return array<string, int|string> */
    private function formData(): array
    {
        return [
            'c_class'       => trim((string) $this->request->getPost('c_class')),
            'c_equivalent'  => (int) $this->request->getPost('c_equivalent'),
            'c_prefix'      => (int) $this->request->getPost('c_prefix'),
            'c_class_main'  => (int) $this->request->getPost('c_class_main'),
            'c_type'        => trim((string) $this->request->getPost('c_type')),
            'c_description' => trim((string) $this->request->getPost('c_description')),
            'c_url'         => trim((string) $this->request->getPost('c_url')),
            'c_url_update'  => trim((string) $this->request->getPost('c_url_update')),
        ];
    }

    /** @return array<string, mixed> */
    private function findOrFail(int $id): array
    {
        $rdfClass = (new RdfClassModel())->find($id);

        if ($rdfClass === null) {
            throw PageNotFoundException::forPageNotFound('Classe RDF não encontrada.');
        }

        return $rdfClass;
    }

    private function classExists(string $class, int $prefix, ?int $ignoreId = null): bool
    {
        $builder = db_connect()->table('rdf_class')
            ->where('c_class', $class)
            ->where('c_prefix', $prefix);

        if ($ignoreId !== null) {
            $builder->where('id_c !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    /** @return array<int, array<string, mixed>> */
    private function prefixes(): array
    {
        return db_connect()->table('rdf_prefix')
            ->orderBy('prefix_ativo', 'DESC')
            ->orderBy('prefix_ref', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function prefixExists(int $id): bool
    {
        return $id > 0 && db_connect()->table('rdf_prefix')
            ->where('id_prefix', $id)
            ->countAllResults() > 0;
    }

    private function requireLogin(): ?RedirectResponse
    {
        if (session()->get('auth_logged_in') === true) {
            return null;
        }

        return redirect()->to(site_url('login'))
            ->with('error', 'Faça login para administrar as classes RDF.');
    }
}
