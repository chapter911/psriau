<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Tutorial extends BaseController
{
    public function index()
    {
        $rawSessionRole = (string) (session()->get('role') ?? '');
        $sessionRoleKey = $this->resolveRoleKey($rawSessionRole);
        $isSuperAdmin = ($sessionRoleKey === 'super_administrator');

        // Full list of role definitions
        $allRoles = [
            'all' => [
                'key' => 'all',
                'label' => 'Semua Role / Seluruh Sistem',
                'description' => 'Panduan lengkap seluruh alur kerja dan menu aplikasi bagi seluruh role pengguna.',
                'badge' => 'badge-dark',
            ],
            'super_administrator' => [
                'key' => 'super_administrator',
                'label' => 'Super Administrator',
                'description' => 'Akses penuh ke seluruh modul, konfigurasi sistem, database, dan pengelolaan hak akses role.',
                'badge' => 'badge-danger',
            ],
            'admin' => [
                'key' => 'admin',
                'label' => 'Admin Operasional',
                'description' => 'Mengelola data master, paket pekerjaan, pegawai, serta pengawasan administrasi operasional.',
                'badge' => 'badge-primary',
            ],
            'keuangan' => [
                'key' => 'keuangan',
                'label' => 'Tim Keuangan / Verifikator',
                'description' => 'Memeriksa dan memverifikasi laporan perjalanan dinas, mengunggah SPT terverifikasi, dan mencetak kuitansi & SPPD.',
                'badge' => 'badge-success',
            ],
            'ppk_kasatker' => [
                'key' => 'ppk_kasatker',
                'label' => 'PPK / Kasatker (Pejabat Penandatangan)',
                'description' => 'Memeriksa dan menyetujui disposisi perjalanan dinas serta mengesahkan dokumen dinas.',
                'badge' => 'badge-warning text-dark',
            ],
            'staf_pelaksana' => [
                'key' => 'staf_pelaksana',
                'label' => 'Staf / Pelaksana Kegiatan',
                'description' => 'Pengajuan disposisi, pelaporan kegiatan dinas, pengunggahan bukti nota/tiket & dokumentasi lapangan.',
                'badge' => 'badge-info',
            ],
        ];

        // If not Superadmin, filter allowed roles strictly to user's active role only
        if (! $isSuperAdmin) {
            $permittedRoles = [
                $allRoles[$sessionRoleKey] ?? $allRoles['staf_pelaksana'],
            ];
        } else {
            $permittedRoles = array_values($allRoles);
        }

        return view('admin/tutorial/index', [
            'title' => 'Tutorial & Flowchart Alur Kerja',
            'session_role' => $rawSessionRole !== '' ? $rawSessionRole : 'User',
            'session_role_key' => $sessionRoleKey,
            'is_super_admin' => $isSuperAdmin,
            'roles_list' => $permittedRoles,
        ]);
    }

    private function resolveRoleKey(string $sessionRole): string
    {
        $role = strtolower(trim($sessionRole));

        if (in_array($role, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true)) {
            return 'super_administrator';
        }
        if (in_array($role, ['keuangan', 'finance', 'bendahara', 'verifikator'], true)) {
            return 'keuangan';
        }
        if (in_array($role, ['ppk', 'kasatker', 'pejabat', 'penandatangan'], true)) {
            return 'ppk_kasatker';
        }
        if (in_array($role, ['admin', 'administrator'], true)) {
            return 'admin';
        }

        return 'staf_pelaksana';
    }
}
