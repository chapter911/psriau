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
    F --> G[Isi Tujuan, Sasaran & Laporan Hasil]
    G --> H[Unggah Foto Kegiatan & Dokumentasi Tiket/Nota]
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
                <small class="text-muted">Proses pengajuan cuti melalui modal, tanggal pengajuan otomatis terkunci, hingga ekspor dokumen (DOCX & PDF).</small>
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
    B --> C[Sistem Otomatis Mengisi Tanggal Pengajuan Terkunci & Data Pegawai]
    C --> D[Pilih Jenis Cuti, Alasan, Lamanya Cuti & Tanggal Mulai-Selesai]
    D --> E[Isi Alamat & Kontak Selama Cuti]
    E --> F[Klik Simpan Pengajuan Cuti]
    F --> G{Persetujuan Atasan / Admin}
    G -->|Disetujui| H[Ekspor Dokumen Resmi: DOCX Template form Surat Cuti.docx / PDF]
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
    <?php if ($canRenderForUser(['keuangan'])): ?>
    <div class="flowchart-card role-section" data-roles="keuangan,all">
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
    E --> F[Cetak Dokumentasi Resmi: SPT, SPPD 2 Hal, Kwitansi & Rincian Biaya, Nominatif]
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
    A["Kelola Master Data: Pegawai (PNS/CPNS/PPPK/PPNPN/Konsultan/Security/CS), Struktur Organisasi, MAK, Dasar SPT"] --> B[Kelola Paket Pekerjaan SIMAK Fisik & Konsultasi]
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
                <h5 class="font-weight-bold mb-1 text-danger"><i class="fas fa-user-gear mr-2"></i> Alur Kerja Super Administrator</h5>
                <small class="text-muted">Proses pengelolaan akun user, pemetaan role grup, pembagian hak akses menu, dan manajemen database.</small>
            </div>
            <div>
                <span class="badge badge-danger">Super Administrator</span>
            </div>
        </div>
        <div class="flowchart-card__body">
            <div class="mermaid-container mb-4">
                <pre class="mermaid">
graph TD
    A[Buka Manajemen User / User Group] --> B[Tambah / Edit Akun User & Mappping Pegawai]
    B --> C[Atur Hak Akses Menu Level 1, 2, 3 per Role]
    C --> D[Buka Application Setting: Merge / Extract Database & Maintenance]
                </pre>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                                    <li>Pilih Disposisi yang sudah disetujui, klik <strong>Buat Laporan</strong>.</li>
                                    <li>Isi Tujuan, Sasaran, dan Hasil Perjadinya.</li>
                                    <li>Unggah foto di tab <strong>Dokumentasi Kegiatan</strong> & tiket/nota di tab <strong>Dokumentasi Tiket</strong> (input teks bebas).</li>
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
                                    <li>Unduh PDF Surat Pernyataan resmi melalui tombol icon PDF.</li>
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
                                    <li>Klik tombol <strong>Ajukan Cuti</strong>.</li>
                                    <li><strong>Tanggal Pengajuan</strong> otomatis terkunci pada tanggal hari ini.</li>
                                    <li>Data pegawai (Nama, NIP, Jabatan, Masa Kerja) terisi secara otomatis.</li>
                                    <li>Pilih <strong>Jenis Cuti</strong> (Tahunan, Besar, Sakit, Melahirkan, Alasan Penting, atau Luar Tanggungan).</li>
                                    <li>Isi Alasan, Periode Tanggal Cuti, Alamat & Telepon selama cuti.</li>
                                    <li>Klik <strong>Simpan Pengajuan Cuti</strong>.</li>
                                    <li>Gunakan tombol <strong>DOCX</strong> atau <strong>PDF</strong> pada tabel untuk mengekspor dokumen Formulir Permintaan dan Pemberian Cuti resmi.</li>
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
                            <li>Klik tombol hijau <strong>Setujui</strong> atau tombol merah <strong>Tolak</strong>.</li>
                            <li>Setelah disetujui, PDF Disposisi akan tersemat <strong>Stempel Approved</strong> dari PPK &amp; Kasatker.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- MODUL KEUANGAN -->
        <?php if ($canRenderForUser(['keuangan'])): ?>
        <div class="card menu-tutorial-card mb-3 role-section" data-roles="keuangan,all">
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
                            <li>Buka menu Surat Tugas (`/admin/surat/perjalanan-dinas/surat-tugas`).</li>
                            <li>Gunakan tombol <strong>Setting Nomor Terakhir SPPD/Kwitansi</strong> untuk mengatur nomor awal auto-increment jika ingin melanjutkan dari penomoran sebelumnya (misal: isi <code>15</code> untuk memulai dari <code>016</code>).</li>
                            <li>Klik tombol kuning <strong>Update Verifikasi</strong> pada baris laporan untuk menentukan KOP Surat, MAK, Dasar SPT, Kode Nomor, dan memasukkan tarif Transport & Penginapan per periode.</li>
                            <li>Upload file PDF <strong>SPT TTD</strong> yang telah disahkan.</li>
                            <li>Cetak dokumen resmi: <strong>Surat Tugas (SPT)</strong>, <strong>SPPD (2 Halaman)</strong> (dengan format ringkas Golongan pada bagian 3.a seperti <code>III/a</code> dan Jabatan pelaksana), <strong>Kwitansi & Rincian Biaya</strong> (dengan "Kode Nomor" SPPD & "Nomor Bukti" Kwitansi terhubung otomatis dan nama penerima kapital), serta <strong>Daftar Nominatif</strong> (NIP otomatis hanya ditampilkan untuk personil berstatus PNS, CPNS, atau PPPK).</li>
                        </ol>
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
                                <p class="small mb-0">Pengelolaan data seluruh personil kantor Satker PPS Riau mencakup PNS, CPNS, PPPK, PPNPN, <strong>Konsultan Individual</strong>, <strong>Security (Tenaga Keamanan)</strong>, <strong>Cleaning Service (Tenaga Kebersihan)</strong>, dan Non-ASN Lainnya. Dilengkapi fitur filter Jenis Pegawai/Eselon/Golongan/Status, penambahan data NIP/NIK/ID Kontrak, unggah foto profil, serta fitur Import & Export Excel.</p>
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
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-users mr-1"></i> Manajemen User & Hak Akses Role</h6>
                                <p class="small text-muted mb-2"><code>/admin/utility/user</code> & <code>/admin/utility/user-group</code></p>
                                <p class="small mb-0">Tambah, edit, dan perbarui data user serta perubahan role secara dinamis dari master pegawai maupun input manual. Dilengkapi validasi role terintegrasi dan pengaturan hak akses menu per role (Akses, Add, Edit, Delete, Export, Import, Approval).</p>
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
