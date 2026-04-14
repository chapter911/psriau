<?= $this->extend('layouts/admin'); ?>

<?= $this->section('content'); ?>
<style>
    .jabatan-hero {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        color: #fff;
        background: linear-gradient(130deg, #0b3c68 0%, #0e7490 50%, #b45309 100%);
        box-shadow: 0 16px 40px rgba(16, 24, 40, .14);
    }

    .jabatan-hero .card-body {
        padding: 1.5rem;
    }

    .jabatan-kicker {
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

    .jabatan-title {
        font-size: clamp(1.8rem, 3vw, 2.45rem);
        margin-bottom: .55rem;
        font-weight: 700;
    }

    .jabatan-subtitle {
        max-width: 720px;
        margin-bottom: 1.25rem;
        color: rgba(255, 255, 255, .9);
        line-height: 1.65;
    }

    .jabatan-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .jabatan-meta .badge {
        padding: .55rem .8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        color: #fff;
        font-weight: 600;
    }

    .jabatan-panel {
        border: 1px solid #e7edf4;
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .jabatan-panel .card-body {
        padding: 1.25rem;
    }
</style>

<div class="container-fluid px-0">
    <div class="card jabatan-hero mb-4">
        <div class="card-body">
            <div class="jabatan-kicker">
                <i class="fas fa-user-tag"></i>
                Menu Master
            </div>
            <h1 class="jabatan-title">Jabatan</h1>
            <p class="jabatan-subtitle">
                Halaman ini disiapkan sebagai titik awal pengelolaan master jabatan.
                Struktur submenu sudah ditambahkan di dalam menu Master, sehingga pengembangan fitur lanjutan bisa langsung dilanjutkan.
            </p>
            <div class="jabatan-meta">
                <span class="badge"><i class="fas fa-sitemap mr-1"></i> Master</span>
                <span class="badge"><i class="fas fa-link mr-1"></i> admin/master/jabatan</span>
                <span class="badge"><i class="fas fa-shield-alt mr-1"></i> Tersambung ke akses menu</span>
            </div>
        </div>
    </div>

    <div class="card jabatan-panel">
        <div class="card-body">
            <h4 class="mb-3">Status Modul</h4>
            <p class="mb-0 text-muted">
                Menu Jabatan sudah tersedia di sidebar. Silakan lanjutkan pengisian fitur dan data master jabatan sesuai kebutuhan aplikasi.
            </p>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>