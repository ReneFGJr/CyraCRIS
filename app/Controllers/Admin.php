<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Admin extends BaseController
{
    public function index(): string|RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Faça login para acessar a área administrativa.');
        }

        return view('admin/index', [
            'title'    => 'Administração',
            'givename' => (string) session()->get('auth_givename'),
            'username' => (string) session()->get('auth_user'),
        ]);
    }
}
