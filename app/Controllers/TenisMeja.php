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
     * Seed initial 5 participants for all categories if not already seeded
     */
    protected function ensureInitialTournaments(): void
    {
        $defaultBalais = [
            'BWS Sumatera III',
            'BPJN Riau',
            'BPPW Riau',
            'BP2JK Riau',
            'Satker PPS Riau',
        ];

        $categories = [
            'putra'       => ['title' => 'Turnamen Tenis Meja Putra', 'prefix' => 'Peserta', 'suffix' => 'Putra'],
            'putri'       => ['title' => 'Turnamen Tenis Meja Putri', 'prefix' => 'Peserta', 'suffix' => 'Putri'],
            'ganda_putra' => ['title' => 'Turnamen Tenis Meja Ganda Putra', 'prefix' => 'Pasangan', 'suffix' => 'Ganda Putra'],
            'ganda_putri' => ['title' => 'Turnamen Tenis Meja Ganda Putri', 'prefix' => 'Pasangan', 'suffix' => 'Ganda Putri'],
        ];

        foreach ($categories as $catKey => $catConfig) {
            $existing = $this->tournamentModel->getActiveTournament($catKey);
            $needsRecreate = (! $existing) || ((int)($existing['participant_count'] ?? 0) === 5 && count($existing['bracket_json']['rounds'][1]['matches'] ?? []) !== 3);
            if ($needsRecreate) {
                $participants = (! empty($existing['participants_json']) && count($existing['participants_json']) === 5)
                    ? $existing['participants_json']
                    : [];
                if (empty($participants)) {
                    foreach ($defaultBalais as $idx => $b) {
                        $participants[] = [
                            'name'  => $catConfig['prefix'] . ' ' . ($idx + 1) . ' ' . $catConfig['suffix'],
                            'balai' => $b,
                        ];
                    }
                }
                $this->tournamentModel->createTournament($catConfig['title'], $catKey, $participants);
            }
        }
    }

    /**
     * Display Tenis Meja Tournament Bracket Page (Public - No Login Required)
     */
    public function index()
    {
        $this->ensureInitialTournaments();

        $putraTournament      = $this->tournamentModel->getActiveTournament('putra');
        $putriTournament      = $this->tournamentModel->getActiveTournament('putri');
        $gandaPutraTournament = $this->tournamentModel->getActiveTournament('ganda_putra');
        $gandaPutriTournament = $this->tournamentModel->getActiveTournament('ganda_putri');

        $homeSetting = [];
        try {
            $homeSetting = (new \App\Models\HomeSettingModel())->first() ?? [];
        } catch (\Throwable $e) {}

        $data = [
            'title'                => 'Bagan Turnamen Tenis Meja - Sistem Gugur (Putra, Putri, Ganda Putra, Ganda Putri)',
            'logoUrl'              => $this->getBrandingLogo(),
            'officialName'         => $homeSetting['official_name'] ?? 'Kementerian Pekerjaan Umum',
            'subHeader'            => 'Satuan Kerja Pelaksanaan Prasarana Permukiman Provinsi Riau',
            'putraTournament'      => $putraTournament,
            'putriTournament'      => $putriTournament,
            'gandaPutraTournament' => $gandaPutraTournament,
            'gandaPutriTournament' => $gandaPutriTournament,
            'defaultBalais'        => TenisMejaTournamentModel::$defaultBalais,
            'isLoggedIn'           => (bool) session()->get('isLoggedIn'),
            'currentUser'          => session()->get('fullName') ?? session()->get('username') ?? null,
            'userRole'             => session()->get('role') ?? null,
        ];

        return view('public/tenismeja/index', $data);
    }

    /**
     * API: Get tournament data (JSON) for a specific category or all
     */
    public function apiData()
    {
        $cat = $this->request->getGet('category');

        if ($cat) {
            $normalized = TenisMejaTournamentModel::normalizeCategory($cat);
            $tournament = $this->tournamentModel->getActiveTournament($normalized);
            return $this->response->setJSON([
                'status'     => 'success',
                'tournament' => $tournament,
            ]);
        }

        return $this->response->setJSON([
            'status'      => 'success',
            'putra'       => $this->tournamentModel->getActiveTournament('putra'),
            'putri'       => $this->tournamentModel->getActiveTournament('putri'),
            'ganda_putra' => $this->tournamentModel->getActiveTournament('ganda_putra'),
            'ganda_putri' => $this->tournamentModel->getActiveTournament('ganda_putri'),
        ]);
    }

    /**
     * API: Setup / Create new tournament bracket
     */
    public function apiSetup()
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status'  => 'error',
                'message' => 'Silakan masuk (login) terlebih dahulu untuk mengatur peserta turnamen.',
            ]);
        }

        $title = trim((string) ($this->request->getPost('title') ?? ''));
        $category = TenisMejaTournamentModel::normalizeCategory($this->request->getPost('category'));
        $participantsRaw = $this->request->getPost('participants');

        $participantList = [];
        if (is_array($participantsRaw)) {
            $participantList = $participantsRaw;
        } elseif (is_string($participantsRaw)) {
            $decoded = json_decode($participantsRaw, true);
            if (is_array($decoded)) {
                $participantList = $decoded;
            }
        }

        // Filter out empty items
        $filtered = [];
        foreach ($participantList as $p) {
            $name = '';
            $balai = '';
            if (is_array($p)) {
                $name = trim((string) ($p['name'] ?? ''));
                $balai = trim((string) ($p['balai'] ?? ''));
            } else {
                $name = trim((string) $p);
            }

            if ($name !== '' || $balai !== '') {
                $filtered[] = [
                    'name'  => $name ?: $balai,
                    'balai' => $balai ?: $name,
                ];
            }
        }

        if (count($filtered) < 2) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON([
                'status'  => 'error',
                'message' => 'Jumlah peserta minimal 2 orang.',
            ]);
        }

        try {
            $tournamentId = $this->tournamentModel->createTournament($title, $category, $filtered);
            $tournament = $this->tournamentModel->getActiveTournament($category);

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Bagan turnamen ' . strtoupper($category) . ' berhasil dibuat dengan ' . count($filtered) . ' peserta.',
                'tournament' => $tournament,
                'category'   => $category,
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
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status'  => 'error',
                'message' => 'Silakan masuk (login) terlebih dahulu untuk mengacak posisi turnamen.',
            ]);
        }

        $password = trim((string) ($this->request->getPost('password') ?? ''));
        $category = TenisMejaTournamentModel::normalizeCategory($this->request->getPost('category'));

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

        $tournament = $this->tournamentModel->getActiveTournament($category);
        if (! $tournament) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Turnamen kategori ' . strtoupper($category) . ' tidak ditemukan.',
            ]);
        }

        try {
            $result = $this->tournamentModel->shuffleParticipants((int) $tournament['id']);
            $updatedTournament = $this->tournamentModel->getActiveTournament($category);

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Posisi peserta turnamen tenis meja ' . strtoupper($category) . ' berhasil diacak!',
                'tournament' => $updatedTournament,
                'category'   => $category,
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
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status'  => 'error',
                'message' => 'Silakan masuk (login) terlebih dahulu untuk mengubah skor atau menentukan pemenang pertandingan.',
            ]);
        }

        $category = TenisMejaTournamentModel::normalizeCategory($this->request->getPost('category'));
        $tournament = $this->tournamentModel->getActiveTournament($category);

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

            $updatedTournament = $this->tournamentModel->getActiveTournament($category);

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Hasil pertandingan berhasil disimpan.',
                'tournament' => $updatedTournament,
                'category'   => $category,
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
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status'  => 'error',
                'message' => 'Silakan masuk (login) terlebih dahulu untuk mereset skor turnamen.',
            ]);
        }

        $category = TenisMejaTournamentModel::normalizeCategory($this->request->getPost('category'));
        $tournament = $this->tournamentModel->getActiveTournament($category);

        if (! $tournament) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Turnamen aktif tidak ditemukan.',
            ]);
        }

        try {
            $bracket = $this->tournamentModel->resetTournamentScores((int) $tournament['id']);
            $updatedTournament = $this->tournamentModel->getActiveTournament($category);

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Skor dan bagan turnamen ' . strtoupper($category) . ' berhasil direset.',
                'tournament' => $updatedTournament,
                'category'   => $category,
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
        $category = TenisMejaTournamentModel::normalizeCategory($this->request->getGet('category'));
        $tournament = $this->tournamentModel->getActiveTournament($category);

        if (! $tournament) {
            throw PageNotFoundException::forPageNotFound('Data turnamen tenis meja belum tersedia untuk diekspor.');
        }

        $homeSetting = [];
        try {
            $homeSetting = (new \App\Models\HomeSettingModel())->first() ?? [];
        } catch (\Throwable $e) {}

        $logoUrl = $this->getBrandingLogo();
        $logoBase64 = '';
        $logoPath = FCPATH . 'uploads/branding/1774773296_62f922407f66fb004e77.jpg';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $imgData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
        }

        $catLabel = strtoupper(TenisMejaTournamentModel::getCategoryLabel($category));
        $orientation = strtolower(trim((string) ($this->request->getGet('orientation') ?? 'landscape')));
        if ($orientation !== 'portrait') {
            $orientation = 'landscape';
        }

        $data = [
            'title'        => $tournament['title'] ?? ('Bagan Turnamen Tenis Meja ' . $catLabel),
            'category'     => $category,
            'categoryLabel'=> $catLabel,
            'tournament'   => $tournament,
            'officialName' => $homeSetting['official_name'] ?? 'KEMENTERIAN PEKERJAAN UMUM',
            'subHeader'    => 'SATUAN KERJA PELAKSANAAN PRASARANA PERMUKIMAN PROVINSI RIAU',
            'logoUrl'      => $logoUrl,
            'logoBase64'   => $logoBase64,
            'printDate'    => date('d F Y, H:i') . ' WIB',
            'orientation'  => $orientation,
        ];

        $html = view('public/tenismeja/pdf', $data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        $filename = 'Bagan_Turnamen_Tenis_Meja_' . $catLabel . '_' . ucfirst($orientation) . '_' . date('Ymd_His') . '.pdf';

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

            $users = $db->table('users')
                ->where('is_active', 1)
                ->groupStart()
                    ->whereIn('role', $superAdminRoles)
                    ->orLike('role', 'super')
                ->groupEnd()
                ->get()
                ->getResultArray();

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
