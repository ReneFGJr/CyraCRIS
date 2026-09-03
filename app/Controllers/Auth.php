<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

class Auth extends BaseController
{
    private const SIGNIN_ENDPOINT = 'https://cip.brapci.inf.br/api/socials/signin';

    public function login(): string|RedirectResponse
    {
        if (session()->get('auth_logged_in') === true) {
            return redirect()->to(site_url('/'));
        }

        return view('auth/login', ['title' => 'Entrar']);
    }

    public function authenticate(): RedirectResponse
    {
        $credentials = [
            'user' => trim((string) $this->request->getPost('user')),
            'pwd'  => (string) $this->request->getPost('pwd'),
        ];

        if (! $this->validateData($credentials, ['user' => 'required', 'pwd' => 'required'])) {
            return $this->loginError('Informe o usuário e a senha.', $credentials['user']);
        }

        try {
            $response = service('curlrequest')->post(self::SIGNIN_ENDPOINT, [
                'form_params' => $credentials,
                'headers'     => ['Accept' => 'application/json'],
                'http_errors' => false,
                'timeout'     => 15,
                'verify'      => env('auth.caBundle', true),
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Falha ao acessar o serviço de autenticação: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->loginError(
                'Não foi possível acessar o serviço de autenticação. Tente novamente.',
                $credentials['user'],
            );
        }

        $data = json_decode((string) $response->getBody(), true);

        if (! is_array($data)) {
            return $this->loginError('O serviço de autenticação retornou uma resposta inválida.', $credentials['user']);
        }

        $apiStatus = (int) ($data['status'] ?? $response->getStatusCode());
        $givename  = $this->findGivename($data);

        if ($apiStatus < 200 || $apiStatus >= 300 || $givename === null) {
            $message = is_string($data['message'] ?? null) ? $data['message'] : 'Usuário ou senha inválidos.';

            return $this->loginError($message, $credentials['user']);
        }

        $session = session();
        $session->regenerate();
        $session->set([
            'auth_logged_in' => true,
            'auth_givename'  => $givename,
            'auth_user'      => $credentials['user'],
            'auth_data'      => $data,
            'auth_login_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('/'))->with('success', 'Login realizado com sucesso.');
    }

    public function logout(): RedirectResponse
    {
        $session = session();
        $session->remove(['auth_logged_in', 'auth_givename', 'auth_user', 'auth_data', 'auth_login_at']);
        $session->regenerate();

        return redirect()->to(site_url('/'))->with('success', 'Você saiu da sua conta.');
    }

    public function profile(): string|RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))->with('error', 'Faça login para acessar seu perfil.');
        }

        $data = session()->get('auth_data');

        return view('auth/profile', [
            'title'       => 'Meu perfil',
            'givename'    => (string) session()->get('auth_givename'),
            'username'    => (string) session()->get('auth_user'),
            'loginAt'     => (string) session()->get('auth_login_at'),
            'profileData' => $this->profileData(is_array($data) ? $data : []),
        ]);
    }

    private function loginError(string $message, string $user): RedirectResponse
    {
        return redirect()->to(site_url('login'))->with('error', $message)->with('login_user', $user);
    }

    private function findGivename(array $data): ?string
    {
        foreach (['givename', 'given_name', 'givenName'] as $key) {
            if (isset($data[$key]) && is_scalar($data[$key])) {
                $givename = trim((string) $data[$key]);

                if ($givename !== '') {
                    return $givename;
                }
            }
        }

        foreach (['data', 'user', 'profile'] as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                $givename = $this->findGivename($data[$key]);

                if ($givename !== null) {
                    return $givename;
                }
            }
        }

        return null;
    }

    private function profileData(array $data, string $prefix = ''): array
    {
        $profile = [];
        $sensitiveFields = ['pwd', 'password', 'token', 'access_token', 'refresh_token', 'secret'];

        foreach ($data as $field => $value) {
            $field = (string) $field;

            if (in_array(strtolower($field), $sensitiveFields, true)) {
                continue;
            }

            $label = $prefix === '' ? $field : $prefix . ' / ' . $field;

            if (is_array($value)) {
                $profile += $this->profileData($value, $label);
            } elseif (is_bool($value)) {
                $profile[$label] = $value ? 'Sim' : 'Não';
            } elseif ($value !== null && is_scalar($value)) {
                $profile[$label] = (string) $value;
            }
        }

        return $profile;
    }
}
