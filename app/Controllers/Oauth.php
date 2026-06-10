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
    public function connect(): string
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

            // Return success response
            return view('oauth/success', [
                'message' => 'Google Drive authentication successful! You can now upload files.'
            ]);
        } else {
            log_message('error', 'OAuth Controller - Authentication failed: ' . $this->oauthService->getLastError());
            return $this->fail('Authentication failed: ' . $this->oauthService->getLastError());
        }
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
}
