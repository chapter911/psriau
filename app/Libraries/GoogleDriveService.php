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

        $content = file_get_contents($localFilePath);
        if ($content === false) {
            $this->lastError = 'Failed to read local file: ' . $localFilePath;
            log_message('error', 'GoogleDriveService - ' . $this->lastError);
            return null;
        }

        return $this->uploadFileContent($content, $fileName, $mimeType, $folderId);
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
        if (!$this->isReady()) {
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
                'includeItemsFromAllDrives' => true,
                'supportsAllDrives' => true,
            ]);

            $files = $results->getFiles();
            if (!empty($files)) {
                return $files[0]->getId();
            }

            return null;
        } catch (\Throwable $e) {
            log_message('error', 'GoogleDriveService - findFolder failed: ' . $e->getMessage());
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
        if (!$this->isReady()) {
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
                'supportsAllDrives' => true,
            ]);

            log_message('info', 'GoogleDriveService - Created folder: ' . $folderName . ' (ID: ' . $file->getId() . ')');
            return $file->getId();
        } catch (\Throwable $e) {
            log_message('error', 'GoogleDriveService - createFolder failed: ' . $e->getMessage());
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
        if (!$this->isReady()) {
            $this->lastError = 'Service not ready.';
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
            log_message('error', 'GoogleDriveService - Failed to create/access SIMAK folder');
            return null;
        }
        $currentFolderId = $simakFolderId;

        // Level 2: Nama Paket
        $paketFolderId = $this->findOrCreateFolder($sanitizedPaket, $currentFolderId);
        if ($paketFolderId === null) {
            log_message('error', 'GoogleDriveService - Failed to create/access paket folder: ' . $sanitizedPaket);
            return null;
        }
        $currentFolderId = $paketFolderId;

        // Level 3: Penyedia
        $penyediaFolderId = $this->findOrCreateFolder($sanitizedPenyedia, $currentFolderId);
        if ($penyediaFolderId === null) {
            log_message('error', 'GoogleDriveService - Failed to create/access penyedia folder: ' . $sanitizedPenyedia);
            return null;
        }
        $currentFolderId = $penyediaFolderId;

        // Level 4: Header Uraian
        $headerFolderId = $this->findOrCreateFolder($sanitizedHeader, $currentFolderId);
        if ($headerFolderId === null) {
            log_message('error', 'GoogleDriveService - Failed to create/access header folder: ' . $sanitizedHeader);
            return null;
        }
        $currentFolderId = $headerFolderId;

        // Level 5: Uraian
        $uraianFolderId = $this->findOrCreateFolder($sanitizedUraian, $currentFolderId);
        if ($uraianFolderId === null) {
            log_message('error', 'GoogleDriveService - Failed to create/access uraian folder: ' . $sanitizedUraian);
            return null;
        }

        log_message('info', 'GoogleDriveService - Built SIMAK folder path: ' .
            'SIMAK/' . $sanitizedPaket . '/' . $sanitizedPenyedia . '/' . $sanitizedHeader . '/' . $sanitizedUraian);

        return $uraianFolderId;
    }

    /**
     * Upload placeholder file to SIMAK folder structure.
     * This will create the folder structure and upload a placeholder file.
     *
     * @param string $rootFolderId Root folder ID
     * @param string $namaPaket Package name
     * @param string $penyedia Provider name
     * @param string $headerUraian Header/Section key
     * @param string $uraian Uraian text
     * @param string $placeholderContent Content of placeholder file
     * @return array ['folder_url' => string, 'file_url' => string]|null
     */
    public function uploadPlaceholderToSimakFolder(
        string $rootFolderId,
        string $namaPaket,
        string $penyedia,
        string $headerUraian,
        string $uraian,
        string $placeholderContent = ''
    ): ?array {
        if (!$this->isReady()) {
            $this->lastError = 'Service not ready.';
            log_message('error', 'GoogleDriveService - uploadPlaceholderToSimakFolder: Service not ready');
            return null;
        }

        // Load custom helper for folder/file sanitization
        helper('custom');

        // Sanitize folder names
        $sanitizedPaket = sanitizeFolderName($namaPaket);
        $sanitizedPenyedia = sanitizeFolderName($penyedia);
        $sanitizedHeader = sanitizeFolderName($headerUraian);
        $sanitizedUraian = sanitizeFolderName($uraian);

        // Build the folder hierarchy
        $currentFolderId = $rootFolderId;

        // Level 1: SIMAK
        $simakFolderId = $this->findOrCreateFolder('SIMAK', $currentFolderId);
        if ($simakFolderId === null) {
            log_message('error', 'GoogleDriveService - uploadPlaceholderToSimakFolder: Failed to create/access SIMAK folder');
            return null;
        }
        $currentFolderId = $simakFolderId;

        // Level 2: Nama Paket
        $paketFolderId = $this->findOrCreateFolder($sanitizedPaket, $currentFolderId);
        if ($paketFolderId === null) {
            log_message('error', 'GoogleDriveService - uploadPlaceholderToSimakFolder: Failed to create/access paket folder: ' . $sanitizedPaket);
            return null;
        }
        $currentFolderId = $paketFolderId;

        // Level 3: Penyedia
        $penyediaFolderId = $this->findOrCreateFolder($sanitizedPenyedia, $currentFolderId);
        if ($penyediaFolderId === null) {
            log_message('error', 'GoogleDriveService - uploadPlaceholderToSimakFolder: Failed to create/access penyedia folder: ' . $sanitizedPenyedia);
            return null;
        }
        $currentFolderId = $penyediaFolderId;

        // Level 4: Header Uraian
        $headerFolderId = $this->findOrCreateFolder($sanitizedHeader, $currentFolderId);
        if ($headerFolderId === null) {
            log_message('error', 'GoogleDriveService - uploadPlaceholderToSimakFolder: Failed to create/access header folder: ' . $sanitizedHeader);
            return null;
        }
        $currentFolderId = $headerFolderId;

        // Level 5: Uraian
        $uraianFolderId = $this->findOrCreateFolder($sanitizedUraian, $currentFolderId);
        if ($uraianFolderId === null) {
            log_message('error', 'GoogleDriveService - uploadPlaceholderToSimakFolder: Failed to create/access uraian folder: ' . $sanitizedUraian);
            return null;
        }

        // Build folder path string for reference
        $folderPathString = 'SIMAK/' . $sanitizedPaket . '/' . $sanitizedPenyedia . '/' . $sanitizedHeader . '/' . $sanitizedUraian;
        log_message('info', 'GoogleDriveService - uploadPlaceholderToSimakFolder: Created folder path: ' . $folderPathString);

        // Upload placeholder file to the final folder
        $placeholderFileName = 'UPLOAD DISINI - ' . $sanitizedUraian . '.txt';
        $placeholderContent = $placeholderContent ?: "UPLOAD FILE ANDA DI SINI\n\nFolder: " . $folderPathString . "\n\nPetunjuk:\n1. Upload file dokumen Anda ke folder ini\n2. Pastikan akses file: \"Anyone with the link\" -> \"Viewer\"\n3. Copy link file dan paste di sistem SIMAK\n\nDokumen yang diharapkan:\n- " . $uraian . "\n";

        try {
            $fileMetadata = new DriveFile([
                'name'    => $placeholderFileName,
                'parents' => [$uraianFolderId],
            ]);

            $file = $this->service->files->create($fileMetadata, [
                'data'              => $placeholderContent,
                'mimeType'          => 'text/plain',
                'uploadType'        => 'multipart',
                'fields'            => 'id, name, webViewLink, parents',
                'supportsAllDrives' => true,
            ]);

            log_message('info', 'GoogleDriveService - uploadPlaceholderToSimakFolder: Uploaded placeholder: ' . $placeholderFileName);

            // Get the folder URL
            $folderUrl = "https://drive.google.com/drive/folders/{$uraianFolderId}";

            return [
                'folder_url' => $folderUrl,
                'file_url' => $file->getWebViewLink(),
                'folder_path' => $folderPathString,
            ];
        } catch (\Throwable $e) {
            $this->lastError = 'uploadPlaceholderToSimakFolder failed: ' . $e->getMessage();
            log_message('error', 'GoogleDriveService - ' . $this->lastError);
            return null;
        }
    }

    /**
     * Upload a file to a specific folder in Google Drive.
     *
     * @param string $content Binary content of the file
     * @param string $fileName Original client file name
     * @param string $mimeType Mime type of the file
     * @param string $folderId Google Drive Parent Folder ID
     * @return string|null Web view link of the uploaded file, or null on failure
     */
    public function uploadFileContentToFolder(string $content, string $fileName, string $mimeType, string $folderId): ?string
    {
        if (!$this->isReady()) {
            $this->lastError = 'Service not ready.';
            log_message('error', 'GoogleDriveService - uploadFileContentToFolder aborted: ' . $this->lastError);
            return null;
        }

        try {
            $fileMetadata = new DriveFile([
                'name'    => $fileName,
                'parents' => [$folderId],
            ]);

            $file = $this->service->files->create($fileMetadata, [
                'data'              => $content,
                'mimeType'          => $mimeType,
                'uploadType'        => 'multipart',
                'fields'            => 'id, webViewLink',
                'supportsAllDrives' => true,
            ]);

            log_message('info', 'GoogleDriveService - Uploaded to folder: ' . $fileName . ' -> ' . $file->webViewLink);
            return $file->webViewLink;
        } catch (\Throwable $e) {
            $this->lastError = 'uploadFileContentToFolder failed: ' . $e->getMessage();
            log_message('error', 'GoogleDriveService - ' . $this->lastError);
            return null;
        }
    }

    /**
     * Download a file by its Google Drive ID using the Drive API
     *
     * This method uses the Google Drive API to download a file.
     * The file must be accessible to the Service Account.
     *
     * @param string $fileId Google Drive File ID
     * @return array|null ['content' => binary, 'mimeType' => string] or null on failure
     */
    public function downloadFileById(string $fileId): ?array
    {
        if (!$this->isReady()) {
            $this->lastError = 'Service not ready.';
            log_message('error', 'GoogleDriveService - downloadFileById aborted: ' . $this->lastError);
            return null;
        }

        try {
            // First, get file metadata to check permissions and get mime type
            $fileMetadata = $this->service->files->get($fileId, [
                'fields' => 'id, name, mimeType, permissions, shared',
                'supportsAllDrives' => true,
            ]);

            $mimeType = $fileMetadata->mimeType ?? 'application/octstream';
            $fileName = $fileMetadata->name ?? 'file';

            log_message('info', 'GoogleDriveService - downloadFileById: Found file "' . $fileName . '" with mimeType: ' . $mimeType);

            // Check if it's a Google Drive folder - can't download folders
            if ($mimeType === 'application/vnd.google-apps.folder') {
                $this->lastError = 'Cannot download a folder.';
                log_message('error', 'GoogleDriveService - downloadFileById: File is a folder');
                return null;
            }

            // Download the file content
            $response = $this->service->files->get($fileId, [
                'alt' => 'media',
                'supportsAllDrives' => true,
            ]);

            $content = $response->getBody()->getContents();

            if (empty($content)) {
                $this->lastError = 'Downloaded content is empty.';
                log_message('error', 'GoogleDriveService - downloadFileById: Empty content');
                return null;
            }

            log_message('info', 'GoogleDriveService - downloadFileById: Downloaded ' . strlen($content) . ' bytes');

            return [
                'content' => $content,
                'mimeType' => $mimeType,
            ];

        } catch (\Google\Service\Exception $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            if ($errorCode === 403) {
                $this->lastError = 'Permission denied: The Service Account does not have access to this file. Please ensure the file is shared with the Service Account email.';
                log_message('error', 'GoogleDriveService - downloadFileById - Permission denied: ' . $errorMessage);
            } elseif ($errorCode === 404) {
                $this->lastError = 'File not found.';
                log_message('error', 'GoogleDriveService - downloadFileById - File not found: ' . $errorMessage);
            } else {
                $this->lastError = 'API Error (' . $errorCode . '): ' . $errorMessage;
                log_message('error', 'GoogleDriveService - downloadFileById - API Error: ' . $errorMessage);
            }
            return null;
        } catch (\Throwable $e) {
            $this->lastError = 'Download failed: ' . $e->getMessage();
            log_message('error', 'GoogleDriveService - downloadFileById - Error: ' . $e->getMessage());
            return null;
        }
    }
}
