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

            $this->client->setAccessToken($accessToken);

            // Check if token is expired and try to refresh
            if ($this->client->isAccessTokenExpired()) {
                log_message('info', 'GoogleOAuthService - Token expired, attempting refresh...');

                if (isset($accessToken['refresh_token'])) {
                    $newToken = $this->client->refreshToken($accessToken['refresh_token']);
                    $this->saveToken($newToken);
                    $accessToken = $newToken;
                    $this->client->setAccessToken($accessToken);
                } else {
                    log_message('error', 'GoogleOAuthService - No refresh token available');
                    return false;
                }
            }
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
     * Find a folder by name under a specific parent folder.
     *
     * @param string $folderName Name of the folder to find
     * @param string $parentId Parent folder ID (use 'root' for root)
     * @return string|null Folder ID if found, null otherwise
     */
    public function findFolder(string $folderName, string $parentId): ?string
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            $query = "name='" . str_replace("'", "\\'", $folderName) . "' and mimeType='application/vnd.google-apps.folder' and trashed=false";

            if ($parentId !== 'root') {
                $query .= " and '" . $parentId . "' in parents";
            }

            $results = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
            ]);

            $files = $results->getFiles();
            if (!empty($files)) {
                return $files[0]->getId();
            }

            return null;
        } catch (\Throwable $e) {
            log_message('error', 'GoogleOAuthService - findFolder failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new folder under a specific parent folder.
     *
     * @param string $folderName Name of the folder to create
     * @param string $parentId Parent folder ID (use 'root' for root)
     * @return string|null Folder ID if created successfully, null otherwise
     */
    public function createFolder(string $folderName, string $parentId): ?string
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            $fileMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentId],
            ]);

            $file = $this->service->files->create($fileMetadata, [
                'fields' => 'id',
            ]);

            log_message('info', 'GoogleOAuthService - Created folder: ' . $folderName . ' (ID: ' . $file->getId() . ')');
            return $file->getId();
        } catch (\Throwable $e) {
            log_message('error', 'GoogleOAuthService - createFolder failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find or create a folder by name under a specific parent folder.
     *
     * @param string $folderName Name of the folder to find or create
     * @param string $parentId Parent folder ID (use 'root' for root)
     * @return string|null Folder ID if found or created, null on error
     */
    public function findOrCreateFolder(string $folderName, string $parentId): ?string
    {
        $existingId = $this->findFolder($folderName, $parentId);
        if ($existingId !== null) {
            return $existingId;
        }

        return $this->createFolder($folderName, $parentId);
    }

    /**
     * Build structured folder path for SIMAK uploads.
     * Creates and returns the final folder ID for the uraian item.
     *
     * Folder structure:
     * [Root] / SIMAK / [Nama Paket] / [Penyedia] / [Header Uraian] / [Uraian]
     *
     * @param string $rootFolderId Root folder ID
     * @param string $namaPaket Package name
     * @param string $penyedia Provider name
     * @param string $headerUraian Header description (e.g., "A", "B", "1")
     * @param string $uraian Description text
     * @return string|null Final folder ID for the uraian folder, null on error
     */
    public function buildSimakFolderPath(
        string $rootFolderId,
        string $namaPaket,
        string $penyedia,
        string $headerUraian,
        string $uraian
    ): ?string {
        if (!$this->isAuthenticated()) {
            $this->lastError = 'Not authenticated';
            return null;
        }

        // Load custom helper for folder/file sanitization
        helper('custom');

        // Sanitize folder names using helper function
        $sanitizedPaket = sanitizeFolderName($namaPaket);
        $sanitizedPenyedia = sanitizeFolderName($penyedia);
        $sanitizedHeader = sanitizeFolderName($headerUraian);
        $sanitizedUraian = sanitizeFolderName($uraian);

        // Build the folder hierarchy
        $currentFolderId = $rootFolderId;

        // Level 1: SIMAK (prefix folder)
        $simakFolderId = $this->findOrCreateFolder('SIMAK', $currentFolderId);
        if ($simakFolderId === null) {
            log_message('error', 'GoogleOAuthService - Failed to create/access SIMAK folder');
            return null;
        }
        $currentFolderId = $simakFolderId;

        // Level 2: Nama Paket
        $paketFolderId = $this->findOrCreateFolder($sanitizedPaket, $currentFolderId);
        if ($paketFolderId === null) {
            log_message('error', 'GoogleOAuthService - Failed to create/access paket folder: ' . $sanitizedPaket);
            return null;
        }
        $currentFolderId = $paketFolderId;

        // Level 3: Penyedia
        $penyediaFolderId = $this->findOrCreateFolder($sanitizedPenyedia, $currentFolderId);
        if ($penyediaFolderId === null) {
            log_message('error', 'GoogleOAuthService - Failed to create/access penyedia folder: ' . $sanitizedPenyedia);
            return null;
        }
        $currentFolderId = $penyediaFolderId;

        // Level 4: Header Uraian
        $headerFolderId = $this->findOrCreateFolder($sanitizedHeader, $currentFolderId);
        if ($headerFolderId === null) {
            log_message('error', 'GoogleOAuthService - Failed to create/access header folder: ' . $sanitizedHeader);
            return null;
        }
        $currentFolderId = $headerFolderId;

        // Level 5: Uraian
        $uraianFolderId = $this->findOrCreateFolder($sanitizedUraian, $currentFolderId);
        if ($uraianFolderId === null) {
            log_message('error', 'GoogleOAuthService - Failed to create/access uraian folder: ' . $sanitizedUraian);
            return null;
        }

        log_message('info', 'GoogleOAuthService - Built SIMAK folder path: ' .
            'SIMAK/' . $sanitizedPaket . '/' . $sanitizedPenyedia . '/' . $sanitizedHeader . '/' . $sanitizedUraian);

        return $uraianFolderId;
    }

    /**
     * Upload file content to a specific folder.
     *
     * @param string $content Binary content of the file
     * @param string $fileName Original client file name
     * @param string $mimeType Mime type of the file
     * @param string $folderId Target folder ID
     * @return string|null Web view link of the uploaded file, or null on failure
     */
    public function uploadFileContentToFolder(string $content, string $fileName, string $mimeType, string $folderId): ?string
    {
        if (!$this->isAuthenticated()) {
            $this->lastError = 'Not authenticated';
            return null;
        }

        try {
            $fileMetadata = new DriveFile([
                'name'    => $fileName,
                'parents' => [$folderId],
            ]);

            $file = $this->service->files->create($fileMetadata, [
                'data'       => $content,
                'mimeType'   => $mimeType,
                'uploadType' => 'multipart',
                'fields'     => 'id, webViewLink',
            ]);

            log_message('info', 'GoogleOAuthService - Uploaded to folder: ' . $fileName . ' -> ' . $file->webViewLink);
            return $file->webViewLink;
        } catch (\Throwable $e) {
            $this->lastError = 'Upload to folder failed: ' . $e->getMessage();
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
