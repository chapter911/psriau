<?php

namespace App\Controllers;

use App\Models\GateballMatchModel;
use CodeIgniter\Exceptions\PageNotFoundException;
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
     * Helper to get branding logo URL
     */
    protected function getBrandingLogo(): string
    {
        $homeSetting = (new \App\Models\HomeSettingModel())->first() ?? [];
        $appSetting  = (new \App\Models\AppSettingModel())->first() ?? [];

        $logoRaw = $homeSetting['logo_url'] ?? $appSetting['app_logo_url'] ?? '';
        return ! empty($logoRaw) ? media_url((string) $logoRaw) : base_url('uploads/branding/1774773296_62f922407f66fb004e77.jpg');
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

        $data = [
            'title'          => 'Jadwal & Klasemen Pertandingan Gateball',
            'logoUrl'        => $this->getBrandingLogo(),
            'officialName'   => $homeSetting['official_name'] ?? 'Kementerian Pekerjaan Umum',
            'putraMatches'   => $putraMatches,
            'putriMatches'   => $putriMatches,
            'putraStandings' => $putraStandings,
            'putriStandings' => $putriStandings,
        ];

        return view('public/gateball/index', $data);
    }

    /**
     * Display Live Match Scoreboard & Timer Page
     */
    public function match(int $matchId)
    {
        $match = $this->matchModel->find($matchId);
        if (! $match) {
            throw PageNotFoundException::forPageNotFound("Pertandingan Gateball #{$matchId} tidak ditemukan.");
        }

        // Set session authorization for match operator
        session()->set('gateball_authorized', true);

        $allMatches = $this->matchModel->getMatchesByCategory($match['category']);
        $homeSetting = (new \App\Models\HomeSettingModel())->first() ?? [];

        // Find prev and next match
        $prevMatch = null;
        $nextMatch = null;
        foreach ($allMatches as $idx => $m) {
            if ((int)$m['id'] === $matchId) {
                $prevMatch = $allMatches[$idx - 1] ?? null;
                $nextMatch = $allMatches[$idx + 1] ?? null;
                break;
            }
        }

        $data = [
            'title'        => 'Papan Skor & Timer Pertandingan #' . $match['match_number'] . ' (' . strtoupper($match['category']) . ')',
            'match'        => $match,
            'prevMatch'    => $prevMatch,
            'nextMatch'    => $nextMatch,
            'logoUrl'      => $this->getBrandingLogo(),
            'officialName' => $homeSetting['official_name'] ?? 'Kementerian Pekerjaan Umum',
        ];

        return view('public/gateball/match', $data);
    }

    /**
     * API: Verify Operator Auth Password
     */
    public function apiVerifyAuth()
    {
        $password = trim((string) ($this->request->getPost('password') ?? $this->request->getVar('password')));
        if ($password !== self::UPDATE_PASSWORD) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Password otorisasi salah!',
            ]);
        }

        session()->set('gateball_authorized', true);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Otorisasi berhasil diverifikasi.',
        ]);
    }

    /**
     * API: Get single match data (real-time sync)
     */
    public function apiMatchData(int $matchId)
    {
        $match = $this->matchModel->find($matchId);
        if (! $match) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Data pertandingan tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'status'    => 'success',
            'data'      => $match,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * API: Update live match timer, scores, and status
     */
    public function apiUpdateMatchLive(int $matchId)
    {
        $password = trim((string) ($this->request->getPost('password') ?? $this->request->getVar('password')));
        $isSessionAuth = (session()->get('gateball_authorized') === true);

        // Accept if password matches or session authorized
        if ($password !== self::UPDATE_PASSWORD && ! $isSessionAuth) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Password salah atau sesi belum terotorisasi.',
            ]);
        }

        $match = $this->matchModel->find($matchId);
        if (! $match) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Data pertandingan tidak ditemukan.',
            ]);
        }

        $updateData = [];

        if ($this->request->getPost('score1') !== null) {
            $val = $this->request->getPost('score1');
            $updateData['score1'] = ($val === '' ? null : (int)$val);
        }

        if ($this->request->getPost('score2') !== null) {
            $val = $this->request->getPost('score2');
            $updateData['score2'] = ($val === '' ? null : (int)$val);
        }

        if ($this->request->getPost('timer_seconds') !== null) {
            $updateData['timer_seconds'] = (int) $this->request->getPost('timer_seconds');
        }

        if ($this->request->getPost('timer_status') !== null) {
            $tStatus = $this->request->getPost('timer_status');
            if (in_array($tStatus, ['stopped', 'running', 'paused'], true)) {
                $updateData['timer_status'] = $tStatus;
            }
        }

        if ($this->request->getPost('timer_started_at') !== null) {
            $updateData['timer_started_at'] = $this->request->getPost('timer_started_at') ?: null;
        }

        if ($this->request->getPost('status') !== null) {
            $status = $this->request->getPost('status');
            if (in_array($status, ['pending', 'ongoing', 'completed'], true)) {
                $updateData['status'] = $status;
            }
        }

        if ($this->request->getPost('score_details_json') !== null) {
            $updateData['score_details_json'] = $this->request->getPost('score_details_json');
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $this->matchModel->update($matchId, $updateData);

        $updatedMatch = $this->matchModel->find($matchId);
        $standings    = $this->matchModel->getStandings($updatedMatch['category']);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Data pertandingan berhasil diperbarui.',
            'data'      => $updatedMatch,
            'standings' => $standings,
        ]);
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
        $isSessionAuth = session()->get('gateball_authorized') === true;

        if ($password !== self::UPDATE_PASSWORD && ! $isSessionAuth) {
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
        $isSessionAuth = session()->get('gateball_authorized') === true;

        if ($password !== self::UPDATE_PASSWORD && ! $isSessionAuth) {
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
        $isSessionAuth = session()->get('gateball_authorized') === true;

        if ($password !== self::UPDATE_PASSWORD && ! $isSessionAuth) {
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
            'score1'              => null,
            'score2'              => null,
            'timer_seconds'       => 1800,
            'timer_status'        => 'stopped',
            'timer_started_at'    => null,
            'score_details_json'  => null,
            'status'              => 'pending',
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Skor pertandingan berhasil direset.',
        ]);
    }
}
