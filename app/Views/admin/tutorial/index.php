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
    A["Kelola Master Data: Pegawai, Tanggal Merah (API upset.dev & Kalender), Struktur Organisasi, MAK, Dasar SPT"] --> B[Kelola Paket Pekerjaan SIMAK Fisik & Konsultasi]
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
                </pre>
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

    <!-- FLOWCHART 7: INVENTARISASI - PENDATAAN ASET SATKER, DBR & SARPRAS SEKOLAH -->
    <div class="flowchart-card role-section" data-roles="admin,super_administrator,all">
        <div class="flowchart-card__header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold mb-1 text-primary"><i class="fas fa-boxes-stacked mr-2 text-primary"></i> Alur Pengelolaan Inventarisasi Satker, DBR &amp; Sekolah</h5>
                <small class="text-muted">Alur import SIMAN/SAKTI BMN, pendataan fisik barang, penempatan aset ruangan (DBR), cetak dokumen resmi DBR (PDF/Excel), serta monitoring sarpras sekolah binaan.</small>
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
    A --> B3["3. Inventaris Sekolah (/admin/inventaris/sekolah)"]

    B1 --> C1["Klasifikasi Peruntukan Aset: Kantor (Satker), Mobiler (Sekolah), &amp; Item Lainnya (Peminjaman / Renovasi / Non-Ruangan)"]
    B1 --> C2["Standar BMN: 1 Kode Barang + 1 NUP = Tepat 1 Unit Fisik (Validasi Ketat Form &amp; Import)"]
    B1 --> C3["Update Peruntukan Massal (Pilihan Seluruh NUP / Sebagian Rentang NUP Terisolasi per Kode, Nama &amp; Merk)"]
    B1 --> C4["Import File SIMAN / Excel (Upsert Cerdas: Kode &amp; NUP Sama = Update, Beda = Insert) &amp; Export Excel"]

    B2 --> D1["Kelola Master Ruangan Kantor Satker &amp; Penanggung Jawab"]
    B2 --> D2["Kalkulasi Unit DBR Berbasis NUP Unik (COUNT DISTINCT nup, Bebas Anomali Duplikasi)"]
    B2 --> D3["Alokasi Aset Kantor ke Ruangan (DBR) via Scan QR / Pencarian Manual"]
    B2 --> D4["Cetak Dokumen DBR PDF (Per Ruangan / Seluruh Ruangan Detail NUP A4 Portrait) &amp; Export Excel"]

    B3 --> E1["Monitoring Sarpras &amp; Distribusi Mobiler Sekolah Binaan"]
    B3 --> E2["Pemetaan Wilayah Kabupaten / Kecamatan / Paket Proyek"]
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
                                <p class="small mb-0">Pengelolaan data seluruh personil kantor Satker PPS Riau mencakup PNS, CPNS, PPPK, PPNPN, <strong>Konsultan Individual</strong>, <strong>Security (Tenaga Keamanan)</strong>, <strong>Cleaning Service (Tenaga Kebersihan)</strong>, dan Non-ASN Lainnya. Dilengkapi integrasi <strong>ID Card (Kartu RFID)</strong> beserta <strong>Upload Foto Fisik ID Card</strong> (dengan modal pop-up preview beresolusi penuh), kalkulasi <strong>Masa Kerja</strong> otomatis real-time dari TMT CPNS (NIP 18-digit, digit 9-14), pengisian manual untuk pegawai non-NIP, <strong>filter interaktif Multi-Select Jenis Pegawai</strong> (dengan tombol aksi cepat <em>[Semua]</em> dan <em>[Reset]</em>)/Eselon/Golongan/Status, penambahan data NIP/NIK/ID Kontrak, unggah foto profil, pengosongan NIP otomatis untuk non-ASN (selain PNS, CPNS, PPPK), serta fitur Import, <strong>Export Excel (.xlsx)</strong>, dan <strong>Export PDF (.pdf)</strong> lengkap dengan nomor RFID/ID Card, foto profil personil, dan sinkronisasi filter aktif.</p>
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
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-1"></i> Master Tanggal Merah & Kalender Libur</h6>
                                <p class="small text-muted mb-2"><code>/admin/master/tanggal-merah</code></p>
                                <p class="small mb-0">Pengelolaan kalender hari libur nasional & cuti bersama terintegrasi live dengan API <code>https://tanggalmerah.upset.dev/api/holidays?year={year}</code>. Dilengkapi <strong>Dropdown Pemilihan Tahun</strong>, tombol <strong>Tarik Data API</strong> dengan modal preview konfirmasi (pembeda data baru vs data yang sudah tersimpan), tampilan <strong>Kalender Interaktif 12 Bulan</strong> (highlight merah untuk Libur Nasional & oranye/amber untuk Cuti Bersama serta klik tanggal untuk detail/tambah), tampilan <strong>Tabel Data</strong>, penambahan/perubahan manual, pembersihan data tahun berjalan, dan ekspor Excel.</p>
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
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-database mr-1"></i> Database Merge & Extract</h6>
                                <p class="small text-muted mb-2"><code>/admin/pengaturan/application</code></p>
                                <p class="small mb-0">Ekstraksi dan penggabungan skema database serta sinkronisasi aplikasi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- MODUL INVENTARISASI -->
        <?php if ($canRenderForUser(['admin', 'super_administrator'])): ?>

        <div class="card menu-tutorial-card mb-3 role-section" data-roles="admin,super_administrator,all">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-weight-bold text-primary">
                    <i class="fas fa-boxes-stacked mr-2"></i> Modul Inventarisasi: Aset BMN Satker, DBR &amp; Sarpras Sekolah
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-boxes-stacked mr-1"></i> 1. Daftar Barang</h6>
                                <p class="small text-muted mb-2"><code>/admin/inventaris/barang</code> (Menu Lv2 Inventarisasi)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Masuk ke menu <strong>Inventarisasi &gt; Daftar Barang</strong>. Seluruh aset fisik BMN berada langsung di menu utama ini.</li>
                                    <li><strong>Klasifikasi Peruntukan Aset:</strong> Setiap barang memiliki status peruntukan yang jelas:
                                        <ul class="pl-3 mt-1">
                                            <li><span class="badge badge-primary px-1">Kantor</span> : Aset operasional kantor satker yang dapat dialokasikan ke ruangan-ruangan kantor (DBR).</li>
                                            <li><span class="badge badge-warning text-dark px-1">Mobiler</span> : Aset bantuan sarana pendidikan yang didistribusikan ke sekolah-sekolah penerima.</li>
                                            <li><span class="badge badge-purple text-white px-1" style="background:#7c3aed;">Item Lainnya</span> : Aset non-operasional ruangan / khusus (seperti peminjaman aset, aset tetap dalam renovasi/pengerjaan, atau aset belum terdata di ruangan).</li>
                                        </ul>
                                    </li>
                                    <li><strong>Bilah Filter Cepat Peruntukan:</strong> Di atas daftar data tersedia tombol filter instan (<em>Semua Barang</em>, <em>Kantor / Satker</em>, <em>Mobiler / Sekolah</em>, dan <em>Item Lainnya</em>) lengkap dengan penghitung total unit terkini.</li>
                                    <li><strong>Standar Kuantitas BMN (1 Kode Barang + 1 NUP = 1 Unit Fisik):</strong> Sesuai dengan kaidah penatausahaan BMN Kementerian Keuangan (SIMAN DJKN / SAKTI), setiap 1 baris yang memiliki pasangan Kode Barang dan NUP (Nomor Urut Pendaftaran) secara fisik selalu bernilai tepat 1 Unit. Pada formulir Tambah maupun Ubah Aset, field <em>Jumlah</em> dikunci otomatis menjadi 1 Unit untuk menjaga integritas data register fisik BMN.</li>
                                    <li><strong>⚡ Update Peruntukan Massal (Seluruh NUP / Sebagian Rentang NUP Terisolasi Spesifik per Kode, Nama &amp; Merk):</strong> Klik tombol <em>Update Peruntukan Massal (NUP)</em> untuk mengubah status peruntukan banyak barang sekaligus dalam 1 kali klik. Dropdown pilihan aset membedakan barang berdasarkan <strong>Kode Barang, Nama Barang, dan Merk/Tipe</strong> (sehingga barang dengan Kode Barang yang sama seperti Meja Siswa dan Meja Guru terpisah secara mandiri). Pilih lingkup: <strong>Seluruh NUP (Semua Unit)</strong> untuk memperbarui seluruh unit aset pada item tersebut sekaligus secara otomatis tanpa mempengaruhi item lain yang kodenya sama, atau <strong>Sebagian NUP (Rentang Tertentu)</strong> untuk memasukkan batas <em>NUP Awal</em> hingga <em>NUP Akhir</em> secara spesifik, lalu pilih target peruntukan (Kantor/Mobiler/Item Lainnya).</li>
                                    <li><strong>Pencatatan Aset, Kode Register Unik &amp; Dropdown Satuan/Ruangan:</strong> Formulir penambahan maupun pengubahan data inventaris dilengkapi: (1) <em>Kode Register</em> fisik SIMAN BMN dengan validasi keunikan ketat di database dan form (namun tetap opsional/boleh kosong jika belum memiliki stiker QR SIMAN), serta tombol salin instan di modal ubah; (2) Dropdown <em>Satuan</em> berbasis Select2 yang menyediakan opsi standar dan mendukung pengetikan satuan baru secara manual; (3) Dropdown <em>Lokasi Ruangan</em> cerdas yang merangkum seluruh ruangan kantor Satker sekaligus mendukung pengetikan nama ruangan baru; (4) Pilihan radio peruntukan aset.</li>
                                    <li><strong>Import SIMAN &amp; Export Excel (Loading Progress Real-Time &amp; Mekanisme Upsert):</strong> Unggah file Excel dari SIMAN / SAKTI BMN maupun format spreadsheet lainnya via modal <em>Import SIMAN</em>. Dilengkapi <strong>Loading Progress Real-Time</strong> (progress bar persentase 0–100%, ukuran upload file MB, pengukur waktu berjalan/timer, serta pelacak 5 tahapan proses dari pembacaan lembar data hingga penyimpanan database). Sistem secara cerdas melakukan pemetaan kolom secara presisi (mengecualikan kolom 'Jumlah Foto' atau 'Jumlah Lantai' dari kuantitas aset) dan menjalankan pencocokan: <strong>jika Kode Barang dan NUP sama persis dengan data yang sudah ada, sistem akan memperbarui (update)</strong> data aset tersebut tanpa mereset status ruangan maupun peruntukan yang telah disetel; <strong>jika Kode Barang atau NUP berbeda/baru, sistem akan menambahkan (insert)</strong> sebagai data aset baru dengan kuantitas baku 1 unit. Setelah proses selesai, kartu ringkasan hasil impor (total diproses, aset baru, dan aset di-update) akan ditampilkan secara transparan. Anda juga dapat mengunduh seluruh data ber-peruntukan melalui tombol <strong>Export Excel</strong>.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-success"><i class="fas fa-door-open mr-1"></i> 2. Inventaris Kantor (DBR)</h6>
                                <p class="small text-muted mb-2"><code>/admin/inventaris/dbr</code> (Menu Lv2 Inventarisasi)</p>
                                <ol class="pl-3 small mb-0">
                                    <li>Masuk ke menu <strong>Inventarisasi &gt; Inventaris Kantor</strong>. Menampilkan seluruh ruangan kerja kantor Satker PPS Riau beserta penanggung jawab ruangan. Dilengkapi fitur <strong>Tambah Ruangan</strong>, <strong>Ubah Data Ruangan</strong> (terintegrasi otomatis dengan master pegawai, kode, nama, lantai, dan keterangan), serta <strong>Hapus Ruangan</strong>.</li>
                                    <li>Klik tombol <strong>Kelola / Detail Barang</strong> pada ruangan untuk membuka penatausahaan aset. Tampilan detail dilengkapi 4 ubin KPI terstruktur (Penanggung Jawab, Lokasi Ruangan, Total Unit Fisik, dan Nilai Perolehan Total Aset BMN) serta navigasi tab rapi antara <em>Rekapitulasi DBR (Format Resmi)</em> dan <em>Daftar Detail Fisik Barang</em>.</li>
                                    <li><strong>Perhitungan Jumlah Unit DBR Berbasis NUP Unik:</strong> Pada rekapitulasi DBR, kolom <em>JUMLAH</em> dihitung murni berdasarkan banyaknya pasangan Kode Barang dan NUP unik (<code>COUNT(DISTINCT nup)</code>). Kode barang dan NUP yang sama tidak akan pernah dihitung lebih dari 1 unit, sehingga rekapitulasi DBR di layar, cetak PDF, dan export Excel selalu akurat mencerminkan unit fisik riil.</li>
                                    <li><strong>Alokasi Khusus Aset Kantor:</strong> Sistem secara otomatis menyaring aset unallocated agar hanya barang ber-peruntukan <strong>Kantor</strong> yang dialokasikan ke ruangan kantor Satker (aset mobiler sekolah disaring agar tidak tercampur).</li>
                                    <li><strong>Alokasikan Barang (Scan QR Code &amp; Alokasi Manual):</strong>
                                        <ul class="pl-3 mt-1">
                                            <li><strong>Scan QR Code / Register (Mobile Scanner Cepat):</strong> Memindai stiker fisik QR SIMAN BMN menggunakan kamera smartphone (Android/iOS) atau scanner barcode fisik USB/Bluetooth. Dilengkapi optimasi mobile: otomatis langsung mengaktifkan kamera belakang (rear camera) via native WebRTC <code>facingMode: "environment"</code> tanpa layar hitam, auto-adaptive aspect ratio, rendering inline (playsinline) bebas hambatan, tampilan bidik (viewfinder) layar penuh diperbesar dengan reticle corner, tombol <em>Ganti Kamera</em> 1-tap, tombol <em>Flash/Senter</em> di ruangan temaram, serta umpan balik audio beep dan getaran (haptic) instan.</li>
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
                    <div class="col-lg-4 col-md-12 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-info"><i class="fas fa-school mr-1"></i> 3. Inventaris Sekolah</h6>
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
                </div>
            </div>
        </div>

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
