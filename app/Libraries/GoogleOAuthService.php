<?php

namespace App\Libraries;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

/**
 * Google OAuth Service for Drive API
 *
 * Handles OAuth 2.0 authentication flow for Google Drive API
 * without requiring Google Workspace / Service Account.
 */
class GoogleOAuthService
{
    private ?Client $client = null;
    private ?Drive $service = null;
    private ?string $lastError = null;
    private string $tokenPath;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private string $folderId;

    public function __construct()
    {
        $this->clientId = getenv('GOOGLE_CLIENT_ID') ?: '';
        $this->clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
        $this->folderId = getenv('GOOGLE_DRIVE_UPLOAD_FOLDER_ID') ?: '';
        $this->redirectUri = base_url('oauth/callback');
 $this->tokenPath = WRITEPATH . 'google_access_token.json';

        log_message('info', 'GoogleOAuthService - Initializing OAuth flow');
        log_message('info', 'GoogleOAuthService - Redirect URI: ' . $this->redirectUri);
    }

    /**
     * Get the OAuth authorization URL
     */
    public function getAuthUrl(): string
    {
        $this->initClient();

        return $this->client->createAuthUrl();
    }

    /**
     * Initialize the Google Client
     */
    private function initClient(): void
    {
        if ($this->client !== null) {
            return;
        }

        $this->client = new Client();
        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setRedirectUri($this->redirectUri);
        $this->client->addScope(Drive::DRIVE);
        $this->client->addScope(Drive::DRIVE_FILE);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
 $this->client->setRedirectUri($this->redirectUri);
    }

    /**
     * Handle the OAuth callback and exchange code for token
     */
    public function handleCallback(string $code): bool
    {
        $this->initClient();

        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($accessToken['error'])) {
                $this->lastError = $accessToken['error_description'] ?? $accessToken['error'];
                log_message('error', 'GoogleOAuthService - OAuth error: ' . $this->lastError);
                return false;
            }

            // Save the token
            $this->saveToken($accessToken);

            // Initialize service with the new token
            $this->client->setAccessToken($accessToken);
            $this->service = new Drive($this->client);

            log_message('info', 'GoogleOAuthService - OAuth authentication successful');
            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            log_message('error', 'GoogleOAuthService - Callback error: ' . $this->lastError);
            return false;
        }
    }

    /**
     * Save access token to file
     */
    private function saveToken(array $accessToken): void
    {
        $tokenDir = dirname($this->tokenPath);
        if (!is_dir($tokenDir)) {
            mkdir($tokenDir, 0755, true);
        }

        file_put_contents($this->tokenPath, json_encode($accessToken, JSON_PRETTY_PRINT));
        chmod($this->tokenPath, 0600);

        log_message('info', 'GoogleOAuthService - Token saved to: ' . $this->tokenPath);
    }

    /**
     * Load token from file and initialize service
     */
    public function loadToken(): bool
    {
        if (!file_exists($this->tokenPath)) {
            log_message('info', 'GoogleOAuthService - No token file found');
            return false;
        }

        $this->initClient();

        try {
            $accessToken = json_decode(file_get_contents($this->tokenPath), true);

            if (!$accessToken || !isset($accessToken['access_token'])) {
                log_message('error', 'GoogleOAuthService - Invalid token file');
                return false;
            }

            // Check if token is expired and try to refresh
            if ($this->client->isAccessTokenExpired()) {
                log_message('info', 'GoogleOAuthService - Token expired, attempting refresh...');

                if (isset($accessToken['refresh_token'])) {
                    $newToken = $this->client->refreshToken($accessToken['refresh_token']);
                    $this->saveToken($newToken);
                    $accessToken = $newToken;
                } else {
                    log_message('error', 'GoogleOAuthService - No refresh token available');
                    return false;
                }
            }

            $this->client->setAccessToken($accessToken);
            $this->service = new Drive($this->client);

            log_message('info', 'GoogleOAuthService - Token loaded successfully');
            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            log_message('error', 'GoogleOAuthService - Token load error: ' . $this->lastError);
            return false;
        }
    }

    /**
     * Check if authenticated (has valid token)
     */
    public function isAuthenticated(): bool
    {
        if ($this->service === null) {
            return $this->loadToken();
        }
        return true;
    }

    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Get the Drive service instance
     */
    public function getService(): ?Drive
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        return $this->service;
    }

    /**
     * Get the folder ID
     */
    public function getFolderId(): string
    {
        return $this->folderId;
    }

    /**
     * Upload a file to Google Drive
     */
    public function uploadFile(string $localFilePath, string $fileName, string $mimeType): ?string
    {
        if (!$this->isAuthenticated()) {
            $this->lastError = 'Not authenticated';
            return null;
        }

        $content = file_get_contents($localFilePath);
        if ($content === false) {
            $this->lastError = 'Failed to read local file: ' . $localFilePath;
            return null;
        }

        return $this->uploadFileContent($content, $fileName, $mimeType);
    }

    /**
     * Upload file content directly to Google Drive
     */
    public function uploadFileContent(string $content, string $fileName, string $mimeType): ?string
    {
        if (!$this->isAuthenticated()) {
            $this->lastError = 'Not authenticated';
            return null;
        }

        try {
            $fileMetadata = new DriveFile([
                'name'    => $fileName,
                'parents' => [$this->folderId],
            ]);

            $file = $this->service->files->create($fileMetadata, [
                'data'       => $content,
                'mimeType'   => $mimeType,
                'uploadType' => 'multipart',
                'fields'     => 'id, webViewLink',
            ]);

            log_message('info', 'GoogleOAuthService - Uploaded: ' . $fileName . ' -> ' . $file->webViewLink);
            return $file->webViewLink;
        } catch (\Throwable $e) {
            $this->lastError = 'Upload failed: ' . $e->getMessage();
            log_message('error', 'GoogleOAuthService - ' . $this->lastError);
            return null;
        }
    }

    /**
     * Revoke the current token (logout)
     */
    public function revokeToken(): bool
    {
        if (!file_exists($this->tokenPath)) {
            return true;
        }

        try {
            $this->initClient();
            $accessToken = json_decode(file_get_contents($this->tokenPath), true);

            if ($accessToken && isset($accessToken['access_token'])) {
                $this->client->revokeToken($accessToken['access_token']);
            }

            unlink($this->tokenPath);
            $this->service = null;
            $this->client = null;

            log_message('info', 'GoogleOAuthService - Token revoked');
            return true;
        } catch (\Throwable $e) {
            log_message('error', 'GoogleOAuthService - Revoke error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check token status
     */
    public function getTokenStatus(): array
    {
        $status = [
            'has_token' => file_exists($this->tokenPath),
            'is_authenticated' => $this->isAuthenticated(),
            'token_file' => $this->tokenPath,
        ];

        if ($status['has_token']) {
            $token = json_decode(file_get_contents($this->tokenPath), true);
            $status['has_refresh_token'] = isset($token['refresh_token']);
            $status['token_expires_at'] = $token['expiry'] ?? null;
        }

        return $status;
    }
}
