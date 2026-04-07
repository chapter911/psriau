<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class History extends BaseController
{
    public function login()
    {
        if (! db_connect()->tableExists('login_histories')) {
            return view('admin/history/login', [
                'title' => 'History Login',
                'table_ready' => false,
                'rows' => [],
                'filters' => [
                    'status' => '',
                    'username' => '',
                    'date_from' => '',
                    'date_to' => '',
                ],
                'stats' => [
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'today' => 0,
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
            'total' => (int) db_connect()->table('login_histories')->countAllResults(),
            'success' => (int) db_connect()->table('login_histories')->where('is_success', 1)->countAllResults(),
            'failed' => (int) db_connect()->table('login_histories')->where('is_success', 0)->countAllResults(),
            'today' => (int) db_connect()->table('login_histories')->where('DATE(attempted_at)', date('Y-m-d'))->countAllResults(),
        ];

        return view('admin/history/login', [
            'title' => 'History Login',
            'table_ready' => true,
            'rows' => $rows,
            'filters' => [
                'status' => $status,
                'username' => $username,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'stats' => $stats,
        ]);
    }

    public function edit()
    {
        return $this->renderByType('edit', 'History Edit');
    }

    public function delete()
    {
        return $this->renderByType('delete', 'History Delete');
    }

    private function renderByType(string $type, string $title)
    {
        if (! db_connect()->tableExists('audit_histories')) {
            return view('admin/history/list', [
                'title' => $title,
                'history_type' => $type,
                'rows' => [],
                'table_ready' => false,
            ]);
        }

        $rows = db_connect()->table('audit_histories')
            ->where('action_type', $type)
            ->orderBy('happened_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(300)
            ->get()
            ->getResultArray();

        return view('admin/history/list', [
            'title' => $title,
            'history_type' => $type,
            'rows' => $rows,
            'table_ready' => true,
        ]);
    }
}
