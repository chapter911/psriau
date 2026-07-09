<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserTokenModel;
use App\Models\UserModel;
use App\Services\ApiAuth;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Retrieve the Authorization header
        $authHeader = $request->getServer('HTTP_AUTHORIZATION') ?? $request->getHeaderLine('Authorization');

        if (empty($authHeader) || ! preg_match('/^Bearer\s+(.+)$/i', trim($authHeader), $matches)) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Token otentikasi tidak disediakan atau format tidak valid.'
                ]);
        }

        $token = $matches[1];
        $tokenHash = hash('sha256', $token);

        // 2. Validate token from database
        $tokenModel = new UserTokenModel();
        $tokenRecord = $tokenModel->where('token_hash', $tokenHash)->first();

        if (! $tokenRecord) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Token otentikasi tidak valid.'
                ]);
        }

        // 3. Check expiration
        if (! empty($tokenRecord['expires_at']) && strtotime($tokenRecord['expires_at']) < time()) {
            // Proactively delete expired token
            $tokenModel->delete($tokenRecord['id']);

            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Token otentikasi telah kedaluwarsa.'
                ]);
        }

        // 4. Retrieve and validate the user
        $userModel = new UserModel();
        $user = $userModel->find($tokenRecord['user_id']);

        if (! $user) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Pengguna tidak ditemukan.'
                ]);
        }

        if ((int) ($user['is_active'] ?? 1) !== 1) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Akun Anda nonaktif. Silakan hubungi administrator.'
                ]);
        }

        if ((int) ($user['akses_mobile'] ?? 1) !== 1) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Akun Anda tidak memiliki akses ke platform Mobile.'
                ]);
        }

        // 5. Store user and token in the context service
        ApiAuth::setUser($user);
        ApiAuth::setToken($token);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
