<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class History extends BaseController
{
    /**
     * Activity History (Log Aktivitas Pengguna)
     * Mencatat aksi: Tambah (Create), Ubah (Edit), Hapus (Delete), dan Kunjungan Halaman (View)
     */
    public function index()
    {
        $db = db_connect('default');

        if (! $db->tableExists('audit_histories')) {
            return view('admin/history/index', [
                'title'       => 'Activity History',
                'table_ready' => false,
                'rows'        => [],
                'user_list'   => [],
                'filters'     => [
                    'type'               => '',
                    'username'           => '',
                    'exclude_user'       => '',
                    'include_superadmin' => false,
                    'module'             => '',
                    'date_from'          => '',
                    'date_to'            => '',
                ],
                'stats'       => [
                    'total'     => 0,
                    'create'    => 0,
                    'edit'      => 0,
                    'delete'    => 0,
                    'view'      => 0,
                    'today'     => 0,
                ],
            ]);
        }

        $type = strtolower(trim((string) ($this->request->getGet('type') ?? $this->request->getGet('action') ?? '')));
        $username = trim((string) $this->request->getGet('username'));
        $excludeUser = trim((string) $this->request->getGet('exclude_user'));
        $module = trim((string) $this->request->getGet('module'));
        $dateFrom = trim((string) $this->request->getGet('date_from'));
        $dateTo = trim((string) $this->request->getGet('date_to'));
        $includeSuperAdmin = $this->request->getGet('include_superadmin') === '1';

        if (! in_array($type, ['', 'all', 'data_changes', 'create', 'edit', 'delete', 'view'], true)) {
            $type = '';
        }

        if ($dateFrom !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $dateFrom = '';
        }

        if ($dateTo !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $dateTo = '';
        }

        // Helper to apply base user/role exclusions to queries
        $applyBaseUserFilters = static function ($builder) use ($includeSuperAdmin, $excludeUser) {
            if (! $includeSuperAdmin) {
                $builder->where('role !=', 'super_administrator');
            }
            if ($excludeUser !== '') {
                $builder->groupStart()
                    ->notLike('username', $excludeUser)
                    ->notLike('role', $excludeUser)
                    ->groupEnd();
            }
        };

        $table = $db->table('audit_histories');
        $applyBaseUserFilters($table);

        if ($type === 'data_changes') {
            $table->whereIn('action_type', ['create', 'add', 'edit', 'update', 'delete', 'hapus']);
        } elseif ($type === 'create') {
            $table->whereIn('action_type', ['create', 'add']);
        } elseif ($type === 'edit') {
            $table->whereIn('action_type', ['edit', 'update']);
        } elseif ($type === 'delete') {
            $table->whereIn('action_type', ['delete', 'hapus']);
        } elseif ($type === 'view') {
            $table->where('action_type', 'view');
        }

        if ($username !== '') {
            $table->groupStart()
                ->like('username', $username)
                ->orLike('role', $username)
                ->groupEnd();
        }

        if ($module !== '') {
            $table->groupStart()
                ->like('module_path', $module)
                ->orLike('table_name', $module)
                ->orLike('record_id', $module)
                ->groupEnd();
        }

        if ($dateFrom !== '') {
            $table->where('DATE(happened_at) >=', $dateFrom);
        }

        if ($dateTo !== '') {
            $table->where('DATE(happened_at) <=', $dateTo);
        }

        $rows = $table
            ->orderBy('happened_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(500)
            ->get()
            ->getResultArray();

        // Calculate stats respecting base user filters
        $getStatsBuilder = function () use ($db, $applyBaseUserFilters) {
            $builder = $db->table('audit_histories');
            $applyBaseUserFilters($builder);
            return $builder;
        };

        $stats = [
            'total'  => (int) $getStatsBuilder()->countAllResults(),
            'create' => (int) $getStatsBuilder()->whereIn('action_type', ['create', 'add'])->countAllResults(),
            'edit'   => (int) $getStatsBuilder()->whereIn('action_type', ['edit', 'update'])->countAllResults(),
            'delete' => (int) $getStatsBuilder()->whereIn('action_type', ['delete', 'hapus'])->countAllResults(),
            'view'   => (int) $getStatsBuilder()->where('action_type', 'view')->countAllResults(),
            'today'  => (int) $getStatsBuilder()->where('DATE(happened_at)', date('Y-m-d'))->countAllResults(),
        ];

        // Fetch user list for exclude options
        $userList = [];
        if ($db->tableExists('users')) {
            $userList = $db->table('users')
                ->select('username, full_name, role')
                ->orderBy('full_name', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('admin/history/index', [
            'title'       => 'Activity History',
            'table_ready' => true,
            'rows'        => $rows,
            'user_list'   => $userList,
            'filters'     => [
                'type'               => $type,
                'username'           => $username,
                'exclude_user'       => $excludeUser,
                'include_superadmin' => $includeSuperAdmin,
                'module'             => $module,
                'date_from'          => $dateFrom,
                'date_to'            => $dateTo,
            ],
            'stats'       => $stats,
        ]);
    }

    /**
     * Backward-compatible redirect for History Edit
     */
    public function edit()
    {
        return redirect()->to(site_url('admin/history?type=edit'));
    }

    /**
     * Backward-compatible redirect for History Delete
     */
    public function delete()
    {
        return redirect()->to(site_url('admin/history?type=delete'));
    }

    /**
     * Login History
     */
    public function login()
    {
        if (! db_connect()->tableExists('login_histories')) {
            return view('admin/history/login', [
                'title'       => 'History Login',
                'table_ready' => false,
                'rows'        => [],
                'filters'     => [
                    'status'    => '',
                    'username'  => '',
                    'date_from' => '',
                    'date_to'   => '',
                ],
                'stats'       => [
                    'total'   => 0,
                    'success' => 0,
                    'failed'  => 0,
                    'today'   => 0,
                ],
            ]);
        }

        $status = strtolower(trim((string) $this->request->getGet('status')));
        $username = trim((string) $this->request->getGet('username'));
        $dateFrom = trim((string) $this->request->getGet('date_from'));
        $dateTo = trim((string) $this->request->getGet('date_to'));

        if (! in_array($status, ['', 'success', 'failed'], true)) {
            $status = '';
        }

        if ($dateFrom !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $dateFrom = '';
        }

        if ($dateTo !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $dateTo = '';
        }

        $table = db_connect()->table('login_histories');
        if ($status === 'success') {
            $table->where('is_success', 1);
        } elseif ($status === 'failed') {
            $table->where('is_success', 0);
        }

        if ($username !== '') {
            $table->groupStart()
                ->like('username_input', $username)
                ->orLike('full_name', $username)
                ->groupEnd();
        }

        if ($dateFrom !== '') {
            $table->where('DATE(attempted_at) >=', $dateFrom);
        }

        if ($dateTo !== '') {
            $table->where('DATE(attempted_at) <=', $dateTo);
        }

        $rows = $table
            ->orderBy('attempted_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(500)
            ->get()
            ->getResultArray();

        $stats = [
            'total'   => (int) db_connect()->table('login_histories')->countAllResults(),
            'success' => (int) db_connect()->table('login_histories')->where('is_success', 1)->countAllResults(),
            'failed'  => (int) db_connect()->table('login_histories')->where('is_success', 0)->countAllResults(),
            'today'   => (int) db_connect()->table('login_histories')->where('DATE(attempted_at)', date('Y-m-d'))->countAllResults(),
        ];

        return view('admin/history/login', [
            'title'       => 'History Login',
            'table_ready' => true,
            'rows'        => $rows,
            'filters'     => [
                'status'    => $status,
                'username'  => $username,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
            'stats'       => $stats,
        ]);
    }
}
