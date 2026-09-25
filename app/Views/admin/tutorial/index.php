<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .tutorial-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .role-badge-pill {
        cursor: pointer;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .role-badge-pill:hover {
        transform: translateY(-2px);
    }

    .role-badge-pill.active {
        border-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.4);
    }

    .flowchart-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .flowchart-card__header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 20px;
    }

    .flowchart-card__body {
        padding: 24px;
        overflow-x: auto;
    }

    .mermaid-container {
        display: flex;
        justify-content: center;
        background: #fafafa;
        border-radius: 8px;
        padding: 16px;
        border: 1px dashed #cbd5e1;
    }

    .step-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    .step-item {
        position: relative;
        padding-left: 36px;
        margin-bottom: 16px;
    }

    .step-item:last-child {
        margin-bottom: 0;
    }

    .step-number {
        position: absolute;
        left: 0;
        top: 0;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #0A66C2;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.78rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .menu-tutorial-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .menu-tutorial-card:hover {
        border-color: #0A66C2;
        box-shadow: 0 4px 12px rgba(10, 102, 194, 0.1);
    }

    .search-tutorial-input {
        border-radius: 20px;
        padding-left: 40px;
    }
</style>

<?php
    // Helper function to check if a section should be rendered for current user
    $canRenderForUser = static function (array $allowedRoles) use ($is_super_admin, $session_role_key): bool {
        if ($is_super_admin) {
            return true;
        }

        return in_array($session_role_key, $allowedRoles, true);
    };
?>

<div class="container-fluid">
    <!-- Header Banner -->
    <div class="tutorial-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="font-weight-bold mb-1"><i class="fas fa-sitemap mr-2 text-warning"></i> Tutorial & Flowchart Sistem</h2>
                <p class="text-light mb-0" style="opacity: 0.9;">
                    Panduan alur kerja dan standar operasional khusus untuk role <strong><?= esc(ucwords($session_role)); ?></strong>.
                </p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <div class="bg-white text-dark p-2 px-3 rounded-pill d-inline-block font-weight-bold shadow-sm">
                    <i class="fas fa-user-shield text-primary mr-1"></i> Role Aktif: <span class="badge badge-primary text-uppercase"><?= esc($session_role); ?></span>
                </div>
            </div>
        </div>

        <hr style="border-top: 1px solid rgba(255,255,255,0.15);" class="my-3">

        <!-- Role Filter Selector (Superadmin sees all filters, other roles only see their own active role badge) -->
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <?php if ($is_super_admin): ?>
                <span class="mr-2 font-weight-bold text-light" style="font-size: 0.85rem;"><i class="fas fa-filter mr-1"></i> Filter Berdasarkan Role (Superadmin View):</span>
                <?php foreach ($roles_list as $roleItem): ?>
                    <button type="button" 
                            class="role-badge-pill btn btn-sm <?= $roleItem['badge']; ?> role-filter-btn <?= $roleItem['key'] === 'all' ? 'active' : ''; ?>" 
                            data-role="<?= esc($roleItem['key']); ?>">
                        <?= esc($roleItem['label']); ?>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="mr-2 font-weight-bold text-light" style="font-size: 0.85rem;"><i class="fas fa-lock mr-1"></i> Tampilan Alur Khusus Role Anda:</span>
                <?php foreach ($roles_list as $roleItem): ?>
                    <span class="role-badge-pill btn btn-sm <?= $roleItem['badge']; ?> active">
                        <i class="fas fa-check-circle mr-1"></i> <?= esc($roleItem['label']); ?>
                    </span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Search Box -->
    <div class="card mb-4 border-0 shadow-sm" style="border-radius: 10px;">
        <div class="card-body py-3">
            <div class="position-relative">
                <i class="fas fa-search position-absolute text-muted" style="left: 16px; top: 12px;"></i>
                <input type="text" id="tutorialSearchInput" class="form-control form-control-sm search-tutorial-input" placeholder="Cari tutorial menu atau alur kerja Anda...">
            </div>
        </div>
    </div>

    <!-- MAIN SECTION: FLOWCHART VISUAL DIAGRAMS -->
    <h4 class="font-weight-bold text-dark mb-3"><i class="fas fa-diagram-project text-primary mr-2"></i> Diagram Flowchart Alur Kerja</h4>

    <!-- FLOWCHART 0: DASHBOARD - KALENDER TERPADU KEGIATAN & LIBUR -->
    <div class="flowchart-card role-section" data-roles="all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-primary"><i class="fas fa-calendar-days mr-2"></i> Alur Pemantauan Kalender Terpadu di Dashboard</h5>
                <small class="text-muted">Monitoring jadwal hari libur nasional, cuti bersama, pegawai cuti, dan perjalanan dinas terintegrasi via FullCalendar.</small>
            </div>
            <div>
                <span class="badge badge-primary">Semua Role</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A[Buka Halaman Dashboard Admin] --> B[Lihat Kalender Terpadu FullCalendar]
    B --> C{Filter Tampilan Kategori}
    C -->|Merah| D[Hari Libur Nasional]
    C -->|Oranye/Amber| E[Cuti Bersama]
    C -->|Biru/Cyan| F[Pegawai Sedang Cuti]
    C -->|Hijau Emerald| G[Pegawai Sedang Perjalanan Dinas]
    B --> H[Klik Jadwal / Tanggal Tertentu]
    H --> I[Muncul Modal Detail: Nama Pegawai, NIP, Jabatan, Tanggal & Tujuan]
    I --> J[Akses Tombol Pintas ke Modul Terkait]
                </pre>
            </div>
        </div>
    </div>

    <!-- FLOWCHART 0B: PETA SEBARAN SEKOLAH & FITUR EXPORT PETA A3 -->
    <div class="flowchart-card role-section" data-roles="all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-primary"><i class="fas fa-map-marked-alt mr-2"></i> Alur Pemetaan Sekolah &amp; Fitur Export Peta A3</h5>
                <small class="text-muted">Navigasi peta sebaran sekolah, filter lokasi/paket proyek, kustomisasi judul export peta, dan unduh dokumen kartografi A3 Landscape.</small>
            </div>
            <div>
                <span class="badge badge-primary">Semua Role</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A["Buka Menu Map (/admin/map)"] --> B["Eksplorasi Peta Interaktif &amp; Gunakan Filter (Tipe Map, NPSN, Wilayah, Paket)"]
    B --> C["Opsi 1: Klik Tombol 'Export Peta A3' di Header Halaman"]
    B --> D["Opsi 2: Klik Marker Sekolah &amp; Buka Modal Detail Sekolah"]
    D --> E["Klik Tombol 'Export Peta A3' pada Modal Detail"]
    C --> F["Muncul 1 Modal Dialog Terpadu 'Export Peta A3'"]
    E --> F
    F --> G["Kolom Judul Peta (Default: 'PETA SEBARAN SEKOLAH RAKYAT PROVINSI RIAU' / Kustom)"]
    G --> H{"Pilih Langsung Format Kartografi dalam Dialog yang Sama"}
    H -->|Tombol Hijau| I["Dengan Kontur Elevasi Topografi"]
    H -->|Tombol Abu-abu| J["Tanpa Kontur (Peta Bersih)"]
    I --> K["Sistem Merender Canvas &amp; Menyusun Layout A3 Landscape"]
    J --> K
    K --> L["Dokumen PDF Berhasil Diterbitkan: Buka di Tab Baru / Unduh Otomatis"]
                </pre>
            </div>
        </div>
    </div>

    <!-- FLOWCHART 0C: MASTER TANGGAL MERAH & REKOMENDASI AMBIL CUTI -->
    <?php if ($canRenderForUser(['admin', 'super_administrator'])): ?>
    <div class="flowchart-card role-section" data-roles="admin,super_administrator,all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-primary"><i class="fas fa-calendar-alt mr-2"></i> Alur Master Tanggal Merah, Sinkronisasi API &amp; Rekomendasi Ambil Cuti</h5>
                <small class="text-muted">Manajemen kalender hari libur nasional SKB 3 Menteri, sinkronisasi API antar-tahun, serta fitur rekomendasi ambil cuti (Harpitnas Optimizer) terhubung ke formulir cuti.</small>
            </div>
            <div>
                <span class="badge badge-primary">Admin &amp; Super Admin</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A["Buka Menu Master Tanggal Merah (/admin/master/tanggal-merah)"] --> B["Pilih Tahun Kalender (Toolbar / Modal)"]
    B --> C1["Tarik Data API / SKB 3 Menteri"]
    B --> C2["Eksplorasi Kalender Interaktif 12 Bulan"]
    B --> C3["Buka Fitur Rekomendasi Ambil Cuti"]

    C1 --> D1["Sistem Cek API Live / Fallback Otomatis SKB 3 Menteri (misal 2027: 18 Libur &amp; 8 Cuti Bersama)"]
    D1 --> D2["Pratinjau Data Baru vs Data Tersimpan di Modal"]
    D2 --> D3["Klik Simpan Data ke Database Satker"]

    C3 --> E1["Klik Tombol 'Rekomendasi Cuti' (Badge Indikator Total Peluang Emas)"]
    E1 --> E2["Sistem Menganalisis Harpitnas (Hari Kejepit) &amp; Jembatan Libur Terpanjang"]
    E2 --> E3["Modal Rekomendasi Cuti: Metrik Total Libur, Hemat Hari Cuti &amp; Efisiensi Multiplier"]
    E3 --> E4["Visual Timeline Strip (Hari Libur, Cuti Bersama, Weekend, &amp; Target Cuti)"]
    E4 --> E5["Klik Tombol 'Ajukan Cuti Ini' -> Otomatis Navigasi ke Form Pengajuan Cuti (/admin/surat/cuti)"]

    C2 --> F1["Indikator Khusus Sel Kalender: Ikon Bohlam 💡 &amp; Border Emas pada Hari Kejepit"]
    F1 --> F2["Klik Tanggal Harpitnas -> Modal Rincian Efisiensi &amp; Shortcut Ajukan Cuti"]
                </pre>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FLOWCHART 1: STAF / PELAKSANA - PENGAJUAN DISPOSISI & PELAPORAN PERJADIN -->
    <?php if ($canRenderForUser(['staf_pelaksana'])): ?>
    <div class="flowchart-card role-section" data-roles="staf_pelaksana,all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-info"><i class="fas fa-user-edit mr-2"></i> Alur Kerja Staf / Pelaksana Kegiatan Perjalanan Dinas</h5>
                <small class="text-muted">Proses dari pengajuan disposisi awal hingga penguncian laporan hasil kegiatan & bukti tiket/nota.</small>
            </div>
            <div>
                <span class="badge badge-info">Staf / Pelaksana</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A[Buka Menu Disposisi Perjadin] --> B[Klik Tambah Disposisi]
    B --> C[Isi Kota Tujuan, Tanggal & Maksud Perjadin]
    C --> D[Kirim Disposisi ke PPK/Kasatker]
    D --> E{Tunggu Approval Pejabat}
    E -->|Approved| F[Buka Menu Laporan Perjadin]
    F --> G[Isi Seluruh Data Tab Umum Terlebih Dahulu]
    G --> H[Unggah Foto Kegiatan & Dokumentasi Tiket/Nota di Tab Berikutnya]
    H --> I[Klik Simpan Final untuk Kunci Laporan]
                </pre>
            </div>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle mr-1"></i> <strong>Catatan Staf:</strong> Pada menu Laporan Perjalanan Dinas, Anda hanya dapat melihat data laporan yang melibatkan Anda sebagai pelaksana.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FLOWCHART 1B: PENGAJUAN CUTI PEGAWAI -->
    <?php if ($canRenderForUser(['staf_pelaksana', 'admin'])): ?>
    <div class="flowchart-card role-section" data-roles="staf_pelaksana,admin,all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-primary"><i class="far fa-calendar-alt mr-2"></i> Alur Kerja Pengajuan & Approval Cuti Pegawai</h5>
                <small class="text-muted">Proses pengajuan cuti melalui modal, ekspor form Word (.docx) kosong resmi, tanggal pengajuan otomatis terkunci, hingga ekspor dokumen Word (.docx) dan PDF terisi.</small>
            </div>
            <div>
                <span class="badge badge-primary">Staf & Admin</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A[Buka Menu Surat-Surat -> Cuti] --> B[Klik Tombol Ajukan Cuti]
    A --> B2[Klik Tombol Form Cuti Kosong .docx]
    A --> B3[Lihat Rekomendasi Cuti di Master Tanggal Merah]
    B3 --> B
    B2 --> H2[Unduh Langsung Template Word Kosong Resmi]
    B --> C[Sistem Otomatis Mengisi Tanggal Pengajuan Terkunci & Data Pegawai]
    C --> D[Pilih Jenis Cuti, Alasan, Lamanya Cuti & Tanggal Mulai-Selesai]
    D --> E[Isi Alamat & Kontak Selama Cuti]
    E --> F[Klik Simpan Pengajuan Cuti]
    F --> G{Persetujuan Atasan / Admin}
    G -->|Disetujui| H[Ekspor Dokumen Terisi: Word .docx & PDF Form Surat Cuti]
                </pre>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FLOWCHART 2: PPK & KASATKER - APPROVAL DISPOSISI & PENGESAHAN -->
    <?php if ($canRenderForUser(['ppk_kasatker'])): ?>
    <div class="flowchart-card role-section" data-roles="ppk_kasatker,all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-warning"><i class="fas fa-signature mr-2"></i> Alur Kerja PPK / Kasatker (Approval Disposisi)</h5>
                <small class="text-muted">Proses pemeriksaan, penolakan, atau persetujuan pengajuan disposisi perjalanan dinas.</small>
            </div>
            <div>
                <span class="badge badge-warning text-dark">PPK / Kasatker</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A[Menerima Email Notifikasi Disposisi / Buka Aplikasi] --> B[Periksa Rincian Maksud Perjadin & Pelaksana]
    B --> C{Keputusan Approval}
    C -->|Tolak| D[Klik Tombol Tolak + Isi Alasan Penolakan]
    C -->|Setujui| E[Klik Tombol Setujui]
    E --> F[Disposisi Disetujui: Notifikasi Terkirim &amp; PDF Dilengkapi Stempel Approved]
                </pre>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FLOWCHART 3: KEUANGAN - VERIFIKASI BIAYA & CETAK KWITANSI/SPPD -->
    <?php if ($canRenderForUser(['admin', 'super_administrator'])): ?>
    <div class="flowchart-card role-section" data-roles="admin,super_administrator,all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-success"><i class="fas fa-calculator mr-2"></i> Alur Kerja Tim Keuangan / Verifikator</h5>
                <small class="text-muted">Proses verifikasi MAK, KOP, tarif transport/penginapan per periode, upload SPT TTD, dan pencetakan kwitansi/SPPD.</small>
            </div>
            <div>
                <span class="badge badge-success">Keuangan</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A[Buka Halaman Surat Tugas] --> B[Pilih Laporan Perjadin yang Disetujui]
    B --> C[Klik Tombol Update Verifikasi]
    C --> D[Pilih KOP Surat, MAK, Dasar SPT & Tarif Periode]
    D --> E[Upload File SPT TTD PDF]
    E --> F[Cetak Dokumentasi Resmi: SPT, SPPD 2 Hal, Kwitansi & Rincian Biaya Excel Multi-Pelaksana 1 File, Nominatif]
                </pre>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FLOWCHART 4: ADMIN OPERASIONAL - MANAJEMEN DATA MASTER & PAKET -->
    <?php if ($canRenderForUser(['admin'])): ?>
    <div class="flowchart-card role-section" data-roles="admin,all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-primary"><i class="fas fa-folder-open mr-2"></i> Alur Kerja Admin Operasional & Data Master</h5>
                <small class="text-muted">Proses pengelolaan master pegawai, paket pekerjaan, SIMAK, dan pengawasan operasional.</small>
            </div>
            <div>
                <span class="badge badge-primary">Admin Operasional</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A["Kelola Master Data: Pegawai (Pencarian Cepat via Tap NFC HP & Quick Profile Card, Scan Form NFC Web API, RFID Dual-Format Reader USB 10-Digit & NFC Android Hex Converter, Masa Kerja TMT CPNS, Foto ID Card), Tanggal Merah & Libur SKB 3 Menteri (Pilih Tahun Dinamis, Sinkronisasi API & Rekomendasi Cuti Harpitnas), Struktur Organisasi, MAK, Dasar SPT"] --> B[Kelola Paket Pekerjaan SIMAK Fisik & Konsultasi]
    B --> C[Kelola RAB Gedung Strategis]
    C --> D[Pantau Rekap Mingguan & Dokumentasi Lapangan]
                </pre>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FLOWCHART 5: SUPER ADMINISTRATOR - PENGATURAN SISTEM & AKSES MENU -->
    <?php if ($canRenderForUser(['super_administrator'])): ?>
    <div class="flowchart-card role-section" data-roles="super_administrator,all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-danger"><i class="fas fa-shield-alt mr-2"></i> Alur Kerja Super Administrator</h5>
                <small class="text-muted">Pengaturan hak akses, menu_akses RBAC, konfigurasi sistem, dan manajemen pengguna.</small>
            </div>
            <div>
                <span class="badge badge-danger">Super Admin</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A[Buka Manajemen User & Role] --> B[Atur Hak Akses RBAC & Menu Akses]
    B --> C[Audit Log & Riwayat Login]
    C --> D[Konfigurasi KOP Surat & Pengaturan Sistem Global]
    D --> E["Unduh File Word (.docx) Template Kop Surat Resmi"]
                </pre>
            </div>
        </div>
    </div>
    <!-- FLOWCHART 6: JADWAL & KLASEMEN GATEBALL -->
    <div class="flowchart-card role-section" data-roles="all,admin,super_administrator,staf_pelaksana,ppk_kasatker">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-primary"><i class="fas fa-trophy mr-2 text-warning"></i> Alur Pertandingan & Klasemen Gateball</h5>
                <small class="text-muted">Akses publik jadwal pertandingan & klasemen live, serta alur operator update skor dengan password otorisasi.</small>
            </div>
            <div>
                <span class="badge badge-info">Publik / Operator</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A["Akses Portal /gateball"] --> B["Pilih Tab Kategori: PUTRA / PUTRI"]
    B --> C["Lihat Jadwal Pertandingan & Klasemen Live (Auto-Sync 3 Detik & Animasi Perpindahan Posisi)"]
    B --> D["Klik Pertandingan / Tombol Update Skor"]
    D --> E["Input Password Otorisasi (Mode Operator)"]
    E --> F["Pilih Mode: Update Cepat ATAU Buka Halaman Timer & Papan Skor (/gateball/match/{id})"]
    F --> G1["Hasil Undian: Klik 'Tukar Posisi' jika tim Merah/Putih bertukar posisi"]
    G1 --> G["Di Halaman Pertandingan: Kontrol Timer 30:00 (Start, Pause, Reset, Peluit/Buzzer)"]
    G --> H["Catat Poin per Bola (G1, G2, G3, Agari) / Tombol Skor (+1, +2, -1)"]
    H --> I["Klik Selesaikan & Kunci Hasil Pertandingan"]
    I --> J["Klasemen & Urutan Peringkat di Seluruh Monitor Terupdate Real-Time Otomatis"]
                </pre>
            </div>
        </div>
    </div>

    <!-- FLOWCHART 7: INVENTARISASI - PENDATAAN ASET SATKER, DBR, PINJAM PAKAI & SARPRAS SEKOLAH -->
    <div class="flowchart-card role-section" data-roles="admin,super_administrator,all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-primary"><i class="fas fa-boxes-stacked mr-2 text-primary"></i> Alur Pengelolaan Inventarisasi Satker, DBR, Pinjam Pakai &amp; Sekolah</h5>
                <small class="text-muted">Alur import SIMAN/SAKTI BMN, pendataan fisik barang, penempatan aset ruangan (DBR), administrasi pinjam pakai aset dinas pegawai, cetak dokumen resmi (PDF/Excel), serta monitoring sarpras sekolah binaan.</small>
            </div>
            <div>
                <span class="badge badge-primary">Inventarisasi</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A["Menu Utama Inventarisasi"] --> B1["1. Daftar Barang (/admin/inventaris/barang)"]
    A --> B2["2. Inventaris Kantor (/admin/inventaris/dbr)"]
    A --> B3["3. Pinjam Pakai Aset (/admin/inventaris/pinjam-pakai)"]
    A --> B4["4. Inventaris Sekolah (/admin/inventaris/sekolah)"]
    A --> B5["5. Aset Tidak Terdata (/admin/inventaris/tidak-terdata)"]
    A --> B6["6. Audit &amp; Stock Opname (/admin/inventaris/audit)"]
    A --> B7["7. Pengaturan Dokumen (/admin/inventaris/pengaturan)"]

    B1 --> C1["Klasifikasi Peruntukan Aset: Kantor (Satker), Mobiler (Sekolah), &amp; Item Lainnya (Peminjaman / Renovasi / Non-Ruangan)"]
    B1 --> C2["Standar BMN: 1 Kode Barang + 1 NUP = Tepat 1 Unit Fisik (Validasi Ketat Form &amp; Import)"]
    B1 --> C3["Update Peruntukan Massal (Pilihan Seluruh NUP / Sebagian Rentang NUP Terisolasi per Kode, Nama &amp; Merk)"]
    B1 --> C4["Import File SIMAN / Excel (Upsert Cerdas: Kode &amp; NUP Sama = Update, Beda = Insert) &amp; Export Excel"]
    B1 --> C5["Cetak Sticker Barcode / QR BMN (Format Resmi Kementerian PU: 1 Baris 2 Sticker, Cetak Satuan / Massal / Filter)"]
    B1 --> C6["Sanitasi Otomatis &amp; Proteksi Anti-Duplikasi Nama Barang / Spesifikasi BMN (Bersih di Seluruh Tabel &amp; Cetakan PDF)"]

    B2 --> D1["Kelola Master Ruangan Kantor Satker &amp; Penanggung Jawab (Fleksibel: Opsi Kosongkan / Belum Ditentukan)"]
    B2 --> D2["Kalkulasi Unit DBR Berbasis NUP Unik (COUNT DISTINCT nup, Bebas Anomali Duplikasi)"]
    B2 --> D3["Alokasi Aset ke DBR via Scan QR Kamera HP Cerdas (Continuous Scan, Laser Reticle, Web Audio &amp; Multi-Format QR SIMAN Paritas Audit) / Manual"]
    B2 --> D4["Cetak Dokumen DBR PDF (Per Ruangan / Seluruh Ruangan Detail NUP A4 Portrait) &amp; Export Excel"]
    B2 --> D5["Integrasi Metrik Real-time: Monitoring Aset Sedang Dipinjam Pakai dari Modul Pinjam Pakai"]
    B2 --> D6["Shortcut 1-Klik 'Audit Ruangan Ini' Terkoneksi ke Modul Audit &amp; Stock Opname"]

    B3 --> F1["Pencatatan &amp; Pengubahan Pinjam Pakai: Kelola Multi-Aset Interaktif (Tambah / Kurang Aset Saat Edit, Validasi Minimal 1 Aset &amp; Sinkronisasi Fisik BMN), Kop Surat Dinamis (Opsi Otomatis Periode Tanggal / Pilihan Kop Spesifik), Nomor Surat (2025 Opsional, 2026+ Otomatis &amp; Terkunci PS.03.01/B/Gs7/{tahun}/{001}), Deteksi Benturan Peminjaman"]
    B3 --> F2["Penerbitan Surat Pinjam Pakai BMN 3 Halaman Resmi Sesuai Standar Word contoh_spp.docx (Font Arial 12pt, Margin 2.5cm/2.0cm, Hlm 1: Kop Base64 Terpilih s/d Pasal 4, Hlm 2: Pasal 5-8 &amp; TTD, Hlm 3 Landscape: Tabel 10 Kolom &amp; Rekap Total)"]
    B3 --> F3["Pembaruan Pinjaman Awal Tahun (Annual Renewal) &amp; Proteksi Rollback Otomatis: Melanjutkan Nomor Registrasi Berjalan di Tahun Baru, Transfer Aset Otomatis, Transaksi Lama Diarsipkan 'Diperbaharui' &amp; Pemulihan Otomatis Saat Dihapus"]
    B3 --> F4["Indikator Baris Merah &amp; Pemisahan Kolom: Kolom Dokumen (Cetak/Upload), Kolom Kelola Pinjam (Perbaharui/Kembalikan) &amp; Kolom Aksi Khusus (Edit/Hapus)"]
    B3 --> F5["Upload Scan Dokumen Resmi &amp; Monitoring Status (Sedang Dipinjam / Diperbaharui / Selesai Dikembalikan)"]
    B3 --> F6["Proses Pengembalian Aset Sekaligus (Update Kondisi Fisik, Catatan &amp; Pemulihan Seluruh Status Aset Induk ke Gudang)"]
    B3 --> F7["Filter Interaktif Tahun &amp; Status: Penyaringan Berdasarkan Tahun Anggaran Pinjam, Status Transaksi, Penyesuaian Metrik Widgets Otomatis &amp; Export Excel Terfilter"]

    B4 --> E1["Monitoring Sarpras &amp; Distribusi Mobiler Sekolah Binaan"]
    B4 --> E2["Pemetaan Wilayah Kabupaten / Kecamatan / Paket Proyek"]

    B5 --> G1["Pencatatan Inventaris Non-BMN / Operasional Satker (P.C Unit, Printer, AC, Lemari Es, dll.)"]
    B5 --> G2["Penetapan Lokasi Penempatan Aset (Terintegrasi Master Ruangan Kantor &amp; Input Detail Ruang)"]
    B5 --> G3["Cetak &amp; Ekspor PDF Format Lampiran (A4 Portrait Tanpa Kop, Total Buah &amp; TTD Petugas Aset)"]
    B5 --> G4["Export Spreadsheet Excel (.xlsx) Lengkap dengan Lokasi &amp; Kondisi Fisik Barang"]

    B6 --> H1["Fleksibilitas Lingkup Audit: 1 Kantor Dibagi Per Ruangan DBR, Single Ruangan, atau Mobiler Sekolah"]
    B6 --> H2["Deteksi Otomatis Status Pinjam Pakai Pegawai (Mencegah Salah Vonis Hilang pada Aset Kedinasan)"]
    B6 --> H3["Partisi Ruangan Dropdown &amp; Penyatuan Aset Dipinjam: Selector Ringkas Terstruktur, Progress Real-time, &amp; Verifikasi Sisa"]
    B6 --> H4["Verifikasi Cepat &amp; Scanner Kamera HP: Scan QR Kode Register SIMAN, Centang Reaktif Auto-Pindah ke Tab Sudah Diperiksa, &amp; Sinkronisasi Dropdown Real-time"]
    B6 --> H5["Pencatatan Status Temuan Lapangan: Sesuai, Kondisi Berubah, Salah Ruangan/Pindah, &amp; Selisih Kurang"]
    B6 --> H6["Penerbitan Berita Acara Stock Opname BMN (BAP PDF A4 Portrait per Ruangan &amp; Export Excel Terkelompok)"]

    B7 --> I1["Pengaturan Masa Berlaku Kop Surat BMN (Auto-Select Sesuai Tanggal Transaksi Surat)"]
    B7 --> I2["Riwayat Masa Jabatan Kasatker Kuasa Pengguna Barang (Sinkronisasi Otomatis Dokumen Lintas Periode)"]
                </pre>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FLOWCHART 8: KONSULTAN INDIVIDUAL - KONTRAK & LAPORAN BULANAN -->
    <?php if ($canRenderForUser(['all', 'admin', 'super_administrator', 'staf_pelaksana'])): ?>
    <div class="flowchart-card role-section" data-roles="all,admin,super_administrator,staf_pelaksana">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-user-tie text-primary mr-2"></i>Flowchart: Alur Kerja Konsultan Individual (Kontrak &amp; Laporan Bulanan)
            </h5>
            <div>
                <span class="badge badge-primary">Konsultan Individual</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A["Pengguna Masuk / Login"] --> B{"Jenis Pegawai Pengguna?"}
    
    B -- "Konsultan Individual" --> C["Akses Penuh Input Menu Konsultan Individual"]
    B -- "Administrator / Pengawas" --> D["Akses Monitoring &amp; Unduh Berkas"]

    C --> C1["1. Submenu Kontrak (/admin/konsultan-individual/kontrak)"]
    C --> C2["2. Submenu Laporan Bulanan (/admin/konsultan-individual/laporan-bulanan)"]

    C1 --> E1["Klik 'Unggah Kontrak Baru'"]
    E1 --> E2["Input Periode: Tanggal Mulai s/d Tanggal Selesai (Tanpa Nomor Kontrak)"]
    E2 --> E3["Pilih File PDF Kontrak (Maks 20MB) &amp; Keterangan"]
    E3 --> E4["Validasi Berkas &amp; Simpan ke Sistem"]
    E4 --> E5["Tersimpan Otomatis di Daftar Kontrak Pribadi"]

    C2 --> F1["Klik 'Unggah Laporan Bulanan'"]
    F1 --> F2["Pilih Periode: Bulan &amp; Tahun (Tanpa Judul Laporan)"]
    F2 --> F3["Pilih Berkas PDF Laporan (Maks 20MB) &amp; Keterangan"]
    F3 --> F4["Validasi Berkas &amp; Simpan ke Sistem"]
    F4 --> F5["Tersimpan di Repositori Arsip Laporan Bulanan"]

    D --> G1["Monitoring Arsip Kontrak Seluruh Konsultan"]
    D --> G2["Monitoring Laporan Bulanan Seluruh Konsultan"]
    G1 --> H1["Filter Berdasarkan Konsultan Individual"]
    G2 --> H2["Filter Berdasarkan Tahun, Bulan, &amp; Konsultan"]
    H1 --> I1["Pratinjau (Preview Modal) &amp; Unduh Dokumen PDF Resmi"]
    H2 --> I1
                </pre>
            </div>
        </div>
    </div>
    <?php endif; ?>


    <!-- PANDUAN PENGGUNAAN SISTEM (ACCORDION STYLE) -->
    <div class="mt-4">
        <!-- MODUL KEUANGAN & SURAT TUGAS -->
        <?php if ($canRenderForUser(['admin', 'super_administrator'])): ?>
        <div class="card menu-tutorial-card mb-3 role-section" data-roles="admin,super_administrator,all">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-weight-bold text-success">
                    <i class="fas fa-calculator mr-2"></i> Modul Keuangan: Verifikasi SPT, Kwitansi, SPPD & Nominatif
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-success"><i class="fas fa-print mr-1"></i> Verifikasi & Cetak Dokumen Keuangan</h6>
                        <p class="small text-muted mb-2"><code>/admin/surat/perjalanan-dinas/surat-tugas</code></p>
                        <ol class="pl-3 small mb-0">
                            <li>Buka menu Surat Tugas (`/admin/surat/perjalanan-dinas/surat-tugas`). <em>(Catatan: Seluruh header dan baris isi tabel (termasuk kolom Periode) dibuat rapi 1 baris. Teks pada kolom Tujuan & Pelaksana dipersingkat pada tampilan 1 baris, dan tooltip interaktif akan muncul saat disorot/hover untuk melihat teks lengkap).</em></li>
                            <li>Gunakan tombol <strong>Setting Nomor Terakhir SPPD/Kwitansi</strong> untuk mengatur nomor awal auto-increment jika ingin melanjutkan dari penomoran sebelumnya (misal: isi <code>15</code> untuk memulai dari <code>016</code>).</li>
                            <li>Klik tombol kuning <strong>Update Verifikasi</strong> pada baris laporan untuk menentukan atau memperbarui Nomor Surat Tugas, KOP Surat, MAK, Dasar SPT, Kode Nomor, dan tarif Transport & Penginapan. <em>(Catatan: Nomor Surat Tugas & Kode Nomor ter-prefill otomatis dengan format <code>/SPT/Gs7/{Tahun}</code> dan <code>/SPD/SATKER/PPS-RIAU/{Tahun}</code>, untuk perjalanan dinas 1 hari baris penginapan dihilangkan secara otomatis namun dapat ditambahkan manual jika diperlukan, serta seluruh data yang pernah disimpan sebelumnya akan terisi otomatis).</em></li>
                            <li>Upload file PDF <strong>SPT TTD</strong> yang telah disahkan.</li>
                            <li>Cetak dokumen resmi: <strong>Surat Tugas (SPT)</strong> (otomatis dilengkapi blok tanda tangan pejabat penandatangan Kasatker/Kepala Satuan Kerja beserta Nama, Jabatan, dan NIP), <strong>SPPD (2 Halaman)</strong> (dengan Maksud Perjalanan Dinas diambil langsung dari Perihal Disposisi, nama Pejabat yang Berwenang Memberi Perintah dibuat 1 baris, bagian 2 hanya menampilkan Nama Pegawai yang melaksanakan perjalanan dinas (tanpa NIP), format ringkas Golongan pada bagian 3.a seperti <code>III/a</code> untuk PNS/CPNS dan prefiks <code>Golongan</code> khusus untuk pegawai PPPK seperti <code>Golongan IX</code> tanpa teks di dalam kurung seperti <code>(Terampil)</code> dan Jabatan pelaksana, bagian 5 Alat angkutan yang dipergunakan menyesuaikan jenis transportasi pada Disposisi/Rincian Biaya, serta blok tanda tangan Pejabat Berwenang beserta nama/NIP dibuat rata tengah), <strong>Kwitansi & Rincian Biaya (Format Excel .xls yang ter-download langsung tanpa HTML preview; jika terdapat beberapa nama pelaksana maka otomatis dibuatkan sheet untuk masing-masing pelaksana seperti <code>RINCI (1)</code>, <code>KWITANSI (1)</code>, <code>RINCI (2)</code>, <code>KWITANSI (2)</code>, dst. tetap dalam 1 file .xls)</strong> (dengan "Kode Nomor" SPPD & "Nomor Bukti" Kwitansi terhubung otomatis, nama penerima kapital sesuai pelaksana masing-masing sheet, serta untuk perjalanan dinas 1 hari tanggal "Berangkat dari tanggal" hanya menampilkan tanggal tanpa tambahan "s/d"), serta <strong>Daftar Nominatif</strong> (NIP otomatis hanya ditampilkan untuk personil berstatus PNS, CPNS, atau PPPK). Seluruh tombol dokumen disajikan ringkas tanpa kata "Cetak" (seperti <code>SPT</code>, <code>Nominatif</code>, <code>SPPD</code>, <code>Kwitansi</code>).</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- DETAILED TUTORIAL PER MENU CATEGORY -->
    <h4 class="font-weight-bold text-dark mt-4 mb-3"><i class="fas fa-book-open text-primary mr-2"></i> Panduan Tutorial Menu Anda</h4>

    <div class="accordion" id="tutorialAccordion">

        <!-- MODUL STAF / PELAKSANA -->
        <?php if ($canRenderForUser(['staf_pelaksana'])): ?>
        <div class="card menu-tutorial-card mb-3 role-section" data-roles="staf_pelaksana,all">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-weight-bold text-info">
                    <i class="fas fa-envelope-open-text mr-2"></i> Modul Staf / Pelaksana: Disposisi, Laporan Perjadin, Lupa Absen & Pengajuan Cuti
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-paper-plane mr-1"></i> Disposisi Perjalanan Dinas</h6>
                                <p class="small text-muted mb-2"><code>/admin/surat/perjalanan-dinas/disposisi</code></p>
                                <ol class="pl-3 small mb-0">
                                    <li>Klik <strong>Tambah Disposisi</strong>.</li>
                                    <li>Pilih Pegawai Pelaksana, Kasatker, dan PPK Penandatangan.</li>
                                    <li>Isi Kota Tujuan, Tanggal Mulai/Selesai, dan Maksud Perjadin.</li>
                                    <li>Sistem mengirimkan email approval ke Pejabat Penandatangan.</li>
                                    <li>Klik tombol merah <strong>Cetak</strong> pada tabel untuk mengunduh PDF Disposisi resmi yang dilengkapi <strong>Stempel Approved</strong>.</li>
                                    <li>Filter periode data secara default disetel dari <strong>awal tahun (1 Januari)</strong> hingga akhir bulan berjalan, serta dapat difilter berdasarkan <strong>Status Disposisi</strong> (Selesai/Disetujui, Belum Selesai/Pending, Ditolak). <em>(Catatan: Seluruh header dan baris isi tabel Disposisi dibuat rapi 1 baris. Teks pada kolom Pelaksana, Tujuan, dan Perihal dipersingkat pada tampilan 1 baris, dan tooltip interaktif tanpa kedip akan muncul saat disorot/hover untuk melihat teks lengkap).</em></li>
                                    <li>Gunakan tombol biru <strong>Ubah</strong> pada kolom Aksi untuk memperbarui data disposisi (pelaksana, tanggal, tujuan, transportasi, perihal, atau pejabat penandatangan).</li>
                                    <li>Gunakan tombol merah <strong>Hapus</strong> pada kolom Aksi jika ingin menghapus disposisi. Sistem akan menghapus data disposisi beserta seluruh dokumen dan laporan terkait hingga benar-benar bersih.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-file-invoice mr-1"></i> Laporan Perjalanan Dinas</h6>
                                <p class="small text-muted mb-2"><code>/admin/surat/perjalanan-dinas</code></p>
                                <ol class="pl-3 small mb-0">
                                    <li>Filter periode data secara default disetel dari <strong>awal tahun (1 Januari)</strong> hingga akhir bulan berjalan, serta dapat difilter berdasarkan <strong>Status Laporan</strong> (Selesai/Final atau Belum Selesai/Draft).</li>
                                    <li>Pilih Disposisi yang sudah disetujui, klik <strong>Buat Laporan</strong>.</li>
                                    <li>Wajib mengisi seluruh data pada <strong>Tab Umum</strong> terlebih dahulu (Pelaksana, Diketahui Oleh, Periode, Tujuan, Sasaran, & Hasil Perjadin). Urutan Pelaksana Perjalanan Dinas secara otomatis tersinkronisasi dengan hirarki <strong>Struktur Organisasi</strong> (`/admin/master/struktur-organisasi`).</li>
                                    <li>Setelah Tab Umum lengkap, buka tab <strong>Dokumentasi Kegiatan</strong> (foto kegiatan) & tab <strong>Dokumentasi Tiket & Pendukung</strong> (tiket/nota).</li>
                                    <li>Klik <strong>Simpan Final</strong> untuk mengunci laporan.</li>
                                    <li>Gunakan kolom <strong>Dokumen SPT</strong> pada tabel Laporan Perjalanan Dinas (`/admin/surat/perjalanan-dinas`) untuk mengunduh/melihat berkas SPT TTD terverifikasi.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-user-clock mr-1"></i> Pengajuan Lupa Absen</h6>
                                <p class="small text-muted mb-2"><code>/admin/surat/lupa-absen</code></p>
                                <ol class="pl-3 small mb-0">
                                    <li>Klik <strong>Ajukan Lupa Absen</strong>.</li>
                                    <li>Pilih Tanggal Absen, Jenis Absen (Masuk/Pulang), dan KOP Surat.</li>
                                    <li>Pilih Template Alasan: <strong>Lupa Absen Masuk</strong>, <strong>Lupa Absen Pulang</strong>, <strong>Terlambat Masuk</strong>, atau <strong>Pulang Sebelum Waktunya</strong> (atau Ketik Manual).</li>
                                    <li>Klik <strong>Ajukan</strong> untuk menyimpan.</li>
                                    <li>Unduh PDF Surat Pernyataan resmi melalui tombol icon PDF (Format Nomor Surat: <code>KP0602/B/Gs7/{Tahun}/</code>).</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="far fa-calendar-alt mr-1"></i> Pengajuan Cuti</h6>
                                <p class="small text-muted mb-2"><code>/admin/surat/cuti</code></p>
                                <ol class="pl-3 small mb-0">
                                    <li>Klik tombol <strong>Form Cuti Kosong (.docx)</strong> di bagian atas untuk mengunduh template formulir cuti kosong resmi Satker PPS Riau (format Word .docx).</li>
                                    <li>Klik tombol <strong>Ajukan Cuti</strong> untuk membuat permohonan cuti baru secara digital.</li>
                                    <li><strong>Tanggal Pengajuan</strong> otomatis terkunci pada tanggal hari ini.</li>
                                    <li>Data pegawai (Nama, NIP, Jabatan, dan kalkulasi Masa Kerja real-time dari NIP TMT CPNS) terisi secara otomatis.</li>
                                    <li>Pilih <strong>Jenis Cuti</strong> (Tahunan, Besar, Sakit, Melahirkan, Alasan Penting, atau Luar Tanggungan).</li>
                                    <li>Isi Alasan, Periode Tanggal Cuti, Alamat & Telepon selama cuti.</li>
                                    <li>Klik <strong>Simpan Pengajuan Cuti</strong>.</li>
                                    <li>Gunakan tombol <strong>Word</strong> atau <strong>PDF</strong> pada tabel untuk mengekspor formulir cuti resmi yang telah terisi data permohonan dan persetujuan.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- MODUL PPK / KASATKER -->
        <?php if ($canRenderForUser(['ppk_kasatker'])): ?>
        <div class="card menu-tutorial-card mb-3 role-section" data-roles="ppk_kasatker,all">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-weight-bold text-warning">
                    <i class="fas fa-signature mr-2"></i> Modul PPK / Kasatker: Persetujuan (Approval) Disposisi
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-warning"><i class="fas fa-check-double mr-1"></i> Verifikasi Disposisi Masuk</h6>
                        <p class="small text-muted mb-2"><code>/admin/surat/perjalanan-dinas/disposisi</code></p>
                        <ul class="pl-3 small mb-0">
                            <li>Buka email notifikasi atau masuk ke menu Disposisi Perjadin.</li>
                            <li>Periksa detail kota tujuan, periode tanggal, dan daftar pegawai pelaksana.</li>
                            <li>Tombol <strong>Setujui</strong> dan <strong>Tolak</strong> secara ketat hanya ditampilkan dan dapat dieksekusi oleh pengguna yang memiliki akses approval (Role dengan <code>FiturApproval</code> aktif, Super Administrator, atau Pejabat Penandatangan PPK/Kasatker yang ditunjuk). Pengguna tanpa akses approval hanya akan melihat status <em>Pending</em>.</li>
                            <li>Klik tombol hijau <strong>Setujui</strong> atau tombol merah <strong>Tolak</strong>.</li>
                            <li>Setelah disetujui, PDF Disposisi akan tersemat <strong>Stempel Approved</strong> dari PPK &amp; Kasatker.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>


        <!-- MODUL ADMIN & SUPERADMIN -->
        <?php if ($canRenderForUser(['admin', 'super_administrator'])): ?>
        <div class="card menu-tutorial-card mb-3 role-section" data-roles="admin,super_administrator,all">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-weight-bold text-primary">
                    <i class="fas fa-cogs mr-2"></i> Modul Data Master, Utility User & Pengaturan Sistem
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-id-card mr-1"></i> Master Data Pegawai</h6>
                                <p class="small text-muted mb-2"><code>/admin/master/pegawai</code></p>
                                <p class="small mb-0">Pengelolaan data seluruh personil kantor Satker PPS Riau mencakup PNS, CPNS, PPPK, PPNPN, <strong>Konsultan Individual</strong>, <strong>Security (Tenaga Keamanan)</strong>, <strong>Cleaning Service (Tenaga Kebersihan)</strong>, dan Non-ASN Lainnya. Dilengkapi integrasi <strong>ID Card (Kartu RFID Mifare Dual-Format)</strong> yang mendukung <strong>Pencarian Cepat Pegawai via Tap NFC Smartphone (Quick Identity Card)</strong> melalui tombol <em>[Cari via NFC]</em> di atas tabel (menempelkan kartu langsung menyaring tabel dan memunculkan pop-up profil pegawai dengan foto, NIP, jabatan, dan status), <strong>Scan NFC HP Langsung dari Browser (Web NFC API)</strong> pada smartphone Android ber-NFC via Google Chrome (cukup klik tombol <em>[Scan NFC]</em> pada modal dan tempelkan kartu ke bodi belakang HP; jika muncul peringatan izin ditolak, buka ikon gembok di sebelah kiri URL Chrome lalu aktifkan izin NFC; jika kartu Mifare Classic tidak merespons, pastikan kartu telah diformat NDEF sekali menggunakan aplikasi NFC Tools atau gunakan scanner USB Reader di PC), deteksi cerdas dan konversi otomatis dua arah antara format <strong>Scanner USB Desktop (10-Digit Desimal / Little-Endian, contoh: <code>3188450969</code>)</strong> dan sensor <strong>NFC Smartphone Android (Heksadesimal / Big-Endian, contoh: <code>99:e6:0b:be</code>)</strong>, lengkap dengan <strong>Live RFID Converter</strong> di modal form (tombol pilih cepat 1-klik), <strong>validasi duplikasi ganda</strong> (mencegah kartu yang sama didaftarkan dengan format berbeda), modal pemindaian NFC interaktif dengan indikator animasi sinyal gelombang, tampilan badge ganda di tabel pegawai, dukungan pencarian DataTables untuk kedua format, <strong>Upload Foto Fisik ID Card</strong> (dengan modal pop-up preview beresolusi penuh), kalkulasi <strong>Masa Kerja</strong> otomatis real-time dari TMT CPNS (NIP 18-digit, digit 9-14), pengisian manual untuk pegawai non-NIP, <strong>filter interaktif Multi-Select Jenis Pegawai</strong> (dengan tombol aksi cepat <em>[Semua]</em> dan <em>[Reset]</em>)/Eselon/Golongan/Status, penambahan data NIP/NIK/ID Kontrak, unggah foto profil, pengosongan NIP otomatis untuk non-ASN (selain PNS, CPNS, PPPK), serta fitur Import, <strong>Export Excel (.xlsx)</strong>, dan <strong>Export PDF (.pdf)</strong> lengkap dengan nomor RFID/ID Card dan format padanannya, foto profil personil, dan sinkronisasi filter aktif.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-sitemap mr-1"></i> Struktur Organisasi Satker</h6>
                                <p class="small text-muted mb-2"><code>/admin/master/struktur-organisasi</code></p>
                                <p class="small mb-0">Pemetaan hirarki bagan piramida / vertikal Top-Down pejabat Satker PPS Riau terintegrasi langsung dengan Master Pegawai, dilengkapi fitur <strong>Grab/Drag to Pan</strong> (navigasi kanvas dengan klik & geser mouse agar bagan sisi kiri/kanan tidak terpotong), perbesaran Zoom In/Out/Reset & Wheel, penambahan anggota sekaligus (*Blok Tim*), mode preview/edit & cetak/export PDF.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-users mr-1"></i> Manajemen User & Hak Akses Role / Group</h6>
                                <p class="small text-muted mb-2"><code>/admin/utility/user</code> & <code>/admin/utility/user-group</code></p>
                                <p class="small mb-0">Tambah, edit, dan perbarui data user serta perubahan role secara dinamis dari master pegawai maupun input manual. Dilengkapi validasi role terintegrasi dan modal <strong>Atur Akses Menu</strong> berfitur <em>Sticky Header</em> untuk kenyamanan navigasi centang hak akses per menu (Akses, Add, Edit, Delete, Export, Import, Approval).</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-1"></i> Master Tanggal Merah, Kalender Libur &amp; Rekomendasi Cuti</h6>
                                <p class="small text-muted mb-2"><code>/admin/master/tanggal-merah</code></p>
                                <p class="small mb-1">Pengelolaan kalender hari libur nasional &amp; cuti bersama terintegrasi live dengan API <code>https://tanggalmerah.upset.dev/api/holidays?year={year}</code> serta <em>fallback</em> resmi ketetapan SKB 3 Menteri (termasuk tahun 2027: 18 Libur Nasional &amp; 8 Cuti Bersama). Dilengkapi fitur unggulan:</p>
                                <ul class="pl-3 small mb-0">
                                    <li><strong>Dropdown Pemilihan Tahun di Toolbar &amp; Modal:</strong> Memilih tahun secara fleksibel (2023 s/d 2027+) langsung dari header halaman maupun switcher tahun di dalam modal Tarik Data API dan modal Rekomendasi Cuti.</li>
                                    <li><strong>Tarik Data API &amp; Fallback SKB 3 Menteri:</strong> Sinkronisasi data otomatis dengan modal preview konfirmasi dinamis (pembeda data baru vs data yang sudah tersimpan). Jika API eksternal belum merilis data (seperti tahun 2027), sistem secara cerdas menggunakan dataset resmi SKB 3 Menteri (No. 1205/2026, No. 3/2026, No. 2/2026).</li>
                                    <li><strong>💡 Fitur Rekomendasi Ambil Cuti (Harpitnas &amp; Long Weekend Optimizer):</strong> Fitur cerdas yang otomatis menganalisis pola hari kejepit nasional (Harpitnas) dan jembatan libur akhir pekan/cuti bersama. Tombol <em>Rekomendasi Cuti</em> di toolbar dilengkapi badge counter jumlah peluang emas. Menampilkan modal interaktif dengan metrik ringkasan (Total Hari Libur Diperoleh, Hari Cuti Terpakai, Efisiensi Rata-Rata Multiplier hingga 5.0x&ndash;9.0x) serta kartu visual berisi timeline strip hari per hari. Dilengkapi tombol <em>Ajukan Cuti Ini</em> yang langsung mengarahkan pengguna ke formulir pengajuan cuti (<code>/admin/surat/cuti</code>).</li>
                                    <li><strong>Indikator Kalender Interaktif 12 Bulan:</strong> Sel kalender hari kejepit yang direkomendasikan diberi tanda khusus (ikon 💡 dan border putus-putus emas). Saat sel diklik, sistem menampilkan popup rincian kalkulasi efisiensi libur beserta tombol cepat untuk mengajukan cuti.</li>
                                    <li><strong>Tabel Data, CRUD Manual &amp; Ekspor Excel:</strong> Tampilan tabel lengkap, penambahan/perubahan hari libur manual, pembersihan data tahun berjalan, serta ekspor file Excel.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-calendar-days mr-1"></i> Dashboard & Kalender Terpadu Satker PPS</h6>
                                <p class="small text-muted mb-2"><code>/admin/dashboard</code></p>
                                <p class="small mb-0">Halaman utama dashboard menyajikan <strong>FullCalendar Terpadu (Col-4 Sidebar Layout)</strong> yang mengombinasikan 4 kategori jadwal: Hari Libur Nasional (merah), Cuti Bersama (oranye/amber), Pegawai Cuti (biru/cyan), dan Perjalanan Dinas (hijau). Dilengkapi filter interaktif, counter pegawai cuti & dinas aktif hari ini, tampilan Bulan/List, serta modal detail lengkap saat tanggal/agenda diklik.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-map-marked-alt mr-1"></i> Peta Sebaran Sekolah (GIS &amp; Export Peta A3)</h6>
                                <p class="small text-muted mb-2"><code>/admin/map</code></p>
                                <p class="small mb-0">Halaman pemetaan interaktif sebaran sekolah binaan berbasis Leaflet Map dengan filter Tipe Map, NPSN, Nama Sekolah, Wilayah Administratif, Klasifikasi Kerusakan, dan Paket Proyek. Dilengkapi fasilitas <strong>Export Peta A3 Landscape</strong> dengan dialog input kustomisasi <strong>Judul Peta</strong> (default: <em>PETA SEBARAN SEKOLAH RAKYAT PROVINSI RIAU</em>), pilihan opsi kartografi <em>Dengan Kontur</em> atau <em>Tanpa Kontur</em>, kompas mata angin, skala bar dinamis, legenda sebaran sekolah, peta indeks (inset) Provinsi Riau, serta blok tanda tangan pengesahan resmi.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-database mr-1"></i> Database Merge & Extract</h6>
                                <p class="small text-muted mb-2"><code>/admin/pengaturan/application</code></p>
                                <p class="small mb-0">Ekstraksi dan penggabungan skema database serta sinkronisasi aplikasi.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-file-image mr-1"></i> Master Kop Surat & Unduh Template Word (.docx)</h6>
                                <p class="small text-muted mb-2"><code>/admin/master/kop-surat</code></p>
                                <p class="small mb-0">Pengelolaan berkas kop surat resmi Satker PPS Riau (unggah gambar banner, pratinjau resolusi penuh, dan aktivasi kop instansi). Dilengkapi fitur <strong>Unduh File Word (.docx)</strong> baik langsung melalui tombol di header halaman (mengunduh kop surat aktif), tombol baris tabel per kop surat, maupun tombol aksi di dalam modal pratinjau. Dokumen Word yang diunduh berformat A4 dengan margin standar kedinasan (Top 1.5 cm, Left/Right/Bottom 2.0 cm), gambar banner kop surat proporsional resolusi tajam, serta draf surat kedinasan lengkap (Nomor, Sifat, Lampiran, Hal, Tanggal, Tujuan Yth., Salam Pembuka, Poin Isi Surat, Penutup, Blok Tanda Tangan Kasatker, dan Tembusan) yang <em>100% editable</em> di Microsoft Word.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- MODUL INVENTARISASI -->
        <!-- MODUL INVENTARISASI & DBR -->
        <?php if ($canRenderForUser(['admin', 'super_administrator'])): ?>
        <div class="card menu-tutorial-card mb-3 role-section" data-roles="admin,super_administrator,all">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-weight-bold text-primary">
                    <i class="fas fa-boxes-stacked mr-2"></i> Modul Inventarisasi: Barang Satker, DBR, Pinjam Pakai, Sekolah &amp; Audit Aset
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-boxes mr-1"></i> 1. Daftar Barang Inventaris</h6>
                                <p class="small text-muted mb-2"><code>/admin/inventaris/barang</code> (Menu Lv2 Inventarisasi)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Masuk ke menu <strong>Inventarisasi &gt; Daftar Barang</strong>. Seluruh aset fisik BMN berada langsung di menu utama ini.</li>
                                    <li><strong>Klasifikasi Peruntukan Aset:</strong> Setiap barang memiliki status peruntukan yang jelas:
                                        <ul class="pl-3 mt-1">
                                            <li><span class="badge badge-primary px-1">Kantor</span> : Aset operasional kantor satker yang dapat dialokasikan ke ruangan-ruangan kantor (DBR).</li>
                                            <li><span class="badge badge-warning text-dark px-1">Mobiler</span> : Aset bantuan sarana pendidikan yang didistribusikan ke sekolah-sekolah penerima.</li>
                                            <li><span class="badge badge-purple text-white px-1" style="background:#7c3aed;">Item Lainnya</span> : Aset non-operasional ruangan / khusus (seperti peminjaman aset kedinasan, aset tetap dalam perbaikan/renovasi, atau aset non-ruangan).</li>
                                        </ul>
                                    </li>
                                    <li><strong>Bilah Filter Cepat Peruntukan:</strong> Di atas daftar data tersedia tombol filter instan (<em>Semua Barang</em>, <em>Kantor / Satker</em>, <em>Mobiler / Sekolah</em>, dan <em>Item Lainnya</em>) lengkap dengan penghitung total unit terkini.</li>
                                    <li><strong>Standar Kuantitas BMN (1 Kode Barang + 1 NUP = 1 Unit Fisik):</strong> Sesuai dengan kaidah penatausahaan BMN Kementerian Keuangan (SIMAN DJKN / SAKTI), setiap 1 baris yang memiliki pasangan Kode Barang dan NUP (Nomor Urut Pendaftaran) secara fisik selalu bernilai tepat 1 Unit. Pada formulir Tambah maupun Ubah Aset, field <em>Jumlah</em> dikunci otomatis menjadi 1 Unit untuk menjaga integritas data register fisik BMN.</li>
                                    <li><strong>⚡ Update Peruntukan Massal (Seluruh NUP / Sebagian Rentang NUP Terisolasi Spesifik per Kode, Nama &amp; Merk):</strong> Klik tombol <em>Update Peruntukan Massal (NUP)</em> untuk mengubah status peruntukan banyak barang sekaligus dalam 1 kali klik. Dropdown pilihan aset membedakan barang berdasarkan <strong>Kode Barang, Nama Barang, dan Merk/Tipe</strong> (sehingga barang dengan Kode Barang yang sama seperti Meja Siswa dan Meja Guru terpisah secara mandiri). Pilih lingkup: <strong>Seluruh NUP (Semua Unit)</strong> untuk memperbarui seluruh unit aset pada item tersebut sekaligus secara otomatis tanpa mempengaruhi item lain yang kodenya sama, atau <strong>Sebagian NUP (Rentang Tertentu)</strong> untuk memasukkan batas <em>NUP Awal</em> hingga <em>NUP Akhir</em> secara spesifik, lalu pilih target peruntukan (Kantor/Mobiler/Item Lainnya).</li>
                                    <li><strong>Pencatatan Aset, Kode Register Unik &amp; Dropdown Satuan/Ruangan:</strong> Formulir penambahan maupun pengubahan data inventaris dilengkapi: (1) <em>Kode Register</em> fisik SIMAN BMN dengan validasi keunikan ketat di database dan form (namun tetap opsional/boleh kosong jika belum memiliki stiker QR SIMAN), serta tombol salin instan di modal ubah; (2) Dropdown <em>Satuan</em> berbasis Select2 yang menyediakan opsi standar dan mendukung pengetikan satuan baru secara manual; (3) Dropdown <em>Lokasi Ruangan</em> cerdas yang merangkum seluruh ruangan kantor Satker sekaligus mendukung pengetikan nama ruangan baru; (4) Pilihan radio peruntukan aset.</li>
                                    <li><strong>Import SIMAN &amp; Export Excel (Loading Progress Real-Time &amp; Mekanisme Upsert):</strong> Unggah file Excel dari SIMAN / SAKTI BMN maupun format spreadsheet lainnya via modal <em>Import SIMAN</em>. Dilengkapi <strong>Loading Progress Real-Time</strong> (progress bar persentase 0–100%, ukuran upload file MB, pengukur waktu berjalan/timer, serta pelacak 5 tahapan proses dari pembacaan lembar data hingga penyimpanan database). Sistem secara cerdas melakukan pemetaan kolom secara presisi (mengecualikan kolom 'Jumlah Foto' atau 'Jumlah Lantai' dari kuantitas aset) dan menjalankan pencocokan: <strong>jika Kode Barang dan NUP sama persis dengan data yang sudah ada, sistem akan memperbarui (update)</strong> data aset tersebut tanpa mereset status ruangan maupun peruntukan yang telah disetel; <strong>jika Kode Barang atau NUP berbeda/baru, sistem akan menambahkan (insert)</strong> sebagai data aset baru dengan kuantitas baku 1 unit. Setelah proses selesai, kartu ringkasan hasil impor (total diproses, aset baru, dan aset di-update) akan ditampilkan secara transparan. Anda juga dapat mengunduh seluruh data ber-peruntukan melalui tombol <strong>Export Excel</strong>.</li>
                                    <li><strong>Cetak Sticker Barcode / QR BMN (Standar Resmi Kementerian PU, 1 Baris Terdiri Atas 2 Sticker):</strong> Fasilitas cetak label fisik aset siap tempel berformat PDF resolusi tinggi pada lembar A4 Portrait. Tata letak mengikuti standar resmi Kementerian Pekerjaan Umum: <strong>1 baris terdiri atas 2 sticker</strong> (kapasitas 14 sticker per halaman A4 dengan proteksi anti-potong <em>page-break-inside: avoid</em>). Setiap sticker memuat logo resmi PU, nama instansi <em>Kementerian Pekerjaan Umum</em> rata tengah dan kode UAKPB + tahun perolehan (<code>145060900691285000KP.[Tahun]</code>) secara utuh tanpa terpotong, garis pemisah horizontal berjarak rapi, Kode Barang, NUP, Nama Baku Barang BMN (Baris Tengah), Deskripsi Fisik / Merk / Tipe (Baris Bawah) dengan <strong>dukungan text wrapping ke baris bawah secara otomatis jika nama item atau spesifikasi panjang</strong>, serta <strong>QR Code sederhana beresolusi tajam berformat <code>#{Kode Register}</code></strong> (atau <code>#{Kode Barang}.{NUP}</code>) yang berukuran ringkas konsisten pada seluruh stiker (modul besar dengan kontras tinggi) sehingga sangat cepat dan mudah dideteksi oleh kamera smartphone. Anda dapat mencetak sticker dengan 3 cara fleksibel: (1) <strong>Cetak Satuan:</strong> Klik tombol ikon QR Code pada kolom Aksi di baris aset bersangkutan; (2) <strong>Cetak Massal Berdasarkan Centang:</strong> Centang satu atau beberapa aset pada tabel lalu klik tombol <em>Cetak Sticker Terpilih</em> pada toolbar pilihan; (3) <strong>Cetak Berdasarkan Filter / Rentang:</strong> Klik tombol <em>Cetak Sticker</em> di header untuk mencetak seluruh aset hasil filter aktif atau memasukkan Kode Barang beserta rentang NUP Awal hingga NUP Akhir tertentu.</li>
                                    <li><strong>Sanitasi Otomatis &amp; Proteksi Anti-Duplikasi Nama Barang / Spesifikasi BMN:</strong> Sistem dilengkapi algoritma <em>auto-deduplication</em> terpusat (<code>clean_inventaris_text</code>) yang secara otomatis mendeteksi dan memangkas teks berulang (seperti string spesifikasi pada file SIMAN yang terisi ganda di kolom Merk dan Tipe, misal: <em>"SAMSUNG GALAXY TAB S11 (5G) 12/256 GB SAMSUNG GALAXY TAB S11 (5G) 12/256 GB"</em>). Sistem otomatis memangkas teks berulang menjadi 1 kali penulisan yang bersih, memulihkan nama umum baku BMN (misal <em>Tablet PC</em>) di kolom Nama Barang, serta menempatkan spesifikasi fisik di kolom Merk/Tipe baik pada tabel Daftar Barang, form Tambah/Ubah, DBR, Berita Acara Audit, Cetak Stiker, hingga Lampiran PDF Pinjam Pakai.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-success"><i class="fas fa-door-open mr-1"></i> 2. Inventaris Kantor (DBR)</h6>
                                <p class="small text-muted mb-2"><code>/admin/inventaris/dbr</code> (Menu Lv2 Inventarisasi)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Masuk ke menu <strong>Inventarisasi &gt; Inventaris Kantor</strong>. Menampilkan seluruh ruangan kerja kantor Satker PPS Riau beserta penanggung jawab ruangan. Halaman ini dilengkapi 4 ubin metrik ringkasan terintegrasi: <em>Total Ruangan</em>, <em>Aset Berlokasi (DBR)</em>, <em>Sedang Dipinjam Pakai</em> (mengambil data real-time aset yang sedang dipinjam pegawai dari modul Pinjam Pakai beserta tombol navigasi cepat), dan <em>Belum Masuk Ruangan</em>. Dilengkapi pula fitur <strong>Tambah Ruangan</strong>, <strong>Ubah Data Ruangan</strong> (terintegrasi otomatis dengan master pegawai, kode, nama, lantai, keterangan, serta tombol &amp; opsi fleksibel <em>Kosongkan / Belum Ditentukan</em> jika penanggung jawab ruangan belum ditetapkan), serta <strong>Hapus Ruangan</strong>.</li>
                                    <li>Klik tombol <strong>Kelola / Detail Barang</strong> pada ruangan untuk membuka penatausahaan aset. Tampilan detail dilengkapi 4 ubin KPI terstruktur (Penanggung Jawab, Lokasi Ruangan, Total Unit Fisik, dan Nilai Perolehan Total Aset BMN) serta navigasi tab rapi antara <em>Rekapitulasi DBR (Format Resmi)</em> dan <em>Daftar Detail Fisik Barang</em>.</li>
                                    <li><strong>Perhitungan Jumlah Unit DBR Berbasis NUP Unik:</strong> Pada rekapitulasi DBR, kolom <em>JUMLAH</em> dihitung murni berdasarkan banyaknya pasangan Kode Barang dan NUP unik (<code>COUNT(DISTINCT nup)</code>). Kode barang dan NUP yang sama tidak akan pernah dihitung lebih dari 1 unit, sehingga rekapitulasi DBR di layar, cetak PDF, dan export Excel selalu akurat mencerminkan unit fisik riil.</li>
                                    <li><strong>Alokasi Khusus Aset Kantor:</strong> Sistem secara otomatis menyaring aset unallocated agar hanya barang ber-peruntukan <strong>Kantor</strong> yang dialokasikan ke ruangan kantor Satker (aset mobiler sekolah disaring agar tidak tercampur).</li>
                                    <li><strong>Alokasikan Barang (Scan QR Code &amp; Alokasi Manual):</strong>
                                        <ul class="pl-3 mt-1">
                                            <li><strong>Scan QR Code / Register (Mobile Scanner Cepat Paritas Audit):</strong> Memindai stiker fisik QR SIMAN BMN atau barcode aset menggunakan arsitektur scanner kamera HP modern yang terpadu dan identik dengan modul Audit (continuous live scanning tanpa reload atau konfirmasi klik manual berulang, modal gelap elegan dengan laser reticle animasi presisi tinggi, umpan balik Web Audio API beep, auto rear-camera, tombol senter/flashlight, dan dukungan multi-format: URL SIMAN BMN, 32-hex register, Kode.NUP, Kode|NUP, maupun barcode USB). Aset yang discan otomatis langsung dialokasikan ke ruangan secara real-time dan modal dapat terus digunakan memindai aset berikutnya tanpa jeda.</li>
                                            <li><strong>Alokasikan Manual:</strong> Modal alokasi aset dengan bilah pencarian cepat (Nama, Kode, NUP, Merk, Register), filter kondisi, retention centang lintas halaman/pencarian, selection summary toolbar, serta <strong>pengurutan NUP numerik murni yang selalu berurutan (1, 2, 3... 13, 14...)</strong> secara terintegrasi dengan kode barang.</li>
                                        </ul>
                                    </li>
                                    <li><strong>Cetak Dokumen Resmi DBR Per Ruangan (PDF Standar PU &amp; Kotak Paraf Multi-Halaman):</strong> Klik tombol <em>Cetak PDF</em> pada detail ruangan untuk mencetak dokumen DBR resmi format A4 Portrait (header instansi Kementerian Pekerjaan Umum, judul DAFTAR BARANG RUANGAN (DBR), kartu info ruangan ringkas, tabel barang agregat, dan kotak paraf otomatis di lembar-lembar awal).</li>
                                    <li><strong>Cetak PDF Seluruh Ruangan (A4 Portrait, Tabel Terpisah Per Ruangan &amp; Smart Page-Break):</strong> Klik tombol merah <span class="badge badge-danger px-1">Cetak PDF Seluruh Ruangan</span> di header halaman utama DBR (<code>/admin/inventaris/dbr/cetak-pdf</code>) untuk mencetak seluruh aset berlokasi di seluruh ruangan kantor dalam format PDF resmi A4 Portrait. Dokumen ditata secara modular dengan <strong>kotak tabel terpisah per ruangan</strong> yang diawali banner judul ruangan, lantai, dan penanggung jawab (kolom nama ruangan dan lantai pada tabel dihilangkan agar tabel bersih dan lega), penulisan masing-masing NUP tersendiri per baris, tanpa kop surat, tanpa kode register, dan tanpa nilai perolehan. Dilengkapi <em>smart page-breaking</em>: jika muat beberapa tabel ruangan dalam satu halaman maka disatukan, namun jika tabel ruangan lanjutannya tanggung/terpotong canggung maka otomatis berpindah rapi ke halaman baru, diakhiri ringkasan total aset dan tanda tangan pengesahan.</li>
                                    <li><strong>Export Excel (Per Ruangan &amp; Seluruh Ruangan Detail NUP):</strong> Tersedia dua fasilitas ekspor spreadsheet: (1) Tombol <em>Export Excel Seluruh Ruangan</em> di halaman utama DBR untuk mengunduh seluruh aset berlokasi di seluruh ruangan secara lengkap di mana setiap NUP ditulis tersendiri per baris (individual NUP) beserta lembar kerja rekapitulasi per ruangan; (2) Tombol <em>Excel</em> pada tabel ruangan atau tombol <em>Export Excel</em> di halaman kelola barang ruangan untuk mengunduh rekapitulasi DBR format resmi per ruangan tertentu.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-warning text-dark"><i class="fas fa-hand-holding mr-1 text-warning"></i> 3. Pinjam Pakai Aset BMN</h6>
                                <p class="small text-muted mb-2"><code>/admin/inventaris/pinjam-pakai</code> (Menu Lv2 Inventarisasi)</p>
                                <ol class="pl-3 small mb-0">
                                    <li><strong>Peminjaman Baru (Pinjamkan Aset - Mendukung Multi-Barang Sekaligus):</strong> Klik tombol <em>Pinjamkan Aset</em>. Sistem mendukung <strong>1 orang pegawai meminjam beberapa barang sekaligus</strong> dalam satu surat perjanjian pinjam pakai. Pilih satu atau beberapa aset BMN yang tersedia dari dropdown multi-select Select2 (aset yang sedang dipinjam aktif otomatis disaring), pilih nama pegawai peminjam dari master pegawai (nama lengkap, NIP, jabatan, dan no. HP terisi otomatis, atau isi manual jika diperlukan), tentukan <strong>Kop Surat Instansi</strong> yang ingin digunakan (sistem langsung menyajikan dan memilih nama kop surat instansi yang berlaku sesuai tanggal pinjam dari Pengaturan Dokumen BMN), tentukan <strong>Nomor Surat Perjanjian Pinjam Pakai</strong>: untuk transaksi <strong>tahun 2025 boleh tidak menggunakan nomor surat (opsional, pada berkas PDF header tampil bersih tanpa titik-titik nomor surat)</strong>, sedangkan untuk transaksi <strong>tahun 2026 ke atas nomor surat terisi secara otomatis dan dikunci rapat (tidak bisa diedit lagi)</strong> dengan format baku resmi <code>PS.03.01/B/Gs7/{tahun}/{nomor auto increment 3 digit}</code> (contoh: <code>PS.03.01/B/Gs7/2026/001</code>) guna menjamin keabsahan dan konsistensi penomoran dokumen BMN. Masukkan tanggal pinjam, estimasi rencana pengembalian, kondisi fisik barang, dan uraian keperluan dinas. Anda juga dapat langsung mengunggah file scan PDF surat peminjaman jika dokumen fisik sudah ditandatangani. Kop surat serta daftar aset BMN yang dipinjam dapat disesuaikan kembali sewaktu-waktu melalui tombol <em>Ubah (Edit)</em>; dropdown kop surat selalu langsung menampilkan nama kop surat instansi yang terpilih/berlaku (tanpa opsi berlabel teks otomatis), dan langsung ter-update pada hasil cetak PDF resmi, sedangkan nomor surat tahun 2026+ akan tetap terkunci. Selain itu, daftar aset BMN yang dipinjam juga dapat diedit, ditambah, dan dikurangi secara fleksibel melalui pemilih multi-aset Select2 (dengan validasi ketat <strong>minimal wajib ada 1 aset terpilih</strong>), di mana aset yang dilepas otomatis dipulihkan status fisiknya ke gudang, dan aset baru langsung beralih menjadi Dipinjam Pakai.</li>
                                    <li><strong>Penerbitan Surat Perjanjian Pinjam Pakai BMN (Cetak PDF 3 Halaman Sama Persis Format Word Resmi contoh_spp.docx):</strong> Klik tombol merah <em>Cetak PDF</em> pada baris transaksi untuk menerbitkan dokumen resmi format 3 halaman standar Kementerian Pekerjaan Umum secara <em>pixel-perfect</em> sesuai tata letak, margin, jenis font Arial, dan ukuran font pada berkas acuan <code>contoh_spp.docx</code>:
                                        <ul class="pl-3 mt-1 mb-1">
                                            <li><strong>Tata Letak, Margin &amp; Tipografi Baku:</strong> Menggunakan font <strong>Arial</strong> (ukuran 11pt untuk teks isi surat dan 10.5–11pt untuk sel tabel lampiran), margin portrait dan landscape yang presisi sesuai standar naskah dinas Kementerian PUPR, serta jarak antar paragraf dan pasal yang lega dan proporsional (tidak dempet).</li>
                                            <li><strong>Halaman 1 (Kop Surat s/d Pasal 4):</strong> Memuat KOP Surat terpilih (di-render via Base64 data URI secara dinamis dan presisi sesuai kop yang disimpan), Judul <em>SURAT PERJANJIAN PINJAM PAKAI</em>, Nomor Surat (bersih tanpa titik-titik jika nomor tidak diisi pada transaksi tahun 2025), pembukaan resmi terbilang hari dan tanggal (<em>"Pada hari ini [Hari] tanggal [Terbilang] bulan [Bulan] tahun [Terbilang] ([DD/MM/YYYY]), kami yang bertandatangan di bawah ini:"</em>), data lengkap identitas PIHAK PERTAMA (Kepala Satker / Kuasa Pengguna Barang) &amp; PIHAK KEDUA (Peminjam), serta kesepakatan Pasal 1 s/d Pasal 4 yang tertata pas dalam 1 halaman dengan 1 baris kosong pemisah setelah judul Pasal.</li>
                                            <li><strong>Halaman 2 (Pasal 5 s/d Pasal 8 &amp; Tanda Tangan):</strong> Memuat ketentuan lanjutan Pasal 5 s/d Pasal 8, kalimat penutup resmi, serta blok tanda tangan resmi PIHAK KEDUA dan PIHAK PERTAMA dengan format nama sejajar horizontal rata (satu baseline) dan bergaris bawah (<u>underline</u>).</li>
                                            <li><strong>Halaman 3 (Orientasi Landscape - Tabel 10 Kolom Lengkap &amp; Rekapitulasi Total):</strong> Memuat Lampiran <em>DAFTAR BARANG MILIK NEGARA YANG DIPINJAM PAKAI</em> dalam orientasi halaman <em>Landscape A4</em> murni. Jika dalam 1 surat dipinjam beberapa barang, seluruh barang ditampilkan lengkap baris per baris bernomor urut 1, 2, 3, dst. (tabel 10 kolom: No, Kode Barang, Nama Barang, NUP, Merk / Tipe, Tahun Perolehan, Jumlah, Nilai Aset per Unit Rp, Kondisi Barang, dan Keterangan) beserta <strong>baris rekapitulasi Total Kuantitas dan Total Nilai Aset</strong> serta blok tanda tangan resmi PIHAK KEDUA &amp; PIHAK PERTAMA yang juga sejajar rata dan bergaris bawah. Dokumen digabungkan secara otomatis dalam satu berkas PDF tunggal (Halaman 1-2 Portrait dan Halaman 3 Landscape).</li>
                                            <li><strong>Ketentuan Khusus Konsultan / Non-ASN:</strong> Jika peminjam berstatus Konsultan Individu / Tenaga Ahli Non-ASN, bagian NIP pada berkas cetak PDF (pada tabel identitas PIHAK KEDUA Halaman 1 serta di bawah nama peminjam pada blok tanda tangan Halaman 2 dan Halaman 3) secara otomatis digantikan dengan teks baku <strong>Tenaga Penunjang Kegiatan</strong> sehingga dokumen resmi terbit dengan tertib administrasi tanpa NIP kosong atau kode dummy.</li>
                                        </ul>
                                    </li>
                                    <li><strong>Pembaruan Pinjam Aset BMN (Annual Renewal / Perpanjangan Awal Tahun Anggaran):</strong> Sesuai tata kelola administrasi BMN Kementerian PU, masa berlaku surat perjanjian pinjam pakai dinas berakhir per 31 Desember tahun berjalan. Pada awal tahun anggaran baru, untuk aset yang secara fisik masih tetap dipegang pegawai untuk kelancaran tugas dinas, barang tidak perlu dikembalikan secara fisik ke gudang. Cukup klik tombol kuning <span class="badge badge-warning text-white font-weight-bold px-1.5"><i class="fas fa-sync-alt mr-1"></i>Perbaharui</span> pada transaksi yang sedang aktif:
                                        <ul class="pl-3 mt-1 mb-1">
                                            <li><strong>Kelanjutan Penomoran Registrasi Surat:</strong> Nomor Surat Perjanjian Pinjam Pakai baru secara otomatis melanjutkan nomor urut registrasi satker yang sedang berjalan pada tahun baru tersebut (format resmi: <code>PS.03.01/B/Gs7/{tahun_baru}/{nomor_urut_berikutnya}</code>).</li>
                                            <li><strong>Periode Masa Pinjam Baru:</strong> Tanggal mulai pinjam baru default ke awal tahun (02 Januari tahun baru atau hari kerja pertama) dan estimasi selesai default ke 31 Desember tahun anggaran berjalan.</li>
                                            <li><strong>Transfer Aset Otomatis (Carry-Over):</strong> Seluruh daftar barang yang dipinjam otomatis disalin ke transaksi baru dengan status <em>Sedang Dipinjam</em> tanpa perlu input ulang aset satu per satu.</li>
                                            <li><strong>Pengarsipan Transaksi Lama:</strong> Status transaksi lama beralih menjadi <em>Diperbaharui</em> dan terhubung secara transparan dengan nomor surat baru untuk rekam jejak audit BMN yang rapi.</li>
                                            <li><strong>Rollback Otomatis Saat Hapus Pembaruan:</strong> Jika transaksi hasil pembaruan tahun baru dihapus (karena pembatalan atau revisi), sistem secara cerdas melakukan <em>rollback otomatis</em>: surat perjanjian induk sebelumnya otomatis dipulihkan kembali statusnya menjadi <strong>Sedang Dipinjam</strong> (tgl realisasi &amp; catatan diperbaharui dibersihkan), seluruh item lama aktif kembali, dan status fisik master aset BMN tetap terjaga aman sebagai <strong>Dipinjam Pakai</strong> atas nama pegawai yang bersangkutan (tidak terlempar ke gudang).</li>
                                        </ul>
                                    </li>
                                    <li><strong>Tata Letak Tabel 9 Kolom (Pemisahan Kolom Dokumen, Kelola Pinjam &amp; Aksi Khusus Edit/Hapus):</strong> Tabel disajikan dalam 9 kolom terstruktur rapi: <em>No</em>, <em>Surat Perjanjian</em>, <em>Rincian Aset BMN</em>, <em>Pegawai Peminjam</em>, <em>Masa Pinjam &amp; Keperluan</em>, <strong>Status &amp; Berkas TTD</strong>, <strong>Dokumen</strong>, <strong>Kelola Pinjam</strong>, dan <strong>Aksi</strong>.
                                        <ul class="pl-3 mt-1 mb-1">
                                            <li><strong>Pemisahan Kolom 'Status &amp; Berkas TTD':</strong> Status peminjaman (<em>Sedang Dipinjam</em> / <em>Diperbaharui</em> / <em>Telah Dikembalikan</em>) dan indikator ketersediaan scan naskah dinas (<em>Scan PDF</em> / <em>Belum Upload</em>) disajikan dalam kolom tersendiri di tengah tabel sehingga status kelengkapan berkas fisik langsung terpantau jelas.</li>
                                            <li><strong>Highlight Baris Merah:</strong> Baris transaksi yang belum memiliki file scan dokumen resmi bertanda tangan secara otomatis diberi latar belakang warna merah lembut (<code>table-danger</code>) serta badge merah peringatan <strong>Belum Upload</strong>.</li>
                                            <li><strong>Kolom Dokumen (Cetak PDF &amp; Upload Cepat):</strong> Dikhususkan untuk naskah dinas, menyediakan tombol merah <span class="badge badge-danger px-1.5"><i class="fas fa-file-pdf mr-1"></i>Cetak PDF</span> untuk mengunduh dokumen resmi 3 halaman, serta tombol <span class="badge badge-outline-danger px-1.5"><i class="fas fa-upload mr-1"></i>Upload / Ganti Scan</span> untuk mengunggah atau mengganti berkas scan bertanda tangan tanpa perlu masuk form edit.</li>
                                            <li><strong>Kolom Kelola Pinjam (Perbaharui &amp; Kembalikan):</strong> Dikhususkan untuk siklus perpanjangan dan pengembalian aset BMN: tombol kuning <span class="badge badge-warning text-white px-1.5"><i class="fas fa-sync-alt mr-1"></i>Perbaharui</span> (annual renewal awal tahun) dan tombol hijau <span class="badge badge-success px-1.5"><i class="fas fa-undo-alt mr-1"></i>Kembalikan</span> (proses serah terima pengembalian aset fisik ke gudang).</li>
                                            <li><strong>Kolom Aksi (Khusus Edit &amp; Hapus):</strong> Dikhususkan untuk pemeliharaan data dasar transaksi: tombol biru <span class="badge badge-outline-primary px-1.5"><i class="fas fa-pen"></i></span> (edit rincian data peminjam, tanggal, kop surat, serta <strong>kelola aset BMN yang dipinjam: bisa menambah atau mengurangi aset dengan syarat minimal 1 aset terpilih</strong>) dan tombol merah <span class="badge badge-outline-danger px-1.5"><i class="fas fa-trash-alt"></i></span> (hapus transaksi).</li>
                                        </ul>
                                    </li>
                                    <li><strong>Sinkronisasi Status Aset Terintegrasi:</strong> Saat peminjaman disimpan, status seluruh aset BMN yang terpilih otomatis beralih menjadi <em>Dipinjam Pakai</em> dan lokasi tercatat sebagai <em>Pinjam Pakai: [Nama Pegawai]</em> sehingga tidak dapat dialokasikan ganda ke ruangan DBR.</li>
                                    <li><strong>Proses Pengembalian Aset Sekaligus:</strong> Setelah peminjaman berakhir, klik tombol hijau <em>Kembalikan</em>. Dialog konfirmasi menampilkan rincian seluruh unit barang yang dipinjam. Tentukan tanggal realisasi pengembalian, kondisi fisik saat barang kembali (Baik / Rusak Ringan / Rusak Berat), dan catatan kelengkapan. Status transaksi berganti menjadi <em>Telah Dikembalikan</em>, dan seluruh aset inventaris induk otomatis dipulihkan sekaligus menjadi <em>Digunakan Sendiri</em> dengan lokasi <em>Gudang / Belum Berlokasi</em> sehingga siap digunakan kembali.</li>
                                    <li><strong>Filter Interaktif Tahun &amp; Status Peminjaman:</strong> Gunakan <em>Filter Card</em> di atas tabel untuk menyaring transaksi pinjam pakai berdasarkan <strong>Tahun Pinjam</strong> (seluruh tahun yang terdata di database tersedia secara otomatis, terurut menurun, disertai indikator Tahun Berjalan) dan <strong>Status Peminjaman</strong> (<em>Sedang Dipinjam</em> / <em>Telah Dikembalikan</em> / <em>Diperbaharui</em>). Filter langsung diterapkan secara otomatis (<em>auto-submit</em>) saat dropdown dipilih atau melalui tombol <em>Saring</em>. Kartu ringkasan metrik statistik (<em>Sedang Dipinjam</em>, <em>Telah Dikembalikan</em>, <em>Pegawai Peminjam</em>, dan <em>Nilai Aset Dipinjam</em>) di bagian atas secara otomatis merefleksikan tahun anggaran yang sedang dipilih.</li>
                                    <li><strong>Export Excel Rekap Pinjam Pakai Terfilter:</strong> Klik tombol hijau <em>Export Excel</em> di bagian atas untuk mengunduh rekaman transaksi peminjaman aset dalam format spreadsheet (.xlsx). Jika filter tahun atau status sedang aktif, berkas Excel yang diunduh secara otomatis menyesuaikan dengan data terfilter dan mencantumkan keterangan filter tahun/status pada kop judul spreadsheet.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-info"><i class="fas fa-school mr-1"></i> 4. Inventaris Sekolah</h6>
                                <p class="small text-muted mb-2"><code>/admin/inventaris/sekolah</code> (Menu Lv2 Inventarisasi)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Masuk ke menu <strong>Inventarisasi &gt; Inventaris Sekolah</strong>.</li>
                                    <li>Halaman menampilkan seluruh sekolah binaan Satker PPS Riau yang telah terdata dalam paket rehabilitasi/renovasi prasarana strategis dan penerima bantuan mobiler pendidikan.</li>
                                    <li>Gunakan filter <strong>Paket Proyek</strong>, <strong>Kabupaten</strong>, atau <strong>Kata Kunci</strong> (NPSN / Nama Sekolah) untuk memetakan inventaris sarpras dan mobiler per lokasi sekolah binaan.</li>
                                    <li>Status sarpras sekolah terpantau secara terintegrasi dengan data paket proyek prasarana sekolah.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #1E3A8A !important;">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-navy" style="color: #1E3A8A;"><i class="fas fa-clipboard-list mr-1"></i> 5. Aset Tidak Terdata (Non-BMN)</h6>
                                <p class="small text-muted mb-2"><code>/admin/inventaris/tidak-terdata</code> (Menu Lv2 Inventarisasi)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Masuk ke menu <strong>Inventarisasi &gt; Aset Tidak Terdata</strong>. Menu ini difungsikan khusus untuk mencatat aset fisik operasional kantor yang belum/tidak terdaftar dalam aplikasi SIMAN BMN (contoh: aset perolehan saat masih status PPK PS di BPPW Cipta Karya seperti P.C Unit, Printer HP/Epson, AC Split, AC Floor Stand, Lemari Es, dsb).</li>
                                    <li><strong>Ubin Metrik Ringkasan:</strong> Dilengkapi 4 indikator KPI otomatis: <em>Jenis Barang</em>, <em>Total Kuantitas (Buah)</em>, <em>Kondisi Baik</em>, dan <em>Perlu Perbaikan / Rusak</em>.</li>
                                    <li><strong>Pencatatan &amp; Penetapan Lokasi Aset:</strong> Klik tombol <em>Tambah Aset</em>. Masukkan Nama Barang, Kuantitas (Buah), Satuan, Merk/Spesifikasi, Tahun Perolehan, dan Kondisi Fisik. Tentukan <strong>Lokasi Aset</strong>: pilih dari dropdown resmi <em>Master Ruangan</em> kantor Satker dan/atau lengkapi detail lokasi spesifik (misal: Ruang Tata Usaha, Ruang Staf, Aula Pertemuan, Pantry/Dapur). Masukkan keterangan riwayat perolehan aset.</li>
                                    <li><strong>Tampilan Tabel &amp; Card Responsif (Tanpa Tombol Expand):</strong> Seluruh 9 kolom data (No, Nama Barang, Kuantitas, Merk/Type, Tahun, Lokasi Aset, Kondisi, Keterangan Perolehan, Aksi) ditampilkan secara utuh tanpa tombol sembunyi/expand (+), dilengkapi navigasi tabel horizontal responsif, filter ruangan/kondisi instan, serta baris rekapitulasi Total Kuantitas yang presisi.</li>
                                    <li><strong>Pengubahan &amp; Penghapusan Data:</strong> Tombol <em>Ubah</em> (kuning) untuk memperbarui data fisik atau mutasi lokasi barang, dan tombol <em>Hapus</em> (merah) dengan dialog konfirmasi aman.</li>
                                    <li><strong>Cetak &amp; Ekspor PDF Format Lampiran (Tanpa Kop Surat):</strong> Sesuai format lampiran asli, dokumen PDF disajikan tanpa kop surat, diawali langsung dengan judul DAFTAR ASET TIDAK TERDATA, tabel data lengkap (No, Nama Barang, Buah, Merk/Type, Tahun Perolehan, Lokasi Aset, Keterangan), baris rekapitulasi Total Buah, serta blok tanda tangan Petugas Aset Tetap Hendrick Bastiar (NIP. 197810162025211023). Tersedia tombol <em>Cetak PDF</em> (pratinjau di tab baru) dan <em>Ekspor PDF</em> (unduh langsung file .pdf).</li>
                                    <li><strong>Export Excel (.xlsx):</strong> Klik tombol <em>Ekspor Excel</em> untuk mengunduh rekapitulasi lengkap dalam format spreadsheet yang rapi dan siap saji.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 mb-3">
                        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #0A66C2 !important;">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-clipboard-check mr-1"></i> 6. Audit &amp; Stock Opname Aset BMN</h6>
                                <p class="small text-muted mb-2"><code>/admin/inventaris/audit</code> (Menu Lv2 Inventarisasi)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Masuk ke menu <strong>Inventarisasi &gt; Audit &amp; Stock Opname</strong>. Modul ini difungsikan khusus untuk pelaksanaan pemeriksaan fisik periodik (stock opname) seluruh aset BMN kantor maupun aset bantuan mobiler sekolah binaan Satker PPS Riau.</li>
                                    <li><strong>Ubin Metrik Global KPI:</strong> Memantau secara real-time: <em>Total Sesi Audit</em>, <em>Sedang Berjalan</em>, <em>Selesai &amp; Terkunci</em>, dan <em>Total Aset Diperiksa</em>.</li>
                                    <li><strong>Mulai Sesi Audit Baru (3 Pilihan Lingkup Fleksibel):</strong> Klik tombol <em>Mulai Sesi Audit Baru</em>. Tentukan Judul Kegiatan, Tanggal, dan <strong>Nama Petugas Auditor</strong> (dapat dipilih langsung dari dropdown daftar pegawai aktif dengan auto-fill NIP, atau diketik manual nama baru). Pilih lingkup audit yang diinginkan:
                                        <ul class="pl-3 mt-1">
                                            <li><strong>1 Kantor (Dibagi Per Ruangan) [Rekomendasi Utama]:</strong> Mengaudit seluruh aset kantor satker dalam 1 sesi terpadu, namun secara otomatis dipartisi per ruangan DBR. Petugas dapat berpindah memeriksa ruangan demi ruangan tanpa perlu membuat banyak sesi terpisah.</li>
                                            <li><strong>Kantor - Khusus 1 Ruangan:</strong> Memilih 1 ruangan kantor target spesifik dari Master DBR (misal: Ruang Tata Usaha saja).</li>
                                            <li><strong>Aset Sekolah / Mobiler:</strong> Menarik seluruh data aset sarana pendidikan binaan sekolah.</li>
                                        </ul>
                                    </li>
                                    <li><strong>Shortcut Cepat 1-Klik dari Menu DBR:</strong> Pada halaman Detail DBR (<code>/admin/inventaris/dbr/{id}</code>) dan tabel daftar ruangan utama, tersedia tombol kuning <span class="badge badge-warning text-dark px-1.5"><i class="fas fa-clipboard-check mr-1"></i>Audit Ruangan Ini</span> yang secara otomatis langsung membuka dan menginisiasi sesi audit untuk ruangan bersangkutan tanpa perlu input manual.</li>
                                    <li><strong>Deteksi Otomatis Aset Sedang Dipinjam Pakai Pegawai:</strong> Saat sesi audit dibentuk, sistem secara otomatis mengecek relasi ke modul <em>Pinjam Pakai</em>. Aset yang sedang sah dipinjam pegawai akan diberi badge oranye <em>Dipinjam</em> lengkap dengan nama peminjam dan nomor surat izin, sehingga petugas auditor tidak salah menetapkan aset tersebut sebagai barang hilang.</li>
                                    <li><strong>Dropdown Partisi Ruangan &amp; Workspace Audit:</strong>
                                        <ul class="pl-3 mt-1">
                                            <li><strong>Dropdown Partisi Ruangan Kompak:</strong> Di atas tabel audit tersedia <em>Dropdown Selector Partisi Ruangan</em> yang praktis dan hemat ruang. Pilihan dipisahkan rapi dalam grup <em>Ruangan Kantor Satker</em> dan <em>Peminjaman &amp; Lainnya</em>, lengkap dengan jumlah unit, persentase progress, dan tanda centang hijau bagi ruangan yang sudah 100% selesai diperiksa.</li>
                                            <li><strong>Penyatuan Grup "Aset yang Dipinjam":</strong> Seluruh aset yang berstatus pinjam pakai disatukan ke dalam satu kelompok partisi tunggal bernama <em>"Aset yang Dipinjam"</em> (tidak lagi terpecah-pecah per nama peminjam), sehingga memudahkan auditor memeriksa kepatuhan peminjaman aset kedinasan secara terpusat.</li>
                                            <li><strong>Kartu Konteks Partisi &amp; Tombol Verifikasi Cepat:</strong> Saat memilih partisi tertentu, muncul kartu info kontekstual (Penanggung Jawab &amp; Lantai untuk ruangan fisik; Keterangan Status Pinjam untuk aset dipinjam) beserta tombol verifikasi cepat per partisi seperti <span class="badge badge-light border text-success font-weight-bold"><i class="fas fa-check-double mr-1"></i>Tandai Sisa Ruangan Ini Selesai</span> atau <span class="badge badge-warning text-dark font-weight-bold"><i class="fas fa-check-double mr-1"></i>Konfirmasi Sisa Aset Pinjam Selesai</span>.</li>
                                            <li><strong>Verifikasi Centang 1-Klik Auto-Pindah ke "Sudah Diperiksa":</strong> Klik tombol centang hijau <i class="fas fa-check text-success"></i> pada baris barang untuk memverifikasi kondisi fisik aset sebagai <em>Sesuai &amp; Ada</em>. Baris aset yang telah diverifikasi akan menyala hijau sejenak (300ms) lalu secara otomatis meluncur hilang (slide-up) dari tabel antrean kerja aktif dan berpindah ke tab <strong>✅ Sudah Diperiksa</strong>, sehingga tabel kerja tetap rapi dan fokus pada sisa barang yang belum diperiksa. Seluruh metrik KPI (Target, Sesuai, Belum Dicek), badge counter tab, serta nilai persentase pada <em>Dropdown Partisi Ruangan (Select2)</em> akan langsung ter-update secara real-time di layar tanpa me-reload halaman sama sekali.</li>
                                            <li><strong>Scanner Kamera Smartphone (QR SIMAN / Kode Register):</strong> Tersedia tombol <span class="badge badge-light border text-primary font-weight-bold"><i class="fas fa-camera mr-1"></i>Scan Kamera HP</span> yang dapat memanfaatkan kamera belakang ponsel auditor secara live. Kamera secara otomatis membaca QR stiker BMN Kementerian Keuangan berbasis Kode Register 32-hex maupun barcode fisik, membunyikan audio konfirmasi, dan memverifikasi barang secara estafet (continuous scan) tanpa reload.</li>
                                            <li><strong>Mode Scan Cepat USB / Manual:</strong> Kotak pemindai di bagian atas mendukung scanner barcode USB atau ketikan NUP/Kode Barang untuk input cepat di meja kerja.</li>
                                            <li><strong>Pencatatan Temuan Khusus:</strong> Klik tombol edit biru <i class="fas fa-edit text-primary"></i> untuk mencatat temuan: (1) <em>Kondisi Berubah</em> (misal di buku tercatat Baik, fisik ditemukan Rusak Ringan/Rusak Berat); (2) <em>Salah Lokasi / Pindah Ruangan</em> (memilih ruangan fisik tempat barang ditemukan); (3) <em>Terkonfirmasi Dipinjam Sah</em>; atau (4) <em>Tidak Ditemukan / Hilang (Selisih Kurang)</em> beserta catatan keterangan.</li>
                                        </ul>
                                    </li>
                                    <li><strong>Verifikasi Sisa Sesuai Massal:</strong> Klik tombol <em>Verifikasi Sisa Sesuai</em> di toolbar utama untuk menandai seluruh sisa barang kantor yang belum diperiksa sekaligus menjadi status Sesuai (aset pinjam pakai otomatis terkonfirmasi dipinjam).</li>
                                    <li><strong>Penyelesaian &amp; Penguncian Sesi Audit:</strong> Setelah seluruh fisik terverifikasi, klik tombol <em>Selesaikan Audit</em> untuk mengunci data agar tidak berubah. Tersedia pula tombol <em>Buka Kembali Sesi</em> jika sewaktu-waktu diperlukan perbaikan/revisi.</li>
                                    <li><strong>Cetak Berita Acara Stock Opname (PDF Resmi A4 Portrait Terkelompok per Ruangan):</strong> Klik tombol <em>Cetak BAP (PDF)</em> untuk mencetak Berita Acara Pemeriksaan Fisik BMN resmi. Tabel rincian barang otomatis terkelompok rapi dengan Banner Ruangan, informasi penanggung jawab ruangan, rincian fisik, serta subtotal unit per ruangan, ditutup dengan kolom tanda tangan pengesahan resmi.</li>
                                    <li><strong>Export Excel Spreadsheet Terkelompok (.xlsx):</strong> Klik tombol <em>Export Excel</em> untuk mengunduh rekapitulasi lengkap hasil audit dalam format spreadsheet Excel dengan banner ruangan terpisah dan pewarnaan status temuan yang rapi.</li>
                                    <li><strong>Penghapusan Sesi Audit:</strong> Pengguna dengan izin <code>FiturDelete</code> (sesuai aturan tabel <code>menu_akses</code>) dapat menghapus sesi audit baik langsung dari tombol hapus di tabel utama maupun dari dalam workspace audit. Konfirmasi dilakukan melalui modal interaktif yang aman untuk mencegah ketidaksengajaan.</li>
                                </ol>
                            </div>
                        </div>
                    <div class="col-lg-12 col-md-12 mb-3">
                        <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #6366F1 !important;">
                            <div class="card-body">
                                <h6 class="font-weight-bold" style="color: #4F46E5;"><i class="fas fa-sliders-h mr-1"></i> 7. Pengaturan Dokumen &amp; Pejabat BMN</h6>
                                <p class="small text-muted mb-2"><code>/admin/inventaris/pengaturan</code> (Menu Lv2 Inventarisasi)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Masuk ke menu <strong>Inventarisasi &gt; Pengaturan Dokumen</strong>. Modul ini dikhususkan untuk mengelola konfigurasi penerbitan berkas resmi BMN (Surat Perjanjian Pinjam Pakai, DBR, dan BAP Audit) secara mandiri di dalam modul Inventarisasi tanpa bercampur dengan Master Data umum instansi.</li>
                                    <li><strong>Tab 1: Masa Berlaku Kop Surat BMN:</strong> Mengelola berkas gambar Kop Surat resmi instansi lengkap dengan rentang tanggal berlaku (<em>Berlaku Dari</em> s/d <em>Berlaku Sampai</em>). Sistem secara cerdas memilih kop surat yang sesuai dengan tanggal transaksi surat saat formulir dibuka atau dicetak.</li>
                                    <li><strong>Tab 2: Riwayat Jabatan Kasatker (Kuasa Pengguna Barang):</strong> Mencatat daftar nama pejabat Kasatker, NIP, gelar, jabatan resmi, dan rentang masa jabatan (TMT Mulai s/d TMT Selesai). Berkas arsip transaksi masa lampau yang dicetak ulang otomatis mencantumkan nama Kasatker yang menjabat pada masa tersebut, bukan pejabat saat ini.</li>
                                    <li><strong>Ketentuan Penandatanganan Dokumen Pinjam Pakai:</strong> Tanda tangan penerima (PIHAK KEDUA) dan penyerah (PIHAK PERTAMA) diperbolehkan sama. Apabila <em>Kepala Satuan Kerja (Kuasa Pengguna Barang)</em> meminjam aset BMN untuk keperluan operasional kedinasannya, dokumen perjanjian pinjam pakai langsung ditandatangani oleh Kasatker selaku Kuasa Pengguna Barang pada kedua belah pihak tanpa memerlukan delegasi ke pejabat lain.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- MODUL PERTANDINGAN GATEBALL -->
        <div class="card menu-tutorial-card mb-3 role-section" data-roles="all,admin,super_administrator,staf_pelaksana,ppk_kasatker">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-weight-bold text-primary">
                    <i class="fas fa-trophy mr-2 text-warning"></i> Modul Jadwal & Klasemen Pertandingan Gateball
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-globe mr-1"></i> Akses Publik & Tampilan Real-time</h6>
                                <p class="small text-muted mb-2"><code>/gateball</code> (satkerpps-riau.online/gateball)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Buka URL <code>/gateball</code> dari browser pada smartphone, tablet, laptop, atau layar display TV monitor.</li>
                                    <li>Pilih tab <strong>PUTRA</strong> atau <strong>PUTRI</strong> di bagian atas banner untuk melihat jadwal dan klasemen masing-masing kategori.</li>
                                    <li>Tabel kiri menampilkan 10 daftar jadwal pertandingan beserta skor & status pertandingan.</li>
                                    <li>Tabel kanan menampilkan klasemen 5 UNOR (PS, BWSS III, BPBPK, BP2JK, BPJN) lengkap dengan kolom Main, M (Menang), K (Kalah), S (Seri), POINT, dan SCORE (Selisih Skor).</li>
                                    <li>Halaman dilengkapi fitur <strong>Live Sync Real-time</strong> (3 detik) dengan <strong>Notifikasi Alert Auto-Close</strong> saat terjadi pembaruan skor di lapangan, serta tombol <strong>Fullscreen</strong> dan <strong>Cetak PDF</strong>.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-stopwatch mr-1"></i> Timer & Papan Skor Live per Pertandingan</h6>
                                <p class="small text-muted mb-2"><code>/gateball/match/{id}</code> (Otorisasi Operator Turnamen)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Klik baris pertandingan mana saja pada tabel jadwal untuk langsung membuka halaman <strong>Papan Skor & Timer Pertandingan</strong> (bebas dilihat siapa saja secara live tanpa password).</li>
                                    <li>Saat operator ingin melakukan perubahan (menjalankan timer, menambah poin bola, atau mereset), sistem akan meminta verifikasi password otorisasi resmi turnamen.</li>
                                    <li>Klasemen turnamen otomatis ter-update secara <em>real-time</em> saat pertandingan sedang berlangsung meskipun laga belum selesai, lengkap dengan <strong>Animasi Meluncur Halus (FLIP)</strong> saat posisi klasemen berubah.</li>
                                    <li>Gunakan tombol <strong>Tukar Posisi (Merah ⇄ Putih)</strong> di toolbar atas atau di bawah lingkaran VS untuk menukar posisi tim sesuai hasil undian bola ganjil/genap sebelum laga dimulai.</li>
                                    <li>Gunakan <strong>Digital Timer 30:00</strong> dengan kontrol Start, Pause, Reset, Tambah/Kurang Waktu, serta Suara Peluit / Buzzer.</li>
                                    <li>Jalankan timer terlebih dahulu, lalu catat poin tim dengan menekan kartu bola yang masuk (sistem mewajibkan timer sedang berjalan sebelum poin dapat diinput, serta otomatis menghitung siklus resmi: <strong>1 pt &rarr; 2 pts &rarr; 3 pts &rarr; 5 pts (Agari)</strong> dengan indikator target gate).</li>
                                    <li>Klik <strong>Selesaikan & Kunci Hasil Pertandingan</strong> untuk menyelesaikan laga dan mengunci hasil akhir turnamen.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
        <!-- MODUL KONSULTAN INDIVIDUAL -->
        <?php if ($canRenderForUser(['all', 'admin', 'super_administrator', 'staf_pelaksana'])): ?>
        <div class="card menu-tutorial-card mb-3 role-section" data-roles="all,admin,super_administrator,staf_pelaksana">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-weight-bold text-primary">
                    <i class="fas fa-user-tie mr-2 text-primary"></i> Modul Konsultan Individual: Kontrak Kerja &amp; Laporan Bulanan
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-file-contract mr-1"></i> 1. Pengelolaan Dokumen Kontrak</h6>
                                <p class="small text-muted mb-2"><code>/admin/konsultan-individual/kontrak</code></p>
                                <ol class="pl-3 small mb-0">
                                    <li><strong>Ketentuan Akses Unggah:</strong> Formulir pengunggahan dokumen kontrak kerja hanya dapat diakses dan dieksekusi oleh pengguna yang terdaftar sebagai <strong>Konsultan Individual</strong>. Pengguna Administrator dan peran lainnya bertindak sebagai pengawas yang dapat memantau dan mengunduh berkas.</li>
                                    <li>Klik tombol <strong>Unggah Kontrak Baru</strong> pada sudut kanan atas.</li>
                                    <li>Data nama dan NIP konsultan otomatis terisi sesuai akun login yang aktif.</li>
                                    <li>Isi <strong>Tanggal Mulai Kontrak</strong> dan <strong>Tanggal Selesai Kontrak</strong>. (Catatan: Sesuai ketentuan, kolom nomor kontrak tidak diperlukan).</li>
                                    <li>Pilih berkas dokumen fisik dalam format <strong>PDF</strong> (maksimum 20 MB).</li>
                                    <li>Tambahkan catatan keterangan atau addendum jika diperlukan, lalu klik <strong>Simpan &amp; Unggah</strong>.</li>
                                    <li>Sistem secara otomatis menghitung durasi kontrak serta menampilkan indikator status aktif (<em>Aktif</em>, <em>Akan Datang</em>, atau <em>Berakhir</em>).</li>
                                    <li>Klik tombol <strong>Lihat</strong> untuk pratinjau PDF langsung di peramban atau tombol <strong>Unduh</strong> untuk menyimpan berkas.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-info"><i class="fas fa-calendar-check mr-1"></i> 2. Pengelolaan Laporan Bulanan</h6>
                                <p class="small text-muted mb-2"><code>/admin/konsultan-individual/laporan-bulanan</code></p>
                                <ol class="pl-3 small mb-0">
                                    <li><strong>Ketentuan Akses Unggah:</strong> Formulir pengunggahan laporan bulanan dikhususkan bagi pegawai berjenis <strong>Konsultan Individual</strong> untuk pelaporan progres kerja berkala.</li>
                                    <li>Klik tombol <strong>Unggah Laporan Bulanan</strong> pada sudut kanan atas.</li>
                                    <li>Data identitas konsultan otomatis terisi dari sesi pengguna aktif.</li>
                                    <li>Pilih <strong>Bulan Laporan</strong> (Januari s/d Desember) dan <strong>Tahun Anggaran</strong>.</li>
                                    <li>Unggah berkas dokumen laporan kegiatan dalam format <strong>PDF</strong> (maksimum 20 MB). <em>(Catatan: Sesuai ketentuan, formulir laporan bulanan tidak menggunakan judul laporan).</em></li>
                                    <li>Isi keterangan singkat (opsional), lalu klik <strong>Simpan &amp; Unggah</strong>. Dokumen langsung tersimpan rapi dalam repositori arsip berkas tanpa memerlukan alur persetujuan (approval) berjenjang.</li>
                                    <li>Gunakan filter <strong>Tahun</strong>, <strong>Bulan</strong>, dan <strong>Konsultan</strong> pada bagian atas tabel untuk mempermudah penelusuran arsip laporan bulanan.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- CDN Mermaid.js for Rendering Diagrams -->
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (typeof mermaid !== 'undefined') {
        mermaid.initialize({
            startOnLoad: true,
            theme: 'default',
            flowchart: {
                useMaxWidth: true,
                htmlLabels: true,
                curve: 'basis'
            }
        });
    }

    // Role Filter Handler (For Superadmin)
    const roleButtons = document.querySelectorAll('.role-filter-btn');
    const roleSections = document.querySelectorAll('.role-section');

    if (roleButtons.length > 0) {
        roleButtons.forEach(button => {
            button.addEventListener('click', function () {
                roleButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const selectedRole = this.getAttribute('data-role');

                roleSections.forEach(section => {
                    const sectionRoles = section.getAttribute('data-roles').split(',');
                    if (selectedRole === 'all' || sectionRoles.includes(selectedRole)) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            });
        });
    }

    // Quick Search Filter
    const searchInput = document.getElementById('tutorialSearchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.menu-tutorial-card, .flowchart-card');

            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (query === '' || text.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>
<?= $this->endSection(); ?>
