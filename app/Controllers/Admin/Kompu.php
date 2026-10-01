<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KompuSosmedModel;
use App\Models\KompuLogPengirimanModel;
use App\Models\KompuUserAccessModel;
use App\Models\MstPegawaiModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Kompu extends BaseController
{
    private const MENU_LINK_SOSMED      = 'admin/kompu/sosmed';
    private const MENU_LINK_LOG         = 'admin/kompu/log-kredensial';
    private const MENU_LINK_PENGATURAN  = 'admin/kompu/pengaturan';

    protected KompuSosmedModel $sosmedModel;
    protected KompuLogPengirimanModel $logModel;
    protected KompuUserAccessModel $userAccessModel;

    public function __construct()
    {
        $this->sosmedModel      = new KompuSosmedModel();
        $this->logModel         = new KompuLogPengirimanModel();
        $this->userAccessModel  = new KompuUserAccessModel();
    }

    // =========================================================================
    // SUBMENU 1: MEDIA SOSIAL SATKER
    // =========================================================================

    public function sosmedIndex()
    {
        $forbidden = $this->denyIfNoAccess(self::MENU_LINK_SOSMED);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = $this->sosmedModel
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $currentUserEmail = $this->resolveCurrentUserEmail();
        $menuPermissions  = $this->resolveMenuPermissions(self::MENU_LINK_SOSMED);

        return view('admin/kompu/sosmed/index', [
            'title'            => 'Media Sosial Satker',
            'items'            => $items,
            'currentUserEmail' => $currentUserEmail,
            'menuPermissions'  => $menuPermissions,
            'isSuperAdmin'     => $this->isSuperAdmin(),
        ]);
    }

    public function saveSosmed()
    {
        $forbidden = $this->denyIfNoAccess(self::MENU_LINK_SOSMED);
        if ($forbidden instanceof RedirectResponse) {
            return $this->request->isAJAX()
                ? $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.'])
                : $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_SOSMED);
        $id = (int) $this->request->getPost('id');

        if ($id > 0 && ! $permissions['edit']) {
            return $this->respondError('Anda tidak memiliki izin untuk mengedit data ini.', 403);
        }
        if ($id <= 0 && ! $permissions['add']) {
            return $this->respondError('Anda tidak memiliki izin untuk menambah data.', 403);
        }

        $namaSosmed = trim((string) $this->request->getPost('nama_sosmed'));
        if ($namaSosmed === '') {
            return $this->respondError('Nama Platform / Media Sosial wajib diisi.', 422);
        }

        $data = [
            'nama_sosmed'  => $namaSosmed,
            'kategori'     => trim((string) ($this->request->getPost('kategori') ?: 'Media Sosial')),
            'username'     => trim((string) $this->request->getPost('username')),
            'email_login'  => trim((string) $this->request->getPost('email_login')),
            'password'     => (string) $this->request->getPost('password'),
            'url_profil'   => trim((string) $this->request->getPost('url_profil')),
            'metode_login' => trim((string) $this->request->getPost('metode_login')),
            'keterangan'   => trim((string) $this->request->getPost('keterangan')),
            'icon'         => trim((string) ($this->request->getPost('icon') ?: 'fas fa-hashtag')),
            'ordering'     => (int) ($this->request->getPost('ordering') ?? 0),
            'is_active'    => (int) ($this->request->getPost('is_active') ?? 1),
        ];

        $currentUserName = (string) (session()->get('fullName') ?: session()->get('username') ?: 'User');

        if ($id > 0) {
            $data['updated_by'] = $currentUserName;
            $this->sosmedModel->update($id, $data);
            $msg = 'Data akun media sosial berhasil diperbarui.';
        } else {
            $data['created_by'] = $currentUserName;
            $this->sosmedModel->insert($data);
            $msg = 'Akun media sosial baru berhasil ditambahkan.';
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => $msg,
            ]);
        }

        return redirect()->to(site_url(self::MENU_LINK_SOSMED))->with('message', $msg);
    }

    public function deleteSosmed(int $id)
    {
        $forbidden = $this->denyIfNoAccess(self::MENU_LINK_SOSMED);
        if ($forbidden instanceof RedirectResponse) {
            return $this->request->isAJAX()
                ? $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.'])
                : $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_SOSMED);
        if (! $permissions['delete']) {
            return $this->respondError('Anda tidak memiliki hak akses untuk menghapus data.', 403);
        }

        $row = $this->sosmedModel->find($id);
        if (! $row) {
            return $this->respondError('Data akun tidak ditemukan.', 404);
        }

        $this->sosmedModel->delete($id);
        $msg = 'Akun ' . esc($row['nama_sosmed']) . ' berhasil dihapus.';

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => $msg,
            ]);
        }

        return redirect()->to(site_url(self::MENU_LINK_SOSMED))->with('message', $msg);
    }

    /**
     * AJAX Action: Kirim password akun sosmed ke email pengguna yang sedang login
     */
    public function sendPassword()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Permintaan tidak valid.',
            ]);
        }

        $forbidden = $this->denyIfNoAccess(self::MENU_LINK_SOSMED);
        if ($forbidden instanceof RedirectResponse) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk meminta kredensial.',
            ]);
        }

        $sosmedId = (int) $this->request->getPost('sosmed_id');
        $sosmed = $this->sosmedModel->find($sosmedId);

        if (! $sosmed) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Akun media sosial tidak ditemukan.',
            ]);
        }

        // Resolving recipient details
        $userId        = (int) (session()->get('userId') ?? 0);
        $sessionUser   = trim((string) (session()->get('username') ?? ''));
        $sessionName   = trim((string) (session()->get('fullName') ?? ''));
        $userEmailInfo = $this->resolveCurrentUserEmailDetails();
        $recipientEmail = $userEmailInfo['email'] ?? null;
        $recipientName  = $userEmailInfo['nama'] ?? ($sessionName ?: $sessionUser);
        $recipientNip   = $userEmailInfo['nip'] ?? $sessionUser;

        $ipAddress = (string) ($this->request->getIPAddress() ?? '');
        $userAgent = substr((string) $this->request->getUserAgent(), 0, 250);

        // Verification 1: Valid email address
        if (empty($recipientEmail) || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->logPengiriman(
                $sosmedId,
                $sosmed['nama_sosmed'],
                $userId,
                $recipientNip,
                $recipientName,
                $recipientEmail ?: '-',
                'gagal',
                'Email pengguna belum terdaftar di data profil pegawai.',
                $ipAddress,
                $userAgent
            );

            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Alamat email Anda belum terdaftar di data profil pegawai sistem. Silakan lengkapi email di menu Pegawai / Profil atau hubungi Administrator.',
            ]);
        }

        // Verification 2: Check password availability
        $password = trim((string) ($sosmed['password'] ?? ''));
        if ($password === '') {
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 'info',
                'message' => 'Akun ' . esc($sosmed['nama_sosmed']) . ' tidak memiliki password tersimpan karena menggunakan ' . esc($sosmed['metode_login'] ?: 'Akun Google Satker') . '.',
            ]);
        }

        // Prepare email content
        $email = \Config\Services::email();
        $emailConfig = config('Email');
        $fromEmail = trim((string) ($emailConfig->fromEmail ?? 'no-reply@satkerpps-riau.online'));
        $fromName  = trim((string) ($emailConfig->fromName ?? 'SATKER PPS Riau'));

        $subject = '[RAHASIA] Kredensial Akun: ' . $sosmed['nama_sosmed'] . ' - Satker PPS Riau';
        $messageBody = $this->buildCredentialEmailHtml($sosmed, $recipientName, $recipientNip, $password);

        try {
            $email->clear(true);
            $email->setMailType('html');
            $email->setFrom($fromEmail, $fromName !== '' ? $fromName : 'SATKER PPS Riau');
            $email->setTo($recipientEmail);
            $email->setSubject($subject);
            $email->setMessage($messageBody);

            if (! $email->send()) {
                $rawDebug = (string) $email->printDebugger(['headers', 'subject', 'body']);
                $parsedError = $this->parseSmtpError($rawDebug);

                $this->logPengiriman(
                    $sosmedId,
                    $sosmed['nama_sosmed'],
                    $userId,
                    $recipientNip,
                    $recipientName,
                    $recipientEmail,
                    'gagal',
                    $parsedError,
                    $ipAddress,
                    $userAgent
                );

                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Email gagal terkirim ke ' . esc($recipientEmail) . '. ' . esc($parsedError),
                ]);
            }

            // Success log
            $this->logPengiriman(
                $sosmedId,
                $sosmed['nama_sosmed'],
                $userId,
                $recipientNip,
                $recipientName,
                $recipientEmail,
                'sukses',
                'Berhasil dikirimkan ke inbox penerima.',
                $ipAddress,
                $userAgent
            );

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Password akun ' . esc($sosmed['nama_sosmed']) . ' telah berhasil dikirimkan ke email Anda: ' . esc($recipientEmail) . '. Silakan periksa inbox atau spam.',
                'email'   => $recipientEmail,
            ]);
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            $this->logPengiriman(
                $sosmedId,
                $sosmed['nama_sosmed'],
                $userId,
                $recipientNip,
                $recipientName,
                $recipientEmail,
                'gagal',
                'Exception: ' . substr($errorMsg, 0, 300),
                $ipAddress,
                $userAgent
            );

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem saat mengirim email: ' . esc($errorMsg),
            ]);
        }
    }

    public function exportSosmed()
    {
        $forbidden = $this->denyIfNoAccess(self::MENU_LINK_SOSMED);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_SOSMED);
        if (! $permissions['export']) {
            return redirect()->to(site_url(self::MENU_LINK_SOSMED))->with('error', 'Anda tidak memiliki hak akses untuk mengekspor data.');
        }

        $items = $this->sosmedModel
            ->orderBy('ordering', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $filename = 'Daftar_Akun_Media_Sosial_Satker_PPS_Riau_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['No.', 'Nama Platform / Media Sosial', 'Kategori', 'Username', 'Email Login', 'Password', 'Metode Login', 'Laman Profil', 'Keterangan']);

        $no = 1;
        foreach ($items as $item) {
            fputcsv($output, [
                $no++,
                $item['nama_sosmed'],
                $item['kategori'],
                $item['username'] ?: '-',
                $item['email_login'] ?: '-',
                ! empty($item['password']) ? '•••••••• (Dirahasiakan)' : '(Login Google SSO)',
                $item['metode_login'] ?: '-',
                $item['url_profil'] ?: '-',
                $item['keterangan'] ?: '-',
            ]);
        }

        fclose($output);
        exit;
    }

    // =========================================================================
    // SUBMENU 2: LOG PENGIRIMAN KREDENSIAL
    // =========================================================================

    public function logIndex()
    {
        $forbidden = $this->denyIfNoAccess(self::MENU_LINK_LOG);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $statusFilter = trim((string) ($this->request->getGet('status') ?? ''));
        $search       = trim((string) ($this->request->getGet('q') ?? ''));

        $query = $this->logModel->orderBy('id', 'DESC');

        if ($statusFilter !== '' && in_array($statusFilter, ['sukses', 'gagal'], true)) {
            $query->where('status', $statusFilter);
        }

        if ($search !== '') {
            $query->groupStart()
                ->like('nama_sosmed', $search)
                ->orLike('user_name', $search)
                ->orLike('user_nip', $search)
                ->orLike('email_tujuan', $search)
                ->orLike('pesan_status', $search)
                ->groupEnd();
        }

        // Non-SuperAdmin only sees their own logs unless granted can_view_log
        if (! $this->isSuperAdmin()) {
            $userAccess = $this->userAccessModel->where('user_id', (int) session()->get('userId'))->first();
            $canViewAllLog = (int) ($userAccess['can_view_log'] ?? 0) === 1;

            if (! $canViewAllLog) {
                $query->where('user_id', (int) session()->get('userId'));
            }
        }

        $logs = $query->findAll(250);
        $menuPermissions = $this->resolveMenuPermissions(self::MENU_LINK_LOG);

        return view('admin/kompu/log/index', [
            'title'           => 'Log Pengiriman Kredensial',
            'logs'            => $logs,
            'statusFilter'    => $statusFilter,
            'search'          => $search,
            'menuPermissions' => $menuPermissions,
            'isSuperAdmin'    => $this->isSuperAdmin(),
        ]);
    }

    public function exportLog()
    {
        $forbidden = $this->denyIfNoAccess(self::MENU_LINK_LOG);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $permissions = $this->resolveMenuPermissions(self::MENU_LINK_LOG);
        if (! $permissions['export']) {
            return redirect()->to(site_url(self::MENU_LINK_LOG))->with('error', 'Anda tidak memiliki hak akses ekspor.');
        }

        $logs = $this->logModel->orderBy('id', 'DESC')->findAll(1000);

        $filename = 'Log_Pengiriman_Kredensial_KOMPU_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['No.', 'Waktu Permintaan', 'Nama Platform', 'Nama Pegawai', 'NIP', 'Email Tujuan', 'Status', 'Keterangan / Debug', 'IP Address']);

        $no = 1;
        foreach ($logs as $log) {
            fputcsv($output, [
                $no++,
                $log['created_at'],
                $log['nama_sosmed'],
                $log['user_name'],
                $log['user_nip'] ?: '-',
                $log['email_tujuan'],
                strtoupper($log['status']),
                $log['pesan_status'],
                $log['ip_address'],
            ]);
        }

        fclose($output);
        exit;
    }

    // =========================================================================
    // SUBMENU 3: PENGATURAN (STRICT SUPER ADMINISTRATOR ONLY)
    // =========================================================================

    public function pengaturanIndex()
    {
        if (! $this->isSuperAdmin()) {
            return redirect()->to('/forbidden?from=' . rawurlencode(self::MENU_LINK_PENGATURAN));
        }

        $db = \Config\Database::connect();

        // 1. Fetch Users with Pegawai data
        $users = [];
        if ($db->tableExists('users')) {
            $userRows = $db->table('users')
                ->select('users.id, users.username, users.full_name, users.role, users.is_active, p.nip, p.email, p.jenis_pegawai, j.jabatan')
                ->join('mst_pegawai p', 'LOWER(p.nip) = LOWER(users.username) OR LOWER(p.nama) = LOWER(users.full_name)', 'left')
                ->join('mst_jabatan j', 'j.id = p.jabatan_utama_id', 'left')
                ->orderBy('users.role', 'ASC')
                ->orderBy('users.full_name', 'ASC')
                ->get()
                ->getResultArray();
            $users = $userRows;
        }

        // 2. Fetch User Specific Access
        $userAccessRecords = $this->userAccessModel->findAll();
        $userAccessMap = [];
        foreach ($userAccessRecords as $uar) {
            $userAccessMap[(int) $uar['user_id']] = $uar;
        }

        // 3. Fetch Roles and KOMPU Menu Akses Matrix
        $roles = [];
        if ($db->tableExists('access_roles')) {
            $roles = $db->table('access_roles')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
        }

        $kompuMenus = [];
        if ($db->tableExists('menu_lv2')) {
            $kompuMenus = $db->table('menu_lv2')
                ->where('header', '13')
                ->orderBy('ordering', 'ASC')
                ->get()
                ->getResultArray();
        }

        $menuAksesMap = [];
        if ($db->tableExists('menu_akses')) {
            $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
            $aksesRows = $db->table('menu_akses')
                ->whereIn('menu_id', ['13', '13-01', '13-02', '13-03'])
                ->get()
                ->getResultArray();

            foreach ($aksesRows as $ar) {
                $rId = (int) $ar[$roleColumn];
                $mId = (string) $ar['menu_id'];
                $menuAksesMap[$rId][$mId] = [
                    'add'      => (int) ($ar['FiturAdd'] ?? 0) === 1,
                    'edit'     => (int) ($ar['FiturEdit'] ?? 0) === 1,
                    'delete'   => (int) ($ar['FiturDelete'] ?? 0) === 1,
                    'export'   => (int) ($ar['FiturExport'] ?? 0) === 1,
                    'import'   => (int) ($ar['FiturImport'] ?? 0) === 1,
                    'approval' => (int) ($ar['FiturApproval'] ?? 0) === 1,
                ];
            }
        }

        // 4. Email configuration info
        $emailConfig = config('Email');
        $smtpInfo = [
            'fromEmail' => $emailConfig->fromEmail ?? 'no-reply@satkerpps-riau.online',
            'fromName'  => $emailConfig->fromName ?? 'SATKER PPS Riau',
            'protocol'  => $emailConfig->protocol ?? 'smtp',
            'host'      => $emailConfig->SMTPHost ?? '-',
            'port'      => $emailConfig->SMTPPort ?? 465,
            'crypto'    => $emailConfig->SMTPCrypto ?? 'ssl',
            'user'      => $emailConfig->SMTPUser ?? '-',
        ];

        return view('admin/kompu/pengaturan/index', [
            'title'         => 'Pengaturan Akses Modul KOMPU',
            'users'         => $users,
            'userAccessMap' => $userAccessMap,
            'roles'         => $roles,
            'kompuMenus'    => $kompuMenus,
            'menuAksesMap'  => $menuAksesMap,
            'smtpInfo'      => $smtpInfo,
            'isSuperAdmin'  => true,
        ]);
    }

    public function saveUserAccess()
    {
        if (! $this->isSuperAdmin()) {
            return $this->respondError('Hanya Super Administrator yang diizinkan mengubah otorisasi pengguna.', 403);
        }

        $accessData = (array) $this->request->getPost('user_access');
        $viewLogData = (array) $this->request->getPost('can_view_log');
        $notesData  = (array) $this->request->getPost('notes');

        $db = \Config\Database::connect();
        $allUsers = $db->table('users')->select('id')->get()->getResultArray();
        $currentAdminName = (string) (session()->get('fullName') ?: session()->get('username') ?: 'Super Admin');

        foreach ($allUsers as $u) {
            $uid = (int) $u['id'];
            $canAccess = isset($accessData[$uid]) && (int) $accessData[$uid] === 1 ? 1 : 0;
            $canLog    = isset($viewLogData[$uid]) && (int) $viewLogData[$uid] === 1 ? 1 : 0;
            $notes     = trim((string) ($notesData[$uid] ?? ''));

            $existing = $this->userAccessModel->where('user_id', $uid)->first();
            if ($existing) {
                $this->userAccessModel->update($existing['id'], [
                    'can_access'   => $canAccess,
                    'can_view_log' => $canLog,
                    'notes'        => $notes,
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->userAccessModel->insert([
                    'user_id'      => $uid,
                    'can_access'   => $canAccess,
                    'can_view_log' => $canLog,
                    'notes'        => $notes,
                    'created_by'   => $currentAdminName,
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->to(site_url(self::MENU_LINK_PENGATURAN))->with('message', 'Pengaturan otorisasi akses pengguna KOMPU berhasil disimpan.');
    }

    public function saveRolePermissions()
    {
        if (! $this->isSuperAdmin()) {
            return $this->respondError('Hanya Super Administrator yang diizinkan mengubah hak akses role.', 403);
        }

        $matrix = (array) $this->request->getPost('matrix');
        $db = \Config\Database::connect();

        if (! $db->tableExists('menu_akses')) {
            return redirect()->to(site_url(self::MENU_LINK_PENGATURAN))->with('error', 'Tabel menu_akses tidak ditemukan.');
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        // KOMPU Submenus: 13-01, 13-02 (13-03 remains Super Admin only)
        $submenus = ['13-01', '13-02'];

        foreach ($matrix as $roleId => $menus) {
            $roleId = (int) $roleId;
            if ($roleId <= 0) {
                continue;
            }

            foreach ($submenus as $menuId) {
                $perm = (array) ($menus[$menuId] ?? []);
                $hasView = ! empty($perm['view']);

                $exists = (int) $db->table('menu_akses')
                    ->where($roleColumn, $roleId)
                    ->where('menu_id', $menuId)
                    ->countAllResults();

                if ($hasView) {
                    $payload = [
                        'FiturAdd'      => ! empty($perm['add']) ? 1 : 0,
                        'FiturEdit'     => ! empty($perm['edit']) ? 1 : 0,
                        'FiturDelete'   => ! empty($perm['delete']) ? 1 : 0,
                        'FiturExport'   => ! empty($perm['export']) ? 1 : 0,
                        'FiturImport'   => ! empty($perm['import']) ? 1 : 0,
                        'FiturApproval' => 0,
                    ];

                    if ($exists > 0) {
                        $db->table('menu_akses')
                            ->where($roleColumn, $roleId)
                            ->where('menu_id', $menuId)
                            ->update($payload);
                    } else {
                        $payload[$roleColumn] = $roleId;
                        $payload['menu_id']   = $menuId;
                        $db->table('menu_akses')->insert($payload);
                    }
                } else {
                    // If unchecked view, remove entry so it disappears from sidebar
                    if ($exists > 0 && $roleId !== 1) { // Super Admin always keeps access
                        $db->table('menu_akses')
                            ->where($roleColumn, $roleId)
                            ->where('menu_id', $menuId)
                            ->delete();
                    }
                }
            }

            // Ensure parent header '13' exists in menu_akses if any submenu is granted
            $hasAnySubmenu = (int) $db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->whereIn('menu_id', ['13-01', '13-02', '13-03'])
                ->countAllResults() > 0;

            $hasParent = (int) $db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', '13')
                ->countAllResults() > 0;

            if ($hasAnySubmenu && ! $hasParent) {
                $db->table('menu_akses')->insert([
                    $roleColumn     => $roleId,
                    'menu_id'       => '13',
                    'FiturAdd'      => 0,
                    'FiturEdit'     => 0,
                    'FiturDelete'   => 0,
                    'FiturExport'   => 0,
                    'FiturImport'   => 0,
                    'FiturApproval' => 0,
                ]);
            } elseif (! $hasAnySubmenu && $hasParent && $roleId !== 1) {
                $db->table('menu_akses')
                    ->where($roleColumn, $roleId)
                    ->where('menu_id', '13')
                    ->delete();
            }
        }

        return redirect()->to(site_url(self::MENU_LINK_PENGATURAN))->with('message', 'Matriks hak akses role KOMPU berhasil diperbarui.');
    }

    public function testEmail()
    {
        if (! $this->isSuperAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $targetEmail = trim((string) $this->request->getPost('test_email'));
        if (empty($targetEmail) || ! filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            $userEmailInfo = $this->resolveCurrentUserEmailDetails();
            $targetEmail = $userEmailInfo['email'] ?? '';
        }

        if (empty($targetEmail) || ! filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Alamat email tujuan pengujian tidak valid.',
            ]);
        }

        $email = \Config\Services::email();
        $emailConfig = config('Email');
        $fromEmail = trim((string) ($emailConfig->fromEmail ?? 'no-reply@satkerpps-riau.online'));
        $fromName  = trim((string) ($emailConfig->fromName ?? 'SATKER PPS Riau'));

        try {
            $email->clear(true);
            $email->setMailType('html');
            $email->setFrom($fromEmail, $fromName !== '' ? $fromName : 'SATKER PPS Riau');
            $email->setTo($targetEmail);
            $email->setSubject('Uji Koneksi Pengiriman Email - Modul KOMPU Satker PPS Riau');
            $email->setMessage('
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <h3 style="color: #0A66C2; margin-top: 0;">Uji Coba Pengiriman Email Berhasil</h3>
                    <p>Halo, ini adalah pesan konfirmasi bahwa koneksi email SMTP pada sistem <strong>SATKER PPS RIAU</strong> (Modul KOMPU) berfungsi secara optimal.</p>
                    <p style="color: #64748b; font-size: 13px;">Waktu pengujian: ' . date('d F Y, H:i:s') . ' WIB</p>
                </div>
            ');

            if (! $email->send()) {
                $rawDebug = (string) $email->printDebugger(['headers', 'subject', 'body']);
                $parsedError = $this->parseSmtpError($rawDebug);

                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal mengirim email uji coba: ' . esc($parsedError),
                ]);
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Email uji coba berhasil dikirimkan ke ' . esc($targetEmail) . '.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    // =========================================================================
    // PRIVATE ACCESS & HELPER METHODS
    // =========================================================================

    private function isSuperAdmin(): bool
    {
        $role = strtolower(trim((string) (session()->get('role') ?? '')));
        return in_array($role, ['super_administrator', 'super administrator', 'super-admin', 'superadmin'], true);
    }

    private function denyIfNoAccess(string $menuLink): ?RedirectResponse
    {
        // 1. Super Admin always bypasses
        if ($this->isSuperAdmin()) {
            return null;
        }

        // 2. Strict check for Pengaturan: ONLY Super Admin!
        if ($menuLink === self::MENU_LINK_PENGATURAN) {
            return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
        }

        $userId = (int) (session()->get('userId') ?? 0);
        if ($userId <= 0) {
            return redirect()->to('/masuk');
        }

        // 3. User-specific authorization check in cfg_kompu_user_access
        $userAccess = $this->userAccessModel->where('user_id', $userId)->first();
        if ($userAccess !== null && (int) ($userAccess['can_access'] ?? 1) === 0) {
            return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
        }

        // 4. Role-based menu_akses check
        $db = \Config\Database::connect();
        if ($db->tableExists('menu_akses')) {
            $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
            $menuId = $this->resolveMenuIdByLink($menuLink, $db);

            if ($roleId !== null && $menuId !== null) {
                $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
                $hasAccess = (int) $db->table('menu_akses')
                    ->where($roleColumn, $roleId)
                    ->where('menu_id', $menuId)
                    ->countAllResults() > 0;

                if (! $hasAccess) {
                    return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
                }
            }
        }

        return null;
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $default = [
            'add'      => false,
            'edit'     => false,
            'delete'   => false,
            'export'   => false,
            'import'   => false,
            'approval' => false,
        ];

        if ($this->isSuperAdmin()) {
            return [
                'add'      => true,
                'edit'     => true,
                'delete'   => true,
                'export'   => true,
                'import'   => true,
                'approval' => true,
            ];
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('menu_akses')) {
            return $default;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($roleId === null || $menuId === null) {
            return $default;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';
        $row = $db->table('menu_akses')
            ->select('FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return $default;
        }

        return [
            'add'      => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit'     => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete'   => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export'   => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import'   => (bool) ((int) ($row['FiturImport'] ?? 0)),
            'approval' => (bool) ((int) ($row['FiturApproval'] ?? 0)),
        ];
    }

    private function resolveRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));
        if ($normalized === '') {
            return null;
        }

        if ($db->tableExists('access_roles')) {
            $variants = [$normalized];
            if (strpos($normalized, 'super') !== false) {
                $variants[] = 'super administrator';
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            }

            $row = $db->table('access_roles')
                ->select('id')
                ->whereIn('role_key', array_values(array_unique($variants)))
                ->where('is_active', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (int) $row['id'];
            }
        }

        return null;
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        $normalized = trim(strtolower($menuLink), '/');

        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(TRIM(link))', $normalized)
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }

    private function resolveCurrentUserEmail(): ?string
    {
        $details = $this->resolveCurrentUserEmailDetails();
        return $details['email'] ?? null;
    }

    private function resolveCurrentUserEmailDetails(): array
    {
        $username = trim((string) (session()->get('username') ?? ''));
        $fullName = trim((string) (session()->get('fullName') ?? ''));
        $userId   = (int) (session()->get('userId') ?? 0);

        $db = \Config\Database::connect();

        // 1. Check mst_pegawai by NIP or Full Name
        if ($db->tableExists('mst_pegawai')) {
            $pegawai = $db->table('mst_pegawai')
                ->select('nip, nama, email')
                ->groupStart()
                    ->where('LOWER(nip)', strtolower($username))
                    ->orWhere('LOWER(nama)', strtolower($fullName))
                ->groupEnd()
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($pegawai) && ! empty($pegawai['email'])) {
                return [
                    'email' => trim((string) $pegawai['email']),
                    'nama'  => trim((string) $pegawai['nama']) ?: $fullName,
                    'nip'   => trim((string) $pegawai['nip']) ?: $username,
                ];
            }
        }

        // 2. Check users table
        if ($userId > 0 && $db->tableExists('users') && $db->fieldExists('email', 'users')) {
            $userRow = $db->table('users')->select('email, full_name, username')->where('id', $userId)->get()->getRowArray();
            if (is_array($userRow) && ! empty($userRow['email'])) {
                return [
                    'email' => trim((string) $userRow['email']),
                    'nama'  => trim((string) ($userRow['full_name'] ?? $fullName)),
                    'nip'   => trim((string) ($userRow['username'] ?? $username)),
                ];
            }
        }

        return [
            'email' => null,
            'nama'  => $fullName ?: $username,
            'nip'   => $username,
        ];
    }

    private function logPengiriman(
        ?int $sosmedId,
        string $namaSosmed,
        int $userId,
        string $userNip,
        string $userName,
        string $emailTujuan,
        string $status,
        string $pesanStatus,
        string $ipAddress,
        string $userAgent
    ): void {
        try {
            $db = \Config\Database::connect();
            $db->table('trn_kompu_log_pengiriman')->insert([
                'sosmed_id'    => $sosmedId > 0 ? $sosmedId : null,
                'nama_sosmed'  => $namaSosmed,
                'user_id'      => $userId > 0 ? $userId : null,
                'user_nip'     => $userNip ?: null,
                'user_name'    => $userName,
                'email_tujuan' => $emailTujuan,
                'status'       => $status,
                'pesan_status' => substr($pesanStatus, 0, 500),
                'ip_address'   => $ipAddress ?: null,
                'user_agent'   => substr($userAgent, 0, 250),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menulis log pengiriman kredensial: ' . $e->getMessage());
        }
    }

    private function parseSmtpError(string $rawDebug): string
    {
        $clean = trim(strip_tags($rawDebug));

        // 1. Password or Username authentication failure
        if (str_contains($clean, '535') || stripos($clean, 'Incorrect authentication data') !== false) {
            return 'Autentikasi SMTP Gagal (Error 535: Password atau Username akun SMTP salah/tidak cocok di server mail hosting). Silakan periksa kembali password akun email di cPanel / file .env.';
        }

        // 2. Relay denied / Sender address rejected
        if (str_contains($clean, '550') || stripos($clean, 'relay') !== false) {
            return 'Alamat email pengirim atau penerima ditolak oleh server hosting (Error 550: Relay access denied).';
        }

        // 3. Timeout
        if (stripos($clean, 'timed out') !== false || stripos($clean, 'timeout') !== false) {
            return 'Koneksi ke server mail SMTP timeout (Port 465 atau Host server tidak merespon).';
        }

        // 4. Connection refused
        if (stripos($clean, 'Connection refused') !== false) {
            return 'Koneksi ke server mail ditolak (Connection refused).';
        }

        // 5. Filter out initial greeting banner lines (220, 250, hello:) and headers
        $lines = preg_split('/[\r\n]+/', $clean);
        $errorLines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, 'Date:') || str_starts_with($line, 'From:') || str_starts_with($line, 'To:') || str_starts_with($line, 'Subject:')) {
                continue;
            }
            if (preg_match('/^(220[- ]|250[- ]|hello:|<pre>)/i', $line)) {
                continue;
            }
            $errorLines[] = $line;
        }

        if (! empty($errorLines)) {
            return implode(' — ', array_slice($errorLines, 0, 2));
        }

        return substr($clean, -250);
    }

    private function buildCredentialEmailHtml(array $sosmed, string $recipientName, string $recipientNip, string $password): string
    {
        $platform = esc($sosmed['nama_sosmed']);
        $username = esc($sosmed['username'] ?: '-');
        $emailLogin = esc($sosmed['email_login'] ?: '-');
        $urlProfil = esc($sosmed['url_profil'] ?: '-');
        $metode = esc($sosmed['metode_login'] ?: 'Username & Password');
        $keterangan = esc($sosmed['keterangan'] ?: '-');
        $waktu = date('d F Y, H:i:s') . ' WIB';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Kredensial Akun Resmi</title>
        </head>
        <body style="font-family: \'Segoe UI\', Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 30px 15px; color: #1e293b;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;">
                <!-- Header -->
                <tr>
                    <td style="background: linear-gradient(135deg, #0A66C2 0%, #004182 100%); padding: 28px 24px; text-align: center; color: #ffffff;">
                        <h2 style="margin: 0 0 6px 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">SATKER PRASARANA STRATEGIS PROVINSI RIAU</h2>
                        <div style="font-size: 13px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px;">Kementerian Pekerjaan Umum • Modul KOMPU</div>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding: 28px 24px;">
                        <div style="background-color: #eff6ff; border-left: 4px solid #0A66C2; padding: 14px 18px; border-radius: 4px; margin-bottom: 22px;">
                            <strong style="color: #0A66C2; font-size: 15px; display: block; margin-bottom: 4px;">Informasi Kredensial Akun Resmi</strong>
                            <span style="font-size: 13px; color: #334155;">Halo <strong>' . esc($recipientName) . '</strong> (NIP: ' . esc($recipientNip) . '), berikut adalah data login akun publikasi resmi yang Anda minta dari sistem:</span>
                        </div>

                        <!-- Platform Box -->
                        <table width="100%" cellpadding="10" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; font-size: 14px; background-color: #f8fafc;">
                            <tr>
                                <td width="35%" style="font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0;">Platform / Media</td>
                                <td style="font-weight: 700; color: #0A66C2; border-bottom: 1px solid #e2e8f0; font-size: 16px;">' . $platform . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0;">Username Akun</td>
                                <td style="border-bottom: 1px solid #e2e8f0; font-weight: 600; font-family: monospace; font-size: 14px;">' . $username . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0;">Email Terhubung</td>
                                <td style="border-bottom: 1px solid #e2e8f0; font-family: monospace;">' . $emailLogin . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0;">Metode Akses</td>
                                <td style="border-bottom: 1px solid #e2e8f0;">' . $metode . '</td>
                            </tr>
                            ' . ($urlProfil !== '-' ? '
                            <tr>
                                <td style="font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0;">Laman Profil</td>
                                <td style="border-bottom: 1px solid #e2e8f0;"><a href="' . $urlProfil . '" target="_blank" style="color: #0A66C2; text-decoration: none;">' . $urlProfil . '</a></td>
                            </tr>' : '') . '
                        </table>

                        <!-- Password Highlight Card -->
                        <div style="text-align: center; margin: 24px 0; background: linear-gradient(to right, #f8fafc, #ffffff, #f8fafc); border: 2px dashed #0A66C2; border-radius: 10px; padding: 20px;">
                            <div style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 8px; letter-spacing: 1px;">Password Akun</div>
                            <div style="font-family: \'Consolas\', \'Courier New\', monospace; font-size: 24px; font-weight: 700; color: #0f172a; letter-spacing: 2px; user-select: all; background: #ffffff; display: inline-block; padding: 8px 24px; border-radius: 6px; border: 1px solid #cbd5e1;">' . esc($password) . '</div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 8px;">(Klik ganda atau seleksi untuk menyalin)</div>
                        </div>

                        <!-- Advisory Security Note -->
                        <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px; margin-top: 20px; font-size: 12px; color: #92400e;">
                            <strong>PENTING &amp; RAHASIA:</strong>
                            <p style="margin: 4px 0 0 0;">Kredensial ini bersifat rahasia dan diperuntukkan secara eksklusif bagi staf pengelola komunikasi publik Satker PPS Riau yang berwenang. Dilarang meneruskan atau membagikan kredensial ini kepada pihak lain.</p>
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 24px; font-size: 12px; color: #64748b; text-align: center;">
                        Email otomatis dikirim pada: <strong>' . $waktu . '</strong><br>
                        © ' . date('Y') . ' Satker Prasarana Strategis Provinsi Riau — Seluruh Hak Cipta Dilindungi.
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';
    }

    private function respondError(string $message, int $statusCode = 400): ResponseInterface|RedirectResponse
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status'  => 'error',
                'message' => $message,
            ]);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }
}
