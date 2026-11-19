<?php

namespace App\Controllers;

use App\Models\TenisMejaTournamentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class TenisMeja extends BaseController
{
    protected TenisMejaTournamentModel $tournamentModel;

    public function __construct()
    {
        $this->tournamentModel = new TenisMejaTournamentModel();
    }

    /**
     * Helper to get branding logo URL
     */
    protected function getBrandingLogo(): string
    {
        try {
            $homeSetting = (new \App\Models\HomeSettingModel())->first() ?? [];
            $appSetting  = (new \App\Models\AppSettingModel())->first() ?? [];

            $logoRaw = $homeSetting['logo_url'] ?? $appSetting['app_logo_url'] ?? '';
            return ! empty($logoRaw) ? media_url((string) $logoRaw) : base_url('uploads/branding/1774773296_62f922407f66fb004e77.jpg');
        } catch (\Throwable $e) {
            return base_url('uploads/branding/1774773296_62f922407f66fb004e77.jpg');
        }
    }

    /**
     * Display Tenis Meja Tournament Bracket Page (Public - No Login Required)
     */
    public function index()
    {
        $tournament = $this->tournamentModel->getActiveTournament();

        // If no tournament exists yet, initialize a default 8-participant PUPR bracket so the page is ready
        if (! $tournament) {
            $defaultBalais = TenisMejaTournamentModel::$defaultBalais;
            $tournamentId = $this->tournamentModel->createTournament(
                'Turnamen Tenis Meja Antar Balai PUPR',
                'Tunggal Putra',
                $defaultBalais
            );
            $tournament = $this->tournamentModel->getActiveTournament();
        }

        $homeSetting = [];
        try {
            $homeSetting = (new \App\Models\HomeSettingModel())->first() ?? [];
        } catch (\Throwable $e) {}

        $data = [
            'title'         => 'Bagan Turnamen Tenis Meja - Sistem Gugur',
            'logoUrl'       => $this->getBrandingLogo(),
            'officialName'  => $homeSetting['official_name'] ?? 'Kementerian Pekerjaan Umum',
            'subHeader'     => 'Satuan Kerja Pelaksanaan Prasarana Permukiman Provinsi Riau',
            'tournament'    => $tournament,
            'defaultBalais' => TenisMejaTournamentModel::$defaultBalais,
        ];

        return view('public/tenismeja/index', $data);
    }

    /**
     * API: Get tournament data (JSON)
     */
    public function apiData()
    {
        $tournament = $this->tournamentModel->getActiveTournament();
        if (! $tournament) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Belum ada turnamen yang aktif.',
            ]);
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'tournament' => $tournament,
        ]);
    }

    /**
     * API: Setup / Create new tournament bracket
     */
    public function apiSetup()
    {
        $title = trim((string) ($this->request->getPost('title') ?? 'Turnamen Tenis Meja'));
        $category = trim((string) ($this->request->getPost('category') ?? 'Tunggal Putra'));
        $participantsRaw = $this->request->getPost('participants');

        $participantList = [];
        if (is_array($participantsRaw)) {
            $participantList = $participantsRaw;
        } elseif (is_string($participantsRaw)) {
            $decoded = json_decode($participantsRaw, true);
            if (is_array($decoded)) {
                $participantList = $decoded;
            } else {
                $participantList = array_map('trim', explode("\n", $participantsRaw));
            }
        }

        // Filter out empty lines
        $participantList = array_values(array_filter(array_map('trim', $participantList), fn($p) => $p !== ''));

        if (count($participantList) < 2) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Jumlah peserta minimal 2 orang atau balai.',
            ]);
        }

        try {
            $tournamentId = $this->tournamentModel->createTournament($title, $category, $participantList);
            $tournament = $this->tournamentModel->getActiveTournament();

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Bagan turnamen berhasil dibuat dengan ' . count($participantList) . ' peserta.',
                'tournament' => $tournament,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal membuat bagan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * API: Shuffle / Acak Posisi peserta (Hanya Superadministrator)
     */
    public function apiAcakPosisi()
    {
        $password = trim((string) ($this->request->getPost('password') ?? ''));

        if ($password === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Password akun Superadministrator wajib diisi.',
            ]);
        }

        // Verify against active superadministrator accounts in database
        if (! $this->verifySuperadminPassword($password)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'  => 'error',
                'message' => 'Password Superadministrator salah! Pengacakan posisi ditolak.',
            ]);
        }

        $tournament = $this->tournamentModel->getActiveTournament();
        if (! $tournament) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Turnamen aktif tidak ditemukan.',
            ]);
        }

        try {
            $result = $this->tournamentModel->shuffleParticipants((int) $tournament['id']);
            $updatedTournament = $this->tournamentModel->getActiveTournament();

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Posisi peserta turnamen tenis meja berhasil diacak!',
                'tournament' => $updatedTournament,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal mengacak posisi: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * API: Update match score and winner
     */
    public function apiUpdateMatch()
    {
        $tournament = $this->tournamentModel->getActiveTournament();
        if (! $tournament) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Turnamen aktif tidak ditemukan.',
            ]);
        }

        $matchId = trim((string) ($this->request->getPost('match_id') ?? ''));
        $score1Raw = $this->request->getPost('score1');
        $score2Raw = $this->request->getPost('score2');
        $winnerName = trim((string) ($this->request->getPost('winner') ?? ''));

        $score1 = ($score1Raw !== null && $score1Raw !== '') ? (int) $score1Raw : null;
        $score2 = ($score2Raw !== null && $score2Raw !== '') ? (int) $score2Raw : null;

        if ($matchId === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'ID Pertandingan wajib disertakan.',
            ]);
        }

        try {
            $bracket = $this->tournamentModel->updateMatch(
                (int) $tournament['id'],
                $matchId,
                $score1,
                $score2,
                $winnerName ?: null
            );

            $updatedTournament = $this->tournamentModel->getActiveTournament();

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Hasil pertandingan berhasil disimpan.',
                'tournament' => $updatedTournament,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal memperbarui pertandingan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * API: Reset tournament scores
     */
    public function apiReset()
    {
        $tournament = $this->tournamentModel->getActiveTournament();
        if (! $tournament) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Turnamen aktif tidak ditemukan.',
            ]);
        }

        try {
            $bracket = $this->tournamentModel->resetTournamentScores((int) $tournament['id']);
            $updatedTournament = $this->tournamentModel->getActiveTournament();

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Seluruh skor dan bagan pertandingan berhasil direset.',
                'tournament' => $updatedTournament,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal mereset: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Export Tournament Bracket to Official PDF
     */
    public function exportPdf()
    {
        $tournament = $this->tournamentModel->getActiveTournament();
        if (! $tournament) {
            throw PageNotFoundException::forPageNotFound('Data turnamen tenis meja belum tersedia untuk diekspor.');
        }

        $homeSetting = [];
        try {
            $homeSetting = (new \App\Models\HomeSettingModel())->first() ?? [];
        } catch (\Throwable $e) {}

        // Convert logo to base64 if needed for dompdf
        $logoUrl = $this->getBrandingLogo();
        $logoBase64 = '';
        $logoPath = FCPATH . 'uploads/branding/1774773296_62f922407f66fb004e77.jpg';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $imgData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
        }

        $data = [
            'title'        => $tournament['title'] ?? 'Bagan Turnamen Tenis Meja',
            'tournament'   => $tournament,
            'officialName' => $homeSetting['official_name'] ?? 'KEMENTERIAN PEKERJAAN UMUM',
            'subHeader'    => 'SATUAN KERJA PELAKSANAAN PRASARANA PERMUKIMAN PROVINSI RIAU',
            'logoUrl'      => $logoUrl,
            'logoBase64'   => $logoBase64,
            'printDate'    => date('d F Y, H:i') . ' WIB',
        ];

        $html = view('public/tenismeja/pdf', $data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Bagan_Turnamen_Tenis_Meja_' . date('Ymd_His') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Check if given password matches any active superadministrator account
     */
    protected function verifySuperadminPassword(string $password): bool
    {
        $password = trim($password);
        if ($password === '') {
            return false;
        }

        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('users')) {
                return false;
            }

            $superAdminRoles = [
                'super administrator',
                'super_administrator',
                'super-admin',
                'superadmin',
            ];

            // Check active superadmin users
            $users = $db->table('users')
                ->where('is_active', 1)
                ->groupStart()
                    ->whereIn('role', $superAdminRoles)
                    ->orLike('role', 'super')
                ->groupEnd()
                ->get()
                ->getResultArray();

            // Fallback if no user role explicitly has 'super', check admin role
            if (empty($users)) {
                $users = $db->table('users')
                    ->where('is_active', 1)
                    ->whereIn('role', ['admin', 'administrator'])
                    ->get()
                    ->getResultArray();
            }

            foreach ($users as $user) {
                $hash = (string) ($user['password_hash'] ?? '');
                if ($hash !== '' && password_verify($password, $hash)) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'TenisMeja::verifySuperadminPassword error: ' . $e->getMessage());
        }

        return false;
    }
}
