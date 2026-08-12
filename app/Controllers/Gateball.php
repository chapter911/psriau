<?php

namespace App\Controllers;

use App\Models\GateballMatchModel;
use CodeIgniter\HTTP\ResponseInterface;

class Gateball extends BaseController
{
    protected GateballMatchModel $matchModel;
    private const UPDATE_PASSWORD = 'ps123';

    public function __construct()
    {
        $this->matchModel = new GateballMatchModel();
    }

    /**
     * Display Gateball Tournament Main Page
     */
    public function index()
    {
        $putraMatches   = $this->matchModel->getMatchesByCategory('putra');
        $putriMatches   = $this->matchModel->getMatchesByCategory('putri');
        $putraStandings = $this->matchModel->getStandings('putra');
        $putriStandings = $this->matchModel->getStandings('putri');

        $homeSetting = (new \App\Models\HomeSettingModel())->first() ?? [];
        $appSetting  = (new \App\Models\AppSettingModel())->first() ?? [];

        $logoRaw = $homeSetting['logo_url'] ?? $appSetting['app_logo_url'] ?? '';
        $logoUrl = ! empty($logoRaw) ? media_url((string) $logoRaw) : base_url('uploads/branding/1774773296_62f922407f66fb004e77.jpg');

        $data = [
            'title'          => 'Jadwal & Klasemen Pertandingan Gateball',
            'logoUrl'        => $logoUrl,
            'officialName'   => $homeSetting['official_name'] ?? 'Kementerian Pekerjaan Umum',
            'putraMatches'   => $putraMatches,
            'putriMatches'   => $putriMatches,
            'putraStandings' => $putraStandings,
            'putriStandings' => $putriStandings,
        ];

        return view('public/gateball/index', $data);
    }

    /**
     * API: Get latest data (matches and standings)
     */
    public function apiData()
    {
        $putraMatches   = $this->matchModel->getMatchesByCategory('putra');
        $putriMatches   = $this->matchModel->getMatchesByCategory('putri');
        $putraStandings = $this->matchModel->getStandings('putra');
        $putriStandings = $this->matchModel->getStandings('putri');

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'putra' => [
                    'matches'   => $putraMatches,
                    'standings' => $putraStandings,
                ],
                'putri' => [
                    'matches'   => $putriMatches,
                    'standings' => $putriStandings,
                ],
            ],
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * API: Update score for a single match
     */
    public function apiUpdateScore()
    {
        $password = trim((string) ($this->request->getPost('password') ?? $this->request->getVar('password')));
        if ($password !== self::UPDATE_PASSWORD) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Password salah! Masukkan password resmi untuk mengupdate skor.',
            ]);
        }

        $matchId = (int) $this->request->getPost('match_id');
        $score1  = $this->request->getPost('score1');
        $score2  = $this->request->getPost('score2');

        $match = $this->matchModel->find($matchId);
        if (! $match) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Data pertandingan tidak ditemukan.',
            ]);
        }

        // Process scores
        $updateData = [];
        if ($score1 === '' || $score1 === null || $score2 === '' || $score2 === null) {
            $updateData['score1'] = null;
            $updateData['score2'] = null;
            $updateData['status'] = 'pending';
        } else {
            $updateData['score1'] = (int) $score1;
            $updateData['score2'] = (int) $score2;
            $updateData['status'] = 'completed';
        }

        $this->matchModel->update($matchId, $updateData);

        $category  = $match['category'];
        $matches   = $this->matchModel->getMatchesByCategory($category);
        $standings = $this->matchModel->getStandings($category);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Skor pertandingan nomor ' . $match['match_number'] . ' (' . strtoupper($category) . ') berhasil diperbarui.',
            'category'  => $category,
            'matches'   => $matches,
            'standings' => $standings,
        ]);
    }

    /**
     * API: Batch update all scores for a category
     */
    public function apiBatchUpdate()
    {
        $password = trim((string) ($this->request->getPost('password') ?? $this->request->getVar('password')));
        if ($password !== self::UPDATE_PASSWORD) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Password salah! Masukkan password resmi untuk mengupdate skor.',
            ]);
        }

        $category = trim((string) $this->request->getPost('category'));
        if (! in_array($category, ['putra', 'putri'], true)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Kategori pertandingan tidak valid.',
            ]);
        }

        $scores = $this->request->getPost('scores'); // array indexed by match_id or match_number
        if (is_array($scores)) {
            foreach ($scores as $matchId => $val) {
                $score1 = $val['score1'] ?? null;
                $score2 = $val['score2'] ?? null;

                $updateData = [];
                if ($score1 === '' || $score1 === null || $score2 === '' || $score2 === null) {
                    $updateData['score1'] = null;
                    $updateData['score2'] = null;
                    $updateData['status'] = 'pending';
                } else {
                    $updateData['score1'] = (int) $score1;
                    $updateData['score2'] = (int) $score2;
                    $updateData['status'] = 'completed';
                }

                $this->matchModel->update((int) $matchId, $updateData);
            }
        }

        $matches   = $this->matchModel->getMatchesByCategory($category);
        $standings = $this->matchModel->getStandings($category);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Semua data skor pertandingan ' . strtoupper($category) . ' berhasil diperbarui.',
            'category'  => $category,
            'matches'   => $matches,
            'standings' => $standings,
        ]);
    }

    /**
     * API: Reset scores
     */
    public function apiReset()
    {
        $password = trim((string) ($this->request->getPost('password') ?? $this->request->getVar('password')));
        if ($password !== self::UPDATE_PASSWORD) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Password salah! Masukkan password resmi untuk mereset skor.',
            ]);
        }

        $category = trim((string) $this->request->getPost('category'));

        $builder = $this->matchModel->builder();
        if (in_array($category, ['putra', 'putri'], true)) {
            $builder->where('category', $category);
        }

        $builder->update([
            'score1'     => null,
            'score2'     => null,
            'status'     => 'pending',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Skor pertandingan berhasil direset.',
        ]);
    }
}
