<?php

namespace App\Controllers;

use App\Libraries\GoogleOAuthService;

/**
 * OAuth Controller
 *
 * Handles Google OAuth 2.0 callback and authentication flow.
 */
class Oauth extends BaseController
{
    private GoogleOAuthService $oauthService;

    public function __construct()
    {
        $this->oauthService = new GoogleOAuthService();
    }

    /**
     * Redirect user to Google OAuth consent page
     */
    public function connect(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        $authUrl = $this->oauthService->getAuthUrl();

        log_message('info', 'OAuth Controller - Redirecting to: ' . $authUrl);

        return redirect()->to($authUrl);
    }

    /**
     * Handle OAuth callback from Google
     */
    public function callback(): string
    {
        $code = $this->request->getGet('code');
        $error = $this->request->getGet('error');

        // Check for error from Google
        if ($error) {
            log_message('error', 'OAuth Controller - Google returned error: ' . $error);
            return $this->fail('OAuth error: ' . $error);
        }

        // Check for authorization code
        if (empty($code)) {
            log_message('error', 'OAuth Controller - No authorization code received');
            return $this->fail('No authorization code received');
        }

        // Process the callback
        if ($this->oauthService->handleCallback($code)) {
            log_message('info', 'OAuth Controller - Authentication successful');

            // Return success response - redirect to success page
            header('Location: ' . base_url('oauth/success'));
            exit;
        } else {
            log_message('error', 'OAuth Controller - Authentication failed: ' . $this->oauthService->getLastError());
            return $this->fail('Authentication failed: ' . $this->oauthService->getLastError());
        }
    }

    /**
     * OAuth success page
     */
    public function success(): string
    {
        return view('oauth/success', [
            'message' => 'Google Drive authentication successful! You can now upload files.'
        ]);
    }

    /**
     * Check authentication status
     */
    public function status(): \CodeIgniter\HTTP\Response
    {
        $status = $this->oauthService->getTokenStatus();

        return $this->response->setJSON([
            'success' => true,
            'data' => $status
        ]);
    }

    /**
     * Logout / revoke token
     */
    public function disconnect(): \CodeIgniter\HTTP\Response
    {
        $this->oauthService->revokeToken();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Google Drive disconnected successfully'
        ]);
    }

    /**
     * Test upload endpoint
     */
    public function test(): \CodeIgniter\HTTP\Response
    {
        if (!$this->oauthService->isAuthenticated()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated. Please connect first.',
                'auth_url' => $this->oauthService->getAuthUrl()
            ], 401);
        }

        // Create a test file
        $testContent = "Google Drive OAuth Test\nTimestamp: " . date('Y-m-d H:i:s');
        $testFileName = 'oauth_test_' . date('YmdHis') . '.txt';

        $result = $this->oauthService->uploadFileContent(
            $testContent,
            $testFileName,
            'text/plain'
        );

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Test upload successful!',
                'file_url' => $result
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Test upload failed: ' . $this->oauthService->getLastError()
            ], 500);
        }
    }

    /**
     * Fail response helper
     */
    private function fail(string $message): string
    {
        return view('oauth/error', ['message' => $message]);
    }

    /**
     * Public diagnostic endpoint to view log files directly
     */
    public function diagLog(): \CodeIgniter\HTTP\Response
    {
        $auth = $this->request->getGet('auth');
        if ($auth !== 'Antigravity999') {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }

        $logDir = WRITEPATH . 'logs/';
        $logFileContent = "=== Log Directory Listing ===\n";
        if (is_dir($logDir)) {
            $files = scandir($logDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $filePath = $logDir . $file;
                $logFileContent .= sprintf("%s - %d bytes - modified: %s\n", $file, filesize($filePath), date('Y-m-d H:i:s', filemtime($filePath)));
            }
        } else {
            $logFileContent .= "Directory not found: $logDir\n";
        }

        $logFile = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
        $rawLogFile = WRITEPATH . 'logs/simak_upload_raw.log';
        $logFileContent .= "\n";

        if (is_file($logFile)) {
            $logFileContent .= "--- " . basename($logFile) . " (Last 100 lines) ---\n";
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);
            $lastLines = array_slice($lines, -150);
            $logFileContent .= implode("\n", $lastLines);
        } else {
            $logFileContent = "Log file not found: $logFile\n";
        }

        if (is_file($rawLogFile)) {
            $logFileContent .= "\n\n--- " . basename($rawLogFile) . " (Last 50 lines) ---\n";
            $content = file_get_contents($rawLogFile);
            $lines = explode("\n", $content);
            $lastLines = array_slice($lines, -50);
            $logFileContent .= implode("\n", $lastLines);
        } else {
            $logFileContent .= "\nRaw log file not found: $rawLogFile\n";
        }

        return $this->response->setBody('<pre>' . esc($logFileContent) . '</pre>');
    }

    /**
     * Public diagnostic endpoint to view active session variables
     */
    public function diagSession(): \CodeIgniter\HTTP\Response
    {
        $auth = $this->request->getGet('auth');
        if ($auth !== 'Antigravity999') {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }

        $sessionData = session()->get();
        return $this->response->setJSON([
            'session_id' => session_id(),
            'session_data' => $sessionData,
            'is_logged_in' => session()->get('isLoggedIn') ? 'yes' : 'no',
            'role' => session()->get('role'),
        ]);
    }

    /**
     * Public diagnostic endpoint to test logging
     */
    public function testLog(): \CodeIgniter\HTTP\Response
    {
        $auth = $this->request->getGet('auth');
        if ($auth !== 'Antigravity999') {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }

        log_message('error', 'THIS IS A TEST ERROR LOG MESSAGE FROM DIAGNOSTIC ENDPOINT');
        log_message('critical', 'THIS IS A TEST CRITICAL LOG MESSAGE FROM DIAGNOSTIC ENDPOINT');

        return $this->response->setBody('Logged. Current time: ' . date('Y-m-d H:i:s'));
    }
}
