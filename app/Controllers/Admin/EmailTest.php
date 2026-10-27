<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class EmailTest extends BaseController
{
    private function emailConfig(): \Config\Email
    {
        $config = clone config('Email');
        $config->protocol = strtolower(trim((string) env('EMAIL_TYPE', $config->protocol)));
        $config->SMTPHost = trim((string) env('EMAIL_SMTP', $config->SMTPHost));
        $config->SMTPPort = (int) env('EMAIL_SMTP_PORT', $config->SMTPPort);
        $config->fromEmail = trim((string) env('EMAIL_FROM', $config->fromEmail));
        $config->SMTPUser = trim((string) env('EMAIL_USER_AUTH', $config->SMTPUser));
        $config->SMTPPass = (string) env('EMAIL_PASSWORD', $config->SMTPPass);

        return $config;
    }

    public function index(): string|RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))->with('error', 'Faça login para testar o envio de e-mail.');
        }

        $config = $this->emailConfig();

        return view('admin/email_test', [
            'title' => 'Teste de e-mail',
            'protocol' => $config->protocol,
            'host' => $config->SMTPHost,
            'port' => $config->SMTPPort,
            'fromEmail' => $config->fromEmail,
        ]);
    }

    public function send(): RedirectResponse
    {
        if (session()->get('auth_logged_in') !== true) {
            return redirect()->to(site_url('login'))->with('error', 'Faça login para testar o envio de e-mail.');
        }

        $url = site_url('admin/email_test');
        $recipient = trim((string) $this->request->getPost('recipient'));
        if (! $this->validateData(['recipient' => $recipient], ['recipient' => 'required|valid_email|max_length[254]'])) {
            return redirect()->to($url)->withInput()->with('error', 'Informe um e-mail de destino válido.');
        }

        $config = $this->emailConfig();
        if (! in_array($config->protocol, ['mail', 'sendmail', 'smtp'], true)
            || ! filter_var($config->fromEmail, FILTER_VALIDATE_EMAIL)
            || ($config->protocol === 'smtp' && ($config->SMTPHost === '' || $config->SMTPPort < 1 || $config->SMTPPort > 65535))) {
            return redirect()->to($url)->withInput()->with('error', 'Verifique EMAIL_TYPE, EMAIL_FROM e, para SMTP, EMAIL_SMTP e EMAIL_SMTP_PORT no .env.');
        }

        try {
            $email = \Config\Services::email($config, false);
            $email->setFrom($config->fromEmail, $config->fromName);
            $email->setTo($recipient);
            $email->setMailType('text');
            $email->setSubject('CyraCRIS - Teste de e-mail');
            $email->setMessage('Este é um e-mail de teste enviado pela área administrativa do CyraCRIS usando a configuração do .env.');
            $sent = $email->send();
        } catch (\Throwable $exception) {
            $sent = false;
        }

        if (! $sent) {
            return redirect()->to($url)->withInput()->with('error', 'Não foi possível enviar o e-mail. Verifique o protocolo, as credenciais e a disponibilidade do serviço configurado no .env.');
        }

        return redirect()->to($url)->with('success', 'E-mail de teste enviado para ' . $recipient . '. Confira a caixa de entrada e o spam.');
    }
}
