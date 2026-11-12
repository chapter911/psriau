<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Controller khusus environment DEVELOPMENT untuk menarik (sync) database
 * dari server production ke database lokal secara penuh atau per tabel tertentu.
 */
class DevDbSync extends BaseController
{
    private const SESSION_KEY = 'dev_prod_sync';

    /**
     * Memastikan endpoint hanya dapat diakses pada environment development oleh Super Administrator.
     */
    private function ensureDevelopmentAccess()
    {
        if (! defined('ENVIRONMENT') || ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak. Fitur sinkronisasi database hanya tersedia di environment development.',
            ]);
        }

        $role = strtolower(trim((string) session()->get('role')));
        $isSuperAdmin = in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);

        if (! $isSuperAdmin) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak. Hanya Super Administrator yang dapat menggunakan fitur ini.',
            ]);
        }

        return null;
    }

    /**
     * Menghubungkan ke server production, login, dan mengambil daftar tabel beserta jumlah barisnya.
     */
    public function fetchTables()
    {
        if ($denied = $this->ensureDevelopmentAccess()) {
            return $denied;
        }

        $prodUrl  = rtrim(trim((string) $this->request->getPost('prod_url')), '/');
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        if ($prodUrl === '' || ! preg_match('#^https?://#i', $prodUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'URL Production tidak valid. Gunakan format https://domain-anda.com',
            ]);
        }

        if ($username === '' || $password === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Username dan Password Admin Production wajib diisi.',
            ]);
        }

        $cookieDir = WRITEPATH . 'cache';
        if (! is_dir($cookieDir)) {
            @mkdir($cookieDir, 0755, true);
        }

        // Bersihkan cookie lama jika ada
        $prevSession = session()->get(self::SESSION_KEY);
        if (is_array($prevSession) && ! empty($prevSession['cookie_file']) && is_file($prevSession['cookie_file'])) {
            @unlink($prevSession['cookie_file']);
        }

        $cookieFile = $cookieDir . DIRECTORY_SEPARATOR . 'dev_sync_cookie_' . bin2hex(random_bytes(8)) . '.txt';

        try {
            // 1. Ambil halaman login untuk mendapatkan CSRF cookie
            $loginPage = $this->curlRequest($prodUrl . '/masuk', 'GET', null, $cookieFile, 20);
            if ($loginPage['error'] !== '') {
                @unlink($cookieFile);
                return $this->response->setStatusCode(502)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menghubungi server production: ' . $loginPage['error'],
                ]);
            }

            $csrf = $this->extractCsrfFromCookieFile($cookieFile);

            // 2. Login ke production
            $loginRes = $this->curlRequest($prodUrl . '/masuk', 'POST', http_build_query([
                'username'       => $username,
                'password'       => $password,
                'csrf_test_name' => $csrf,
            ]), $cookieFile, 25);

            if ($loginRes['error'] !== '') {
                @unlink($cookieFile);
                return $this->response->setStatusCode(502)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal login ke server production: ' . $loginRes['error'],
                ]);
            }

            if (stripos($loginRes['body'], 'id="username"') !== false || stripos($loginRes['body'], 'name="username"') !== false) {
                @unlink($cookieFile);
                return $this->response->setStatusCode(401)->setJSON([
                    'status'  => 'error',
                    'message' => 'Login ke server production gagal. Periksa kembali Username / Password Admin Production.',
                ]);
            }

            // 3. Ambil daftar tabel production
            $tablesRes = $this->curlRequest($prodUrl . '/admin/pengaturan/application/prod-db-tables', 'GET', null, $cookieFile, 30);
            if ($tablesRes['code'] === 404) {
                @unlink($cookieFile);
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Endpoint export belum tersedia di server production. Silakan jalankan Git Pull di server production terlebih dahulu.',
                ]);
            }

            $json = json_decode($tablesRes['body'], true);
            if (! is_array($json) || ($json['status'] ?? '') !== 'ok') {
                @unlink($cookieFile);
                return $this->response->setStatusCode($tablesRes['code'] >= 400 ? $tablesRes['code'] : 500)->setJSON([
                    'status'  => 'error',
                    'message' => $json['message'] ?? ('Gagal mengambil daftar tabel production (HTTP ' . $tablesRes['code'] . ').'),
                ]);
            }

            // Simpan sesi koneksi sementara (tanpa menyimpan password)
            session()->set(self::SESSION_KEY, [
                'prod_url'    => $prodUrl,
                'cookie_file' => $cookieFile,
                'connected_at'=> date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON([
                'status' => 'ok',
                'data'   => $json['data'] ?? [],
            ]);
        } catch (\Throwable $e) {
            if (is_file($cookieFile)) {
                @unlink($cookieFile);
            }
            log_message('error', 'DevDbSync fetchTables error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat menghubungkan ke production: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Menarik satu batch data (dan schema pada offset=0) untuk satu tabel dari production ke database lokal.
     */
    public function pullBatch()
    {
        if ($denied = $this->ensureDevelopmentAccess()) {
            return $denied;
        }

        $syncSession = session()->get(self::SESSION_KEY);
        if (! is_array($syncSession) || empty($syncSession['prod_url']) || empty($syncSession['cookie_file']) || ! is_file($syncSession['cookie_file'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Sesi koneksi ke production belum aktif atau sudah kedaluwarsa. Silakan hubungkan ulang.',
            ]);
        }

        $table  = trim((string) $this->request->getPost('table'));
        $offset = max(0, (int) ($this->request->getPost('offset') ?? 0));
        $limit  = (int) ($this->request->getPost('limit') ?? 500);
        if ($limit < 1 || $limit > 1000) {
            $limit = 500;
        }
        $mode = strtolower(trim((string) ($this->request->getPost('mode') ?? 'replace')));
        if (! in_array($mode, ['replace', 'truncate_insert'], true)) {
            $mode = 'replace';
        }

        if ($table === '' || ! preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Nama tabel tidak valid.',
            ]);
        }

        @set_time_limit(120);
        @ini_set('memory_limit', '512M');

        $includeSchema = ($offset === 0 && $mode === 'replace') ? '1' : '0';
        $dumpUrl = $syncSession['prod_url']
            . '/admin/pengaturan/application/prod-db-dump?table=' . rawurlencode($table)
            . '&offset=' . $offset
            . '&limit=' . $limit
            . '&include_schema=' . $includeSchema;

        $res = $this->curlRequest($dumpUrl, 'GET', null, $syncSession['cookie_file'], 60);
        if ($res['error'] !== '') {
            return $this->response->setStatusCode(502)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal mengambil batch tabel ' . $table . ': ' . $res['error'],
            ]);
        }

        $payload = json_decode($res['body'], true);
        if (! is_array($payload) || ($payload['status'] ?? '') !== 'ok') {
            return $this->response->setStatusCode($res['code'] >= 400 ? $res['code'] : 500)->setJSON([
                'status'  => 'error',
                'message' => $payload['message'] ?? ('Gagal mengekspor tabel ' . $table . ' dari production (HTTP ' . $res['code'] . ').'),
            ]);
        }

        $total     = (int) ($payload['total'] ?? 0);
        $rows      = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];
        $schemaSql = $payload['schema_sql'] ?? null;

        $db = \Config\Database::connect();
        $escapedTable = '`' . str_replace('`', '``', $table) . '`';

        try {
            $db->query('SET FOREIGN_KEY_CHECKS = 0');

            if ($offset === 0) {
                if ($mode === 'replace' && is_string($schemaSql) && trim($schemaSql) !== '') {
                    $cleanSchema = $this->sanitizeSchemaSql($schemaSql);
                    $db->query("DROP TABLE IF EXISTS {$escapedTable}");
                    $db->query($cleanSchema);
                } elseif ($db->tableExists($table)) {
                    $db->query("TRUNCATE TABLE {$escapedTable}");
                }
            }

            $inserted = 0;
            if (! empty($rows)) {
                // Insert per 250 baris agar query packet MySQL lokal tidak kepenuhan
                foreach (array_chunk($rows, 250) as $chunk) {
                    $db->table($table)->insertBatch($chunk);
                    $inserted += count($chunk);
                }
            }

            $db->query('SET FOREIGN_KEY_CHECKS = 1');

            $nextOffset = $offset + count($rows);
            $done = ($nextOffset >= $total) || empty($rows);

            return $this->response->setJSON([
                'status'      => 'ok',
                'table'       => $table,
                'total'       => $total,
                'offset'      => $offset,
                'next_offset' => $nextOffset,
                'inserted'    => $inserted,
                'done'        => $done,
            ]);
        } catch (\Throwable $e) {
            try {
                $db->query('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Throwable $ignored) {
            }
            log_message('error', 'DevDbSync pullBatch error (' . $table . '): ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data tabel ' . $table . ' ke database lokal: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Membersihkan cookie dan sesi sinkronisasi sementara.
     */
    public function disconnect()
    {
        if ($denied = $this->ensureDevelopmentAccess()) {
            return $denied;
        }

        $syncSession = session()->get(self::SESSION_KEY);
        if (is_array($syncSession) && ! empty($syncSession['cookie_file']) && is_file($syncSession['cookie_file'])) {
            @unlink($syncSession['cookie_file']);
        }
        session()->remove(self::SESSION_KEY);

        return $this->response->setJSON(['status' => 'ok']);
    }

    private function sanitizeSchemaSql(string $sql): string
    {
        // Normalisasi collation MySQL 8 / MariaDB agar kompatibel lintas versi lokal
        $sql = str_replace(
            ['utf8mb4_0900_ai_ci', 'utf8mb4_uca1400_ai_ci', 'utf8mb4_unicode_520_ci'],
            'utf8mb4_general_ci',
            $sql
        );

        return $sql;
    }

    private function curlRequest(string $url, string $method, ?string $postFields, string $cookieFile, int $timeout = 30): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_COOKIEFILE     => $cookieFile,
            CURLOPT_COOKIEJAR      => $cookieFile,
            CURLOPT_USERAGENT      => 'SatkerPPS-DevDbSync/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postFields !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }
        }

        $body  = curl_exec($ch);
        $error = curl_error($ch);
        $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code'  => $code,
            'body'  => is_string($body) ? $body : '',
            'error' => $error,
        ];
    }

    private function extractCsrfFromCookieFile(string $cookieFile): string
    {
        if (! is_file($cookieFile)) {
            return '';
        }

        $content = (string) file_get_contents($cookieFile);
        if (preg_match('/csrf_cookie_name\s+([^\s]+)/', $content, $m)) {
            return trim($m[1]);
        }

        return '';
    }
}

