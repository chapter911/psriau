<?php

namespace App\Libraries;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveService
{
    private ?Client $client = null;
    private ?Drive $service = null;

    public function __construct()
    {
        $jsonPath = getenv('GOOGLE_SERVICE_ACCOUNT_JSON_PATH');
        if (empty($jsonPath)) {
            // Default fallback to project root
            $jsonPath = ROOTPATH . 'google-service-account.json';
        } else {
            $jsonPath = trim($jsonPath, " \t\n\r\0\x0B'\"");
            // If it is a relative path, resolve it relative to the project root
            if ($jsonPath !== '' && $jsonPath[0] !== '/' && $jsonPath[0] !== '\\' && substr($jsonPath, 1, 2) !== ':\\') {
                $jsonPath = ROOTPATH . $jsonPath;
            }
        }

        if (!file_exists($jsonPath)) {
            log_message('error', 'GoogleDriveService - Credentials file not found at: ' . $jsonPath);
            return;
        }

        try {
            $this->client = new Client();
            $this->client->setAuthConfig($jsonPath);
            $this->client->addScope(Drive::DRIVE);
            $this->service = new Drive($this->client);
        } catch (\Throwable $e) {
            log_message('error', 'GoogleDriveService - Failed to initialize: ' . $e->getMessage());
        }
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
     * @param string $localFilePath Absolute path of the file on local disk
     * @param string $fileName Original client file name
     * @param string $mimeType Mime type of the file
     * @param string $folderId Google Drive Parent Folder ID
     * @return string|null Web view link of the uploaded file, or null on failure
     */
    public function uploadFile(string $localFilePath, string $fileName, string $mimeType, string $folderId): ?string
    {
        if (!$this->isReady()) {
            log_message('error', 'GoogleDriveService - uploadFile aborted: Service not ready.');
            return null;
        }

        try {
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [$folderId]
            ]);

            $content = file_get_contents($localFilePath);
            if ($content === false) {
                log_message('error', 'GoogleDriveService - Failed to read local file: ' . $localFilePath);
                return null;
            }

            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]);

            return $file->webViewLink;
        } catch (\Throwable $e) {
            log_message('error', 'GoogleDriveService - uploadFile failed: ' . $e->getMessage());
            return null;
        }
    }
}
