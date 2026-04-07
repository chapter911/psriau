<?php

namespace App\Controllers;

use App\Models\LoginHistoryModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function loginForm()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/admin');
        }

        return view('public/login');
    }

    public function login()
    {
        $usernameInput = trim((string) $this->request->getPost('username'));
        $context = $this->buildLoginContext($usernameInput);

        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            $this->logLoginAttempt(false, 'validation_failed', $context, null);
            return redirect()->back()->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $user = (new UserModel())->where('username', $usernameInput)->first();

        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password_hash'])) {
            $this->logLoginAttempt(false, 'invalid_credentials', $context, is_array($user) ? $user : null);
            return redirect()->back()->withInput()->with('error', 'Kredensial tidak valid.');
        }

        if ((int) ($user['is_active'] ?? 1) !== 1) {
            $this->logLoginAttempt(false, 'inactive_account', $context, $user);
            return redirect()->back()->withInput()->with('error', 'Akun Anda nonaktif. Hubungi administrator untuk aktivasi.');
        }

        session()->set([
            'isLoggedIn' => true,
            'userId'     => $user['id'],
            'fullName'   => $user['full_name'],
            'role'       => $user['role'] ?? 'editor',
        ]);

        $this->logLoginAttempt(true, null, $context, $user);

        return redirect()->to('/admin')->with('message', 'Selamat datang, ' . $user['full_name'] . '.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/')->with('message', 'Anda telah keluar dari sistem.');
    }

    private function buildLoginContext(string $usernameInput): array
    {
        return [
            'username_input' => $usernameInput,
            'ip_address' => (string) ($this->request->getIPAddress() ?? ''),
            'user_agent' => substr((string) $this->request->getUserAgent(), 0, 255),
            'http_method' => strtoupper((string) $this->request->getMethod()),
            'request_path' => trim((string) $this->request->getUri()->getPath(), '/'),
            'referer' => substr((string) ($this->request->getServer('HTTP_REFERER') ?? ''), 0, 255),
            'session_id' => substr((string) session_id(), 0, 128),
            'request_payload' => [
                'username' => $usernameInput,
                'password_provided' => trim((string) $this->request->getPost('password')) !== '',
            ],
            'server_context' => [
                'host' => (string) ($this->request->getServer('HTTP_HOST') ?? ''),
                'forwarded_for' => (string) ($this->request->getServer('HTTP_X_FORWARDED_FOR') ?? ''),
                'forwarded_proto' => (string) ($this->request->getServer('HTTP_X_FORWARDED_PROTO') ?? ''),
                'remote_port' => (string) ($this->request->getServer('REMOTE_PORT') ?? ''),
            ],
        ];
    }

    private function logLoginAttempt(bool $isSuccess, ?string $failureReason, array $context, ?array $user): void
    {
        try {
            $db = db_connect();
            if (! $db->tableExists('login_histories')) {
                return;
            }

            (new LoginHistoryModel())->insert([
                'attempted_at' => date('Y-m-d H:i:s'),
                'is_success' => $isSuccess ? 1 : 0,
                'failure_reason' => $failureReason,
                'username_input' => (string) ($context['username_input'] ?? ''),
                'user_id' => is_array($user) ? (int) ($user['id'] ?? 0) : null,
                'full_name' => is_array($user) ? (string) ($user['full_name'] ?? '') : null,
                'role' => is_array($user) ? (string) ($user['role'] ?? '') : null,
                'account_active' => is_array($user) ? (int) ($user['is_active'] ?? 0) : null,
                'ip_address' => (string) ($context['ip_address'] ?? ''),
                'user_agent' => (string) ($context['user_agent'] ?? ''),
                'http_method' => (string) ($context['http_method'] ?? ''),
                'request_path' => (string) ($context['request_path'] ?? ''),
                'referer' => (string) ($context['referer'] ?? ''),
                'session_id' => (string) ($context['session_id'] ?? ''),
                'request_payload_json' => json_encode($context['request_payload'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'server_context_json' => json_encode($context['server_context'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Logging must not break authentication flow.
        }
    }
}
