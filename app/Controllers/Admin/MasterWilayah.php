<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MstKabupatenModel;
use App\Models\MstKecamatanModel;
use App\Models\MstKelurahanModel;
use App\Models\MstProvinsiModel;
use CodeIgniter\HTTP\RedirectResponse;

class MasterWilayah extends BaseController
{
    private const MENU_LINK_PROVINSI = 'admin/master/provinsi';
    private const MENU_LINK_KABUPATEN = 'admin/master/kabupaten';
    private const MENU_LINK_KECAMATAN = 'admin/master/kecamatan';
    private const MENU_LINK_KELURAHAN = 'admin/master/kelurahan';

    public function wilayah()
    {
        return redirect()->to('/admin/master/provinsi');
    }

    public function provinsi()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_PROVINSI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        $items = (new MstProvinsiModel())
            ->orderBy('kode_provinsi', 'ASC')
            ->findAll();

        return view('admin/master/provinsi', $this->buildPageData(self::MENU_LINK_PROVINSI, 'Master Provinsi', $items));
    }

    public function provinsiCreate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_PROVINSI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/provinsi')->with('error', 'Anda tidak memiliki akses untuk menambah data provinsi.');
        }

        $rules = [
            'kode_provinsi' => 'required|max_length[10]',
            'nama_provinsi' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/provinsi')->withInput()->with('error', 'Data provinsi belum valid.');
        }

        $model = new MstProvinsiModel();
        $kode = trim((string) $this->request->getPost('kode_provinsi'));

        if ($model->where('kode_provinsi', $kode)->countAllResults() > 0) {
            return redirect()->to('/admin/master/provinsi')->withInput()->with('error', 'Kode provinsi sudah terdaftar.');
        }

        $model->insert([
            'kode_provinsi' => $kode,
            'nama_provinsi' => trim((string) $this->request->getPost('nama_provinsi')),
        ]);

        return redirect()->to('/admin/master/provinsi')->with('message', 'Data provinsi berhasil ditambahkan.');
    }

    public function provinsiEdit(string $kode)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_PROVINSI);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/provinsi')->with('error', 'Anda tidak memiliki akses untuk mengubah data provinsi.');
        }

        $rules = [
            'kode_provinsi' => 'required|max_length[10]',
            'nama_provinsi' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/provinsi')->withInput()->with('error', 'Data provinsi belum valid.');
        }

        $model = new MstProvinsiModel();
        $existing = $model->where('kode_provinsi', $kode)->first();
        if (! is_array($existing)) {
            return redirect()->to('/admin/master/provinsi')->with('error', 'Data provinsi tidak ditemukan.');
        }

        $newKode = trim((string) $this->request->getPost('kode_provinsi'));
        if ($newKode !== $kode && $model->where('kode_provinsi', $newKode)->countAllResults() > 0) {
            return redirect()->to('/admin/master/provinsi')->withInput()->with('error', 'Kode provinsi sudah digunakan oleh data lain.');
        }

        db_connect()->table('mst_provinsi')->where('kode_provinsi', $kode)->update([
            'kode_provinsi' => $newKode,
            'nama_provinsi' => trim((string) $this->request->getPost('nama_provinsi')),
        ]);

        return redirect()->to('/admin/master/provinsi')->with('message', 'Data provinsi berhasil diperbarui.');
    }

    public function kabupaten()
    {
        $isDataTableRequest = $this->isDataTableRequest();
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KABUPATEN);
        if ($forbidden instanceof RedirectResponse) {
            if ($this->request->isAJAX() || $isDataTableRequest) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ]);
            }

            return $forbidden;
        }

        if ($this->request->isAJAX() || $isDataTableRequest) {
            return $this->kabupatenDataTable();
        }

        return view('admin/master/kabupaten', array_merge(
            $this->buildPageData(self::MENU_LINK_KABUPATEN, 'Master Kabupaten/Kota', []),
            ['provinsiOptions' => $this->provinsiOptions()]
        ));
    }

    public function kabupatenCreate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KABUPATEN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/kabupaten')->with('error', 'Anda tidak memiliki akses untuk menambah data kabupaten.');
        }

        $rules = [
            'kode_provinsi' => 'required|max_length[10]',
            'kode_kabupaten' => 'required|max_length[10]',
            'nama_kabupaten' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/kabupaten')->withInput()->with('error', 'Data kabupaten belum valid.');
        }

        $kodeProvinsi = trim((string) $this->request->getPost('kode_provinsi'));
        $kodeKabupaten = trim((string) $this->request->getPost('kode_kabupaten'));

        $exists = db_connect()->table('mst_kabupaten')
            ->where('kode_provinsi', $kodeProvinsi)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->countAllResults();

        if ($exists > 0) {
            return redirect()->to('/admin/master/kabupaten')->withInput()->with('error', 'Kode kabupaten sudah terdaftar pada provinsi ini.');
        }

        (new MstKabupatenModel())->insert([
            'kode_provinsi' => $kodeProvinsi,
            'kode_kabupaten' => $kodeKabupaten,
            'nama_kabupaten' => trim((string) $this->request->getPost('nama_kabupaten')),
        ]);

        return redirect()->to('/admin/master/kabupaten')->with('message', 'Data kabupaten berhasil ditambahkan.');
    }

    public function kabupatenEdit(string $kodeProvinsi, string $kodeKabupaten)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KABUPATEN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/kabupaten')->with('error', 'Anda tidak memiliki akses untuk mengubah data kabupaten.');
        }

        $rules = [
            'kode_provinsi' => 'required|max_length[10]',
            'kode_kabupaten' => 'required|max_length[10]',
            'nama_kabupaten' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/kabupaten')->withInput()->with('error', 'Data kabupaten belum valid.');
        }

        $db = db_connect();
        $existing = $db->table('mst_kabupaten')
            ->where('kode_provinsi', $kodeProvinsi)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->get()
            ->getRowArray();

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/kabupaten')->with('error', 'Data kabupaten tidak ditemukan.');
        }

        $newProvinsi = trim((string) $this->request->getPost('kode_provinsi'));
        $newKabupaten = trim((string) $this->request->getPost('kode_kabupaten'));

        $duplicate = $db->table('mst_kabupaten')
            ->where('kode_provinsi', $newProvinsi)
            ->where('kode_kabupaten', $newKabupaten)
            ->where('(kode_provinsi != ' . $db->escape($kodeProvinsi) . ' OR kode_kabupaten != ' . $db->escape($kodeKabupaten) . ')', null, false)
            ->countAllResults();

        if ($duplicate > 0) {
            return redirect()->to('/admin/master/kabupaten')->withInput()->with('error', 'Kode kabupaten sudah digunakan oleh data lain.');
        }

        $db->table('mst_kabupaten')
            ->where('kode_provinsi', $kodeProvinsi)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->update([
                'kode_provinsi' => $newProvinsi,
                'kode_kabupaten' => $newKabupaten,
                'nama_kabupaten' => trim((string) $this->request->getPost('nama_kabupaten')),
            ]);

        return redirect()->to('/admin/master/kabupaten')->with('message', 'Data kabupaten berhasil diperbarui.');
    }

    public function kecamatan()
    {
        $isDataTableRequest = $this->isDataTableRequest();
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KECAMATAN);
        if ($forbidden instanceof RedirectResponse) {
            if ($this->request->isAJAX() || $isDataTableRequest) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ]);
            }

            return $forbidden;
        }

        if ($this->request->isAJAX() || $isDataTableRequest) {
            return $this->kecamatanDataTable();
        }

        return view('admin/master/kecamatan', array_merge(
            $this->buildPageData(self::MENU_LINK_KECAMATAN, 'Master Kecamatan', []),
            [
                'provinsiOptions' => $this->provinsiOptions(),
                'kabupatenOptions' => $this->kabupatenOptions(),
            ]
        ));
    }

    public function kecamatanCreate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KECAMATAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/kecamatan')->with('error', 'Anda tidak memiliki akses untuk menambah data kecamatan.');
        }

        $rules = [
            'kode_provinsi' => 'required|max_length[10]',
            'kode_kabupaten' => 'required|max_length[10]',
            'kode_kecamatan' => 'required|max_length[10]',
            'nama_kecamatan' => 'required|max_length[255]',
            'kategori_konflik' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/kecamatan')->withInput()->with('error', 'Data kecamatan belum valid.');
        }

        $db = db_connect();
        $kodeProvinsi = trim((string) $this->request->getPost('kode_provinsi'));
        $kodeKabupaten = trim((string) $this->request->getPost('kode_kabupaten'));
        $kodeKecamatan = trim((string) $this->request->getPost('kode_kecamatan'));

        $exists = $db->table('mst_kecamatan')
            ->where('kode_provinsi', $kodeProvinsi)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->where('kode_kecamatan', $kodeKecamatan)
            ->countAllResults();

        if ($exists > 0) {
            return redirect()->to('/admin/master/kecamatan')->withInput()->with('error', 'Kode kecamatan sudah terdaftar pada kabupaten ini.');
        }

        (new MstKecamatanModel())->insert([
            'kode_provinsi' => $kodeProvinsi,
            'kode_kabupaten' => $kodeKabupaten,
            'kode_kecamatan' => $kodeKecamatan,
            'nama_kecamatan' => trim((string) $this->request->getPost('nama_kecamatan')),
            'kategori_konflik' => trim((string) $this->request->getPost('kategori_konflik')),
        ]);

        return redirect()->to('/admin/master/kecamatan')->with('message', 'Data kecamatan berhasil ditambahkan.');
    }

    private function kabupatenDataTable()
    {
        return $this->respondDataTable(
            fn () => db_connect()->table('mst_kabupaten k')
                ->select('k.kode_provinsi, p.nama_provinsi, k.kode_kabupaten, k.nama_kabupaten')
                ->join('mst_provinsi p', 'p.kode_provinsi = k.kode_provinsi', 'left'),
            function ($builder): void {
                $provinsi = trim((string) $this->request->getGet('filter_provinsi'));
                if ($provinsi !== '') {
                    $builder->where('k.kode_provinsi', $provinsi);
                }
            },
            ['k.kode_provinsi', 'p.nama_provinsi', 'k.kode_kabupaten', 'k.nama_kabupaten'],
            ['k.kode_provinsi', 'p.nama_provinsi', 'k.kode_kabupaten', 'k.nama_kabupaten'],
            function (array $row): array {
                if (! $this->canEditMasterDataFor(self::MENU_LINK_KABUPATEN)) {
                    return $row;
                }

                $row['action_html'] = $this->buildKabupatenActionHtml($row);

                return $row;
            }
        );
    }

    private function kecamatanDataTable()
    {
        return $this->respondDataTable(
            fn () => db_connect()->table('mst_kecamatan kc')
                ->select('kc.kode_provinsi, p.nama_provinsi, kc.kode_kabupaten, kb.nama_kabupaten, kc.kode_kecamatan, kc.nama_kecamatan, kc.kategori_konflik')
                ->join('mst_provinsi p', 'p.kode_provinsi = kc.kode_provinsi', 'left')
                ->join('mst_kabupaten kb', 'kb.kode_provinsi = kc.kode_provinsi AND kb.kode_kabupaten = kc.kode_kabupaten', 'left'),
            function ($builder): void {
                $provinsi = trim((string) $this->request->getGet('filter_provinsi'));
                $kabupaten = trim((string) $this->request->getGet('filter_kabupaten'));

                if ($provinsi !== '') {
                    $builder->where('kc.kode_provinsi', $provinsi);
                }

                if ($kabupaten !== '') {
                    $builder->where('kc.kode_kabupaten', $kabupaten);
                }
            },
            ['kc.kode_provinsi', 'p.nama_provinsi', 'kc.kode_kabupaten', 'kb.nama_kabupaten', 'kc.kode_kecamatan', 'kc.nama_kecamatan', 'kc.kategori_konflik'],
            ['kc.kode_provinsi', 'p.nama_provinsi', 'kc.kode_kabupaten', 'kb.nama_kabupaten', 'kc.kode_kecamatan', 'kc.nama_kecamatan', 'kc.kategori_konflik'],
            function (array $row): array {
                if (! $this->canEditMasterDataFor(self::MENU_LINK_KECAMATAN)) {
                    return $row;
                }

                $row['action_html'] = $this->buildKecamatanActionHtml($row);

                return $row;
            }
        );
    }

    private function kelurahanDataTable()
    {
        return $this->respondDataTable(
            fn () => db_connect()->table('mst_kelurahan kl')
                ->select('kl.kode_provinsi, p.nama_provinsi, kl.kode_kabupaten, kb.nama_kabupaten, kl.kode_kecamatan, kc.nama_kecamatan, kl.kode_kelurahan, kl.nama_kelurahan, kl.kategori_konflik')
                ->join('mst_provinsi p', 'p.kode_provinsi = kl.kode_provinsi', 'left')
                ->join('mst_kabupaten kb', 'kb.kode_provinsi = kl.kode_provinsi AND kb.kode_kabupaten = kl.kode_kabupaten', 'left')
                ->join('mst_kecamatan kc', 'kc.kode_provinsi = kl.kode_provinsi AND kc.kode_kabupaten = kl.kode_kabupaten AND kc.kode_kecamatan = kl.kode_kecamatan', 'left'),
            function ($builder): void {
                $provinsi = trim((string) $this->request->getGet('filter_provinsi'));
                $kabupaten = trim((string) $this->request->getGet('filter_kabupaten'));
                $kecamatan = trim((string) $this->request->getGet('filter_kecamatan'));

                if ($provinsi !== '') {
                    $builder->where('kl.kode_provinsi', $provinsi);
                }

                if ($kabupaten !== '') {
                    $builder->where('kl.kode_kabupaten', $kabupaten);
                }

                if ($kecamatan !== '') {
                    $builder->where('kl.kode_kecamatan', $kecamatan);
                }
            },
            ['kl.kode_provinsi', 'p.nama_provinsi', 'kl.kode_kabupaten', 'kb.nama_kabupaten', 'kl.kode_kecamatan', 'kc.nama_kecamatan', 'kl.kode_kelurahan', 'kl.nama_kelurahan', 'kl.kategori_konflik'],
            ['kl.kode_provinsi', 'p.nama_provinsi', 'kl.kode_kabupaten', 'kb.nama_kabupaten', 'kl.kode_kecamatan', 'kc.nama_kecamatan', 'kl.kode_kelurahan', 'kl.nama_kelurahan', 'kl.kategori_konflik'],
            function (array $row): array {
                if (! $this->canEditMasterDataFor(self::MENU_LINK_KELURAHAN)) {
                    return $row;
                }

                $row['action_html'] = $this->buildKelurahanActionHtml($row);

                return $row;
            }
        );
    }

    private function respondDataTable(callable $queryFactory, callable $filterApplier, array $searchColumns, array $orderColumns, ?callable $rowMapper = null)
    {
        try {
            $draw = $this->getDataTableDraw();
            $start = $this->getDataTableStart();
            $length = $this->getDataTableLength();
            $search = $this->getDataTableSearchTerm();
            $orderIndex = $this->getDataTableOrderColumnIndex();
            $orderDirection = $this->getDataTableOrderDirection();

            $totalBuilder = $queryFactory();
            $filterApplier($totalBuilder);
            $recordsTotal = (int) $totalBuilder->countAllResults(false);

            $filteredBuilder = $queryFactory();
            $filterApplier($filteredBuilder);
            $this->applyDataTableSearch($filteredBuilder, $searchColumns, $search);
            $recordsFiltered = (int) $filteredBuilder->countAllResults(false);

            $orderColumn = $orderColumns[$orderIndex] ?? $orderColumns[0] ?? '';
            if ($orderColumn !== '') {
                $filteredBuilder->orderBy($orderColumn, $orderDirection);
            }

            $rows = $filteredBuilder->limit($length, $start)->get()->getResultArray();

            if ($rowMapper !== null) {
                $rows = array_map($rowMapper, $rows);
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $rows,
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'DataTable master wilayah gagal dimuat: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setJSON([
                'draw' => $this->getDataTableDraw(),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Gagal memuat data wilayah.',
            ]);
        }
    }

    private function applyDataTableSearch($builder, array $columns, string $searchTerm): void
    {
        $searchTerm = trim($searchTerm);
        if ($searchTerm === '' || $columns === []) {
            return;
        }

        $builder->groupStart();
        foreach ($columns as $index => $column) {
            if ($index === 0) {
                $builder->like($column, $searchTerm);
                continue;
            }

            $builder->orLike($column, $searchTerm);
        }
        $builder->groupEnd();
    }

    private function getDataTableDraw(): int
    {
        return max(0, (int) $this->request->getGet('draw'));
    }

    private function getDataTableStart(): int
    {
        return max(0, (int) $this->request->getGet('start'));
    }

    private function getDataTableLength(): int
    {
        $length = (int) $this->request->getGet('length');

        return $length > 0 ? $length : 10;
    }

    private function getDataTableSearchTerm(): string
    {
        $search = $this->request->getGet('search');
        if (! is_array($search)) {
            return '';
        }

        return trim((string) ($search['value'] ?? ''));
    }

    private function getDataTableOrderColumnIndex(): int
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 0;
        }

        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 0;
        }

        return max(0, (int) ($first['column'] ?? 0));
    }

    private function getDataTableOrderDirection(): string
    {
        $order = $this->request->getGet('order');
        if (! is_array($order) || $order === []) {
            return 'ASC';
        }

        $first = $order[0] ?? [];
        if (! is_array($first)) {
            return 'ASC';
        }

        return strtolower((string) ($first['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
    }

    private function isDataTableRequest(): bool
    {
        return $this->request->getGet('draw') !== null
            && $this->request->getGet('start') !== null
            && $this->request->getGet('length') !== null;
    }

    private function canEditMasterDataFor(string $menuLink): bool
    {
        static $cache = [];

        if (! array_key_exists($menuLink, $cache)) {
            $cache[$menuLink] = $this->canManageMasterData() && (bool) ($this->resolveMenuPermissions($menuLink)['edit'] ?? false);
        }

        return (bool) $cache[$menuLink];
    }

    private function buildKabupatenActionHtml(array $row): string
    {
        $kodeProvinsi = htmlspecialchars((string) ($row['kode_provinsi'] ?? ''), ENT_QUOTES, 'UTF-8');
        $kodeKabupaten = htmlspecialchars((string) ($row['kode_kabupaten'] ?? ''), ENT_QUOTES, 'UTF-8');
        $namaKabupaten = htmlspecialchars((string) ($row['nama_kabupaten'] ?? ''), ENT_QUOTES, 'UTF-8');

        return '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-ubah-kabupaten" data-kode-provinsi="' . $kodeProvinsi . '" data-kode-kabupaten="' . $kodeKabupaten . '" data-nama-kabupaten="' . $namaKabupaten . '">UBAH</button>';
    }

    private function buildKecamatanActionHtml(array $row): string
    {
        $kodeProvinsi = htmlspecialchars((string) ($row['kode_provinsi'] ?? ''), ENT_QUOTES, 'UTF-8');
        $kodeKabupaten = htmlspecialchars((string) ($row['kode_kabupaten'] ?? ''), ENT_QUOTES, 'UTF-8');
        $kodeKecamatan = htmlspecialchars((string) ($row['kode_kecamatan'] ?? ''), ENT_QUOTES, 'UTF-8');
        $namaKecamatan = htmlspecialchars((string) ($row['nama_kecamatan'] ?? ''), ENT_QUOTES, 'UTF-8');
        $kategoriKonflik = htmlspecialchars((string) ($row['kategori_konflik'] ?? ''), ENT_QUOTES, 'UTF-8');

        return '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-ubah-kecamatan" data-kode-provinsi="' . $kodeProvinsi . '" data-kode-kabupaten="' . $kodeKabupaten . '" data-kode-kecamatan="' . $kodeKecamatan . '" data-nama-kecamatan="' . $namaKecamatan . '" data-kategori-konflik="' . $kategoriKonflik . '">UBAH</button>';
    }

    private function buildKelurahanActionHtml(array $row): string
    {
        $kodeProvinsi = htmlspecialchars((string) ($row['kode_provinsi'] ?? ''), ENT_QUOTES, 'UTF-8');
        $kodeKabupaten = htmlspecialchars((string) ($row['kode_kabupaten'] ?? ''), ENT_QUOTES, 'UTF-8');
        $kodeKecamatan = htmlspecialchars((string) ($row['kode_kecamatan'] ?? ''), ENT_QUOTES, 'UTF-8');
        $kodeKelurahan = htmlspecialchars((string) ($row['kode_kelurahan'] ?? ''), ENT_QUOTES, 'UTF-8');
        $namaKelurahan = htmlspecialchars((string) ($row['nama_kelurahan'] ?? ''), ENT_QUOTES, 'UTF-8');
        $kategoriKonflik = htmlspecialchars((string) ($row['kategori_konflik'] ?? ''), ENT_QUOTES, 'UTF-8');

        return '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-ubah-kelurahan" data-kode-provinsi="' . $kodeProvinsi . '" data-kode-kabupaten="' . $kodeKabupaten . '" data-kode-kecamatan="' . $kodeKecamatan . '" data-kode-kelurahan="' . $kodeKelurahan . '" data-nama-kelurahan="' . $namaKelurahan . '" data-kategori-konflik="' . $kategoriKonflik . '">UBAH</button>';
    }

    public function kecamatanEdit(string $kodeProvinsi, string $kodeKabupaten, string $kodeKecamatan)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KECAMATAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/kecamatan')->with('error', 'Anda tidak memiliki akses untuk mengubah data kecamatan.');
        }

        $rules = [
            'kode_provinsi' => 'required|max_length[10]',
            'kode_kabupaten' => 'required|max_length[10]',
            'kode_kecamatan' => 'required|max_length[10]',
            'nama_kecamatan' => 'required|max_length[255]',
            'kategori_konflik' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/kecamatan')->withInput()->with('error', 'Data kecamatan belum valid.');
        }

        $db = db_connect();
        $existing = $db->table('mst_kecamatan')
            ->where('kode_provinsi', $kodeProvinsi)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->where('kode_kecamatan', $kodeKecamatan)
            ->get()
            ->getRowArray();

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/kecamatan')->with('error', 'Data kecamatan tidak ditemukan.');
        }

        $newProvinsi = trim((string) $this->request->getPost('kode_provinsi'));
        $newKabupaten = trim((string) $this->request->getPost('kode_kabupaten'));
        $newKecamatan = trim((string) $this->request->getPost('kode_kecamatan'));

        $duplicate = $db->table('mst_kecamatan')
            ->where('kode_provinsi', $newProvinsi)
            ->where('kode_kabupaten', $newKabupaten)
            ->where('kode_kecamatan', $newKecamatan)
            ->where('(kode_provinsi != ' . $db->escape($kodeProvinsi) . ' OR kode_kabupaten != ' . $db->escape($kodeKabupaten) . ' OR kode_kecamatan != ' . $db->escape($kodeKecamatan) . ')', null, false)
            ->countAllResults();

        if ($duplicate > 0) {
            return redirect()->to('/admin/master/kecamatan')->withInput()->with('error', 'Kode kecamatan sudah digunakan oleh data lain.');
        }

        $db->table('mst_kecamatan')
            ->where('kode_provinsi', $kodeProvinsi)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->where('kode_kecamatan', $kodeKecamatan)
            ->update([
                'kode_provinsi' => $newProvinsi,
                'kode_kabupaten' => $newKabupaten,
                'kode_kecamatan' => $newKecamatan,
                'nama_kecamatan' => trim((string) $this->request->getPost('nama_kecamatan')),
                'kategori_konflik' => trim((string) $this->request->getPost('kategori_konflik')),
            ]);

        return redirect()->to('/admin/master/kecamatan')->with('message', 'Data kecamatan berhasil diperbarui.');
    }

    public function kelurahan()
    {
        $isDataTableRequest = $this->isDataTableRequest();
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KELURAHAN);
        if ($forbidden instanceof RedirectResponse) {
            if ($this->request->isAJAX() || $isDataTableRequest) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ]);
            }

            return $forbidden;
        }

        if ($this->request->isAJAX() || $isDataTableRequest) {
            return $this->kelurahanDataTable();
        }

        return view('admin/master/kelurahan', array_merge(
            $this->buildPageData(self::MENU_LINK_KELURAHAN, 'Master Kelurahan', []),
            [
                'provinsiOptions' => $this->provinsiOptions(),
                'kabupatenOptions' => $this->kabupatenOptions(),
                'kecamatanOptions' => $this->kecamatanOptions(),
            ]
        ));
    }

    public function kelurahanCreate()
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KELURAHAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/kelurahan')->with('error', 'Anda tidak memiliki akses untuk menambah data kelurahan.');
        }

        $rules = [
            'kode_provinsi' => 'required|max_length[10]',
            'kode_kabupaten' => 'required|max_length[10]',
            'kode_kecamatan' => 'required|max_length[10]',
            'kode_kelurahan' => 'required|max_length[10]',
            'nama_kelurahan' => 'required|max_length[255]',
            'kategori_konflik' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/kelurahan')->withInput()->with('error', 'Data kelurahan belum valid.');
        }

        $db = db_connect();
        $kodeProvinsi = trim((string) $this->request->getPost('kode_provinsi'));
        $kodeKabupaten = trim((string) $this->request->getPost('kode_kabupaten'));
        $kodeKecamatan = trim((string) $this->request->getPost('kode_kecamatan'));
        $kodeKelurahan = trim((string) $this->request->getPost('kode_kelurahan'));

        $exists = $db->table('mst_kelurahan')
            ->where('kode_provinsi', $kodeProvinsi)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->where('kode_kecamatan', $kodeKecamatan)
            ->where('kode_kelurahan', $kodeKelurahan)
            ->countAllResults();

        if ($exists > 0) {
            return redirect()->to('/admin/master/kelurahan')->withInput()->with('error', 'Kode kelurahan sudah terdaftar pada kecamatan ini.');
        }

        (new MstKelurahanModel())->insert([
            'kode_provinsi' => $kodeProvinsi,
            'kode_kabupaten' => $kodeKabupaten,
            'kode_kecamatan' => $kodeKecamatan,
            'kode_kelurahan' => $kodeKelurahan,
            'nama_kelurahan' => trim((string) $this->request->getPost('nama_kelurahan')),
            'kategori_konflik' => trim((string) $this->request->getPost('kategori_konflik')),
        ]);

        return redirect()->to('/admin/master/kelurahan')->with('message', 'Data kelurahan berhasil ditambahkan.');
    }

    public function kelurahanEdit(string $kodeProvinsi, string $kodeKabupaten, string $kodeKecamatan, string $kodeKelurahan)
    {
        $forbidden = $this->denyIfNoMenuAccess(self::MENU_LINK_KELURAHAN);
        if ($forbidden instanceof RedirectResponse) {
            return $forbidden;
        }

        if (! $this->canManageMasterData()) {
            return redirect()->to('/admin/master/kelurahan')->with('error', 'Anda tidak memiliki akses untuk mengubah data kelurahan.');
        }

        $rules = [
            'kode_provinsi' => 'required|max_length[10]',
            'kode_kabupaten' => 'required|max_length[10]',
            'kode_kecamatan' => 'required|max_length[10]',
            'kode_kelurahan' => 'required|max_length[10]',
            'nama_kelurahan' => 'required|max_length[255]',
            'kategori_konflik' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/master/kelurahan')->withInput()->with('error', 'Data kelurahan belum valid.');
        }

        $db = db_connect();
        $existing = $db->table('mst_kelurahan')
            ->where('kode_provinsi', $kodeProvinsi)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->where('kode_kecamatan', $kodeKecamatan)
            ->where('kode_kelurahan', $kodeKelurahan)
            ->get()
            ->getRowArray();

        if (! is_array($existing)) {
            return redirect()->to('/admin/master/kelurahan')->with('error', 'Data kelurahan tidak ditemukan.');
        }

        $newProvinsi = trim((string) $this->request->getPost('kode_provinsi'));
        $newKabupaten = trim((string) $this->request->getPost('kode_kabupaten'));
        $newKecamatan = trim((string) $this->request->getPost('kode_kecamatan'));
        $newKelurahan = trim((string) $this->request->getPost('kode_kelurahan'));

        $duplicate = $db->table('mst_kelurahan')
            ->where('kode_provinsi', $newProvinsi)
            ->where('kode_kabupaten', $newKabupaten)
            ->where('kode_kecamatan', $newKecamatan)
            ->where('kode_kelurahan', $newKelurahan)
            ->where('(kode_provinsi != ' . $db->escape($kodeProvinsi) . ' OR kode_kabupaten != ' . $db->escape($kodeKabupaten) . ' OR kode_kecamatan != ' . $db->escape($kodeKecamatan) . ' OR kode_kelurahan != ' . $db->escape($kodeKelurahan) . ')', null, false)
            ->countAllResults();

        if ($duplicate > 0) {
            return redirect()->to('/admin/master/kelurahan')->withInput()->with('error', 'Kode kelurahan sudah digunakan oleh data lain.');
        }

        $db->table('mst_kelurahan')
            ->where('kode_provinsi', $kodeProvinsi)
            ->where('kode_kabupaten', $kodeKabupaten)
            ->where('kode_kecamatan', $kodeKecamatan)
            ->where('kode_kelurahan', $kodeKelurahan)
            ->update([
                'kode_provinsi' => $newProvinsi,
                'kode_kabupaten' => $newKabupaten,
                'kode_kecamatan' => $newKecamatan,
                'kode_kelurahan' => $newKelurahan,
                'nama_kelurahan' => trim((string) $this->request->getPost('nama_kelurahan')),
                'kategori_konflik' => trim((string) $this->request->getPost('kategori_konflik')),
            ]);

        return redirect()->to('/admin/master/kelurahan')->with('message', 'Data kelurahan berhasil diperbarui.');
    }

    private function buildPageData(string $menuLink, string $pageTitle, array $items): array
    {
        $menuPermissions = $this->resolveMenuPermissions($menuLink);

        $canManage = $this->canManageMasterData();

        return [
            'pageTitle' => $pageTitle,
            'items' => $items,
            'can_add' => $canManage && (bool) ($menuPermissions['add'] ?? false),
            'can_edit' => $canManage && (bool) ($menuPermissions['edit'] ?? false),
        ];
    }

    private function resolveMenuPermissions(string $menuLink): array
    {
        $default = [
            'add' => false,
            'edit' => false,
            'delete' => false,
            'export' => false,
            'import' => false,
            'approval' => false,
        ];

        $db = db_connect();
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
            'add' => (bool) ((int) ($row['FiturAdd'] ?? 0)),
            'edit' => (bool) ((int) ($row['FiturEdit'] ?? 0)),
            'delete' => (bool) ((int) ($row['FiturDelete'] ?? 0)),
            'export' => (bool) ((int) ($row['FiturExport'] ?? 0)),
            'import' => (bool) ((int) ($row['FiturImport'] ?? 0)),
            'approval' => (bool) ((int) ($row['FiturApproval'] ?? 0)),
        ];
    }

    private function provinsiOptions(): array
    {
        return (new MstProvinsiModel())
            ->orderBy('kode_provinsi', 'ASC')
            ->findAll();
    }

    private function kabupatenOptions(): array
    {
        return (new MstKabupatenModel())
            ->orderBy('kode_provinsi', 'ASC')
            ->orderBy('kode_kabupaten', 'ASC')
            ->findAll();
    }

    private function kecamatanOptions(): array
    {
        return (new MstKecamatanModel())
            ->orderBy('kode_provinsi', 'ASC')
            ->orderBy('kode_kabupaten', 'ASC')
            ->orderBy('kode_kecamatan', 'ASC')
            ->findAll();
    }

    private function canManageMasterData(): bool
    {
        $role = strtolower(trim((string) session()->get('role')));

        return in_array($role, ['admin', 'super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
    }

    private function denyIfNoMenuAccess(string $menuLink): ?RedirectResponse
    {
        if ($this->hasMenuAccess($menuLink)) {
            return null;
        }

        return redirect()->to('/forbidden?from=' . rawurlencode($menuLink));
    }

    private function hasMenuAccess(string $menuLink): bool
    {
        $db = db_connect();
        if (! $db->tableExists('menu_akses')) {
            return true;
        }

        $roleId = $this->resolveRoleId((string) session()->get('role'), $db);
        if ($roleId === null) {
            return false;
        }

        $menuId = $this->resolveMenuIdByLink($menuLink, $db);
        if ($menuId === null) {
            return false;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        return (int) $db->table('menu_akses')
            ->where($roleColumn, $roleId)
            ->where('menu_id', $menuId)
            ->countAllResults() > 0;
    }

    private function resolveRoleId(string $role, $db): ?int
    {
        $normalized = strtolower(trim($role));
        if ($normalized === '') {
            return null;
        }

        if ($db->tableExists('access_roles')) {
            $variants = [$normalized];
            if ($normalized === 'super administrator') {
                $variants[] = 'super_administrator';
                $variants[] = 'super-admin';
                $variants[] = 'superadmin';
            } elseif ($normalized === 'super_administrator' || $normalized === 'super-admin' || $normalized === 'superadmin') {
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

        return match ($normalized) {
            'admin' => 1,
            'editor' => 2,
            default => null,
        };
    }

    private function resolveMenuIdByLink(string $menuLink, $db): ?string
    {
        foreach (['menu_lv3', 'menu_lv2', 'menu_lv1'] as $table) {
            if (! $db->tableExists($table) || ! $db->fieldExists('link', $table)) {
                continue;
            }

            $row = $db->table($table)
                ->select('id')
                ->where('LOWER(link)', strtolower(trim($menuLink)))
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if (is_array($row) && isset($row['id'])) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
