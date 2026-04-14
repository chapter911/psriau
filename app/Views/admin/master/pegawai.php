<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .pegawai-hero {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        color: #fff;
        background: linear-gradient(135deg, #0f4c81 0%, #0b7f6d 52%, #d97706 100%);
        box-shadow: 0 16px 40px rgba(16, 24, 40, .14);
    }

    .pegawai-hero .card-body {
        padding: 1.5rem;
    }

    .pegawai-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .35rem .75rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        font-size: .85rem;
        font-weight: 600;
        letter-spacing: .02em;
        margin-bottom: 1rem;
    }

    .pegawai-title {
        font-size: clamp(1.8rem, 3vw, 2.45rem);
        margin-bottom: .55rem;
        font-weight: 700;
    }

    .pegawai-subtitle {
        max-width: 720px;
        margin-bottom: 1.25rem;
        color: rgba(255, 255, 255, .9);
        line-height: 1.65;
    }

    .pegawai-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .pegawai-meta .badge {
        padding: .55rem .8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        color: #fff;
        font-weight: 600;
    }

    .pegawai-panel {
        border: 1px solid #e7edf4;
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .pegawai-panel .card-body {
        padding: 1.25rem;
    }
</style>

<div class="container-fluid px-0">
    <div class="card pegawai-hero mb-4">
        <div class="card-body">
            <div class="pegawai-kicker">
                <i class="fas fa-user-tie"></i>
                Menu Master
            </div>
            <h1 class="pegawai-title">Pegawai</h1>
            <p class="pegawai-subtitle">
                Halaman ini disiapkan sebagai pintu masuk pengelolaan data pegawai di dalam kelompok menu Master.
                Struktur menu sudah tersedia, sehingga modul ini bisa diisi fitur CRUD atau data referensi berikutnya tanpa mengubah sidebar lagi.
            </p>
            <div class="pegawai-meta">
                <span class="badge"><i class="fas fa-sitemap mr-1"></i> Master</span>
                <span class="badge"><i class="fas fa-link mr-1"></i> admin/master/pegawai</span>
                <span class="badge"><i class="fas fa-shield-alt mr-1"></i> Tersambung ke akses menu</span>
            </div>
        </div>
    </div>

    <div class="card pegawai-panel">
        <div class="card-body">
            <h4 class="mb-3">Status Modul</h4>
            <p class="mb-0 text-muted">
                Menu Pegawai sudah tersedia di sidebar. Silakan lanjutkan pengisian fitur dan data master pegawai sesuai kebutuhan aplikasi.
            </p>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>