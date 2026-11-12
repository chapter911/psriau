<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Controller khusus environment DEVELOPMENT untuk menarik (sync) database
 * dari server production ke database lokal menggunakan Token Sync (tanpa NIP/password).
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
     * Menghubungkan ke server production menggunakan Token Sync dan mengambil daftar tabel.
     */
    public function fetchTables()
    {
        if ($denied = $this->ensureDevelopmentAccess()) {
            return $denied;
        }

        $prodUrl = rtrim(trim((string) $this->request->getPost('prod_url')), '/');
        $token   = trim((string) $this->request->getPost('sync_token'));
        if ($token === '') {
            $token = trim((string) env('PROD_DB_SYNC_TOKEN', ''));
        }

        if ($prodUrl === '' || ! preg_match('#^https?://#i', $prodUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'URL Production tidak valid. Gunakan format https://domain-anda.com',
            ]);
        }

        if ($token === '' || strlen($token) < 16) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Token Sync wajib diisi (minimal 16 karakter) dan harus sama dengan PROD_DB_SYNC_TOKEN di .env server production.',
            ]);
        }

        try {
            $tablesRes = $this->curlGetWithToken($prodUrl . '/dev-sync-api/prod-db-tables', $token, 30);
            if ($tablesRes['error'] !== '') {
                return $this->response->setStatusCode(502)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menghubungi server production: ' . $tablesRes['error'],
                ]);
            }

            if ($tablesRes['code'] === 404) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Endpoint export belum tersedia di server production. Silakan jalankan Git Pull di server production terlebih dahulu.',
                ]);
            }

            $json = json_decode($tablesRes['body'], true);
            if (! is_array($json) || ($json['status'] ?? '') !== 'ok') {
                return $this->response->setStatusCode($tablesRes['code'] >= 400 ? $tablesRes['code'] : 500)->setJSON([
                    'status'  => 'error',
                    'message' => $json['message'] ?? ('Gagal mengambil daftar tabel production (HTTP ' . $tablesRes['code'] . ').'),
                ]);
            }

            session()->set(self::SESSION_KEY, [
                'prod_url'     => $prodUrl,
                'sync_token'   => $token,
                'connected_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON([
                'status' => 'ok',
                'data'   => $json['data'] ?? [],
            ]);
        } catch (\Throwable $e) {
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
        if (! is_array($syncSession) || empty($syncSession['prod_url']) || empty($syncSession['sync_token'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Sesi koneksi ke production belum aktif. Silakan klik Muat Tabel terlebih dahulu.',
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
            . '/dev-sync-api/prod-db-dump?table=' . rawurlencode($table)
            . '&offset=' . $offset
            . '&limit=' . $limit
            . '&include_schema=' . $includeSchema;

        $res = $this->curlGetWithToken($dumpUrl, (string) $syncSession['sync_token'], 60);
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
            $db->query("SET SESSION sql_mode = 'ALLOW_INVALID_DATES'");
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
     * Membersihkan sesi sinkronisasi sementara.
     */
    public function disconnect()
    {
        if ($denied = $this->ensureDevelopmentAccess()) {
            return $denied;
        }

        session()->remove(self::SESSION_KEY);

        return $this->response->setJSON(['status' => 'ok']);
    }

    private function sanitizeSchemaSql(string $sql): string
    {
        $sql = str_replace(
            ['utf8mb4_0900_ai_ci', 'utf8mb4_uca1400_ai_ci', 'utf8mb4_unicode_520_ci'],
            'utf8mb4_general_ci',
            $sql
        );

        // Bersihkan zero-date default agar aman di MySQL 8/9 strict mode
        $sql = preg_replace(
            "/\bNOT\s+NULL\s+DEFAULT\s+'0000-00-00(?:\s+00:00:00)?'/i",
            'NULL DEFAULT NULL',
            $sql
        );
        $sql = preg_replace(
            "/\bDEFAULT\s+'0000-00-00(?:\s+00:00:00)?'/i",
            'DEFAULT NULL',
            $sql
        );

        // MariaDB mengizinkan `date DEFAULT current_timestamp()`, namun MySQL 8/9 menolaknya
        $sql = preg_replace(
            "/\b(date)\s+(?:NOT\s+NULL\s+)?DEFAULT\s+current_timestamp(?:\(\))?/i",
            '$1 DEFAULT NULL',
            $sql
        );

        return $sql;
    }

    private function curlGetWithToken(string $url, string $token, int $timeout = 30): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_USERAGENT      => 'SatkerPPS-DevDbSync/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER     => [
                'X-Sync-Token: ' . $token,
                'Accept: application/json',
            ],
        ]);

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
}
