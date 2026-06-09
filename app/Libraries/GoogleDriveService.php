<?php

namespace App\Libraries;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveService
{
    private ?Client $client = null;
    private ?Drive $service = null;
    private ?string $lastError = null;

    public function __construct()
    {
        $jsonPath = getenv('GOOGLE_SERVICE_ACCOUNT_JSON_PATH');
        if (empty($jsonPath)) {
            // Default fallback to project root
            $jsonPath = ROOTPATH . 'google-service-account.json';
        } else {
            $jsonPath = trim($jsonPath, " \t\n\r\0\x0B'\";");
            // If it is a relative path, resolve it relative to the project root
            if ($jsonPath !== '' && $jsonPath[0] !== '/' && $jsonPath[0] !== '\\' && substr($jsonPath, 1, 2) !== ':\\') {
                $jsonPath = ROOTPATH . $jsonPath;
            }
        }

        log_message('info', 'GoogleDriveService - Resolved credential path: ' . $jsonPath);

        if (!file_exists($jsonPath)) {
            $this->lastError = 'Credentials file not found at: ' . $jsonPath;
            log_message('error', 'GoogleDriveService - ' . $this->lastError);
            log_message('error', 'GoogleDriveService - ROOTPATH is: ' . ROOTPATH);
            return;
        }

        try {
            $this->client = new Client();
            $this->client->setAuthConfig($jsonPath);
            // Request both DRIVE_FILE (for Shared Drive uploads) and DRIVE scopes
            $this->client->addScope(Drive::DRIVE);
            $this->client->addScope(Drive::DRIVE_FILE);
            $this->service = new Drive($this->client);
            log_message('info', 'GoogleDriveService - Initialized successfully.');
        } catch (\Throwable $e) {
            $this->lastError = 'Failed to initialize: ' . $e->getMessage();
            log_message('error', 'GoogleDriveService - ' . $this->lastError);
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Check if the Google Drive API service is initialized successfully.
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->service !== null;
    }

    /**
     * Upload a local file to Google Drive.
     *
     * Supports both regular shared folders (My Drive) and Shared Drives (Team Drive).
     * supportsAllDrives=true is required for Shared Drive uploads.
     *
     * @param string $localFilePath Absolute path of the file on local disk
     * @param string $fileName Original client file name
     * @param string $mimeType Mime type of the file
     * @param string $folderId Google Drive Parent Folder ID
     * @return string|null Web view link of the uploaded file, or null on failure
     */
    public function uploadFile(string $localFilePath, string $fileName, string $mimeType, string $folderId): ?string
    {
        if (!$this->isReady()) {
            $this->lastError = 'Service not ready.';
            log_message('error', 'GoogleDriveService - uploadFile aborted: ' . $this->lastError);
            return null;
        }

        try {
            $fileMetadata = new DriveFile([
                'name'    => $fileName,
                'parents' => [$folderId],
            ]);

            $content = file_get_contents($localFilePath);
            if ($content === false) {
                $this->lastError = 'Failed to read local file: ' . $localFilePath;
                log_message('error', 'GoogleDriveService - ' . $this->lastError);
                return null;
            }

            // supportsAllDrives=true is required to upload into Shared Drives.
            // It also works fine for regular My Drive folders that are shared to
            // the Service Account.
            $file = $this->service->files->create($fileMetadata, [
                'data'              => $content,
                'mimeType'          => $mimeType,
                'uploadType'        => 'multipart',
                'fields'            => 'id, webViewLink',
                'supportsAllDrives' => true,
            ]);

            log_message('info', 'GoogleDriveService - Uploaded: ' . $fileName . ' -> ' . $file->webViewLink);
            return $file->webViewLink;
        } catch (\Throwable $e) {
            $this->lastError = 'uploadFile failed: ' . $e->getMessage();
            log_message('error', 'GoogleDriveService - ' . $this->lastError);
            return null;
        }
    }
}
