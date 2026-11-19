<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Bagan Turnamen Tenis Meja - Sistem Gugur'); ?> | Satker PPS Riau</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --pupr-navy: #002244;
            --pupr-blue: #0b3c74;
            --pupr-light-blue: #e8f0fe;
            --pupr-yellow: #f7a81b;
            --pupr-gold: #d48b00;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --bg-canvas: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-canvas);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image:
                radial-gradient(#cbd5e1 1px, transparent 1px),
                radial-gradient(#cbd5e1 1px, #f8fafc 1px);
            background-size: 32px 32px;
            background-position: 0 0, 16px 16px;
        }

        /* HEADER STYLES */
        .site-header {
            background: linear-gradient(135deg, #001f3f 0%, #002244 50%, #0b3c74 100%);
            color: #ffffff;
            padding: 20px 24px;
            box-shadow: 0 4px 20px rgba(0, 34, 68, 0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            color: #ffffff;
        }

        .header-logo {
            height: 54px;
            width: auto;
            border-radius: 8px;
            background: #ffffff;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            object-fit: contain;
        }

        .header-titles h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
        }

        .header-titles h1 span {
            color: var(--pupr-yellow);
        }

        .header-titles p {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 2px;
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
            outline: none;
        }

        .btn-action-primary {
            background-color: var(--pupr-yellow);
            color: #001f3f;
        }
        .btn-action-primary:hover {
            background-color: #e59a14;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(247, 168, 27, 0.35);
        }

        .btn-action-shuffle {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
        }
        .btn-action-shuffle:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
        }

        .btn-action-pdf {
            background-color: #3b82f6;
            color: #ffffff;
        }
        .btn-action-pdf:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }

        .btn-action-light {
            background-color: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-action-light:hover {
            background-color: rgba(255, 255, 255, 0.22);
        }

        /* MAIN WRAPPER */
        .main-wrapper {
            max-width: 1600px;
            width: 100%;
            margin: 20px auto;
            padding: 0 20px 40px;
            flex: 1;
        }

        /* INFO & PODIUM BANNER */
        .tournament-banner {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border-color);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .tournament-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .meta-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f1f5f9;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .meta-pill i {
            color: var(--pupr-blue);
        }

        .badge-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-ongoing {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-completed {
            background-color: #d1fae5;
            color: #065f46;
        }

        /* PODIUM CARDS */
        .podium-box {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .podium-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid var(--border-color);
        }

        .podium-item.champion {
            background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);
            border-color: #facc15;
            box-shadow: 0 4px 12px rgba(250, 204, 21, 0.25);
        }

        .podium-item.runner-up {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-color: #cbd5e1;
        }

        .podium-item.third {
            background: linear-gradient(135deg, #fed7aa 0%, #ffedd5 100%);
            border-color: #fdba74;
        }

        .podium-medal {
            font-size: 1.4rem;
        }

        .podium-details h4 {
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }
        .podium-item.champion .podium-details h4 {
            color: #854d0e;
        }

        .podium-details p {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0;
            white-space: nowrap;
        }

        /* BRACKET WRAPPER & COLUMNS */
        .bracket-scroll-container {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            overflow-x: auto;
            position: relative;
        }

        .bracket-tree {
            display: flex;
            gap: 50px;
            min-width: max-content;
            align-items: stretch;
            padding: 10px 0;
        }

        .bracket-round-column {
            display: flex;
            flex-direction: column;
            width: 320px;
            flex-shrink: 0;
        }

        .round-header {
            text-align: center;
            background: linear-gradient(135deg, #002244 0%, #0b3c74 100%);
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            box-shadow: 0 3px 8px rgba(0, 34, 68, 0.15);
        }

        .round-header h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--pupr-yellow);
        }

        .round-header span {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .round-matches-list {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            flex-grow: 1;
            gap: 28px;
        }

        /* MATCH CARD */
        .match-card {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transition: all 0.25s ease;
            position: relative;
        }

        .match-card:hover {
            border-color: var(--pupr-blue);
            box-shadow: 0 6px 16px rgba(11, 60, 116, 0.12);
            transform: translateY(-2px);
        }

        .match-card.is-final {
            border-color: var(--pupr-yellow);
            border-width: 2px;
            box-shadow: 0 4px 16px rgba(247, 168, 27, 0.2);
        }

        .match-card-header {
            background: #f1f5f9;
            padding: 6px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            border-bottom: 1px solid #e2e8f0;
        }

        .match-card.is-final .match-card-header {
            background: #fef3c7;
            color: #92400e;
        }

        /* PLAYER SLOTS */
        .player-slot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            gap: 10px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }

        .player-slot:last-of-type {
            border-bottom: none;
        }

        .player-slot.is-winner {
            background-color: #ecfdf5;
        }

        .player-slot.is-winner .player-name {
            font-weight: 800;
            color: #065f46;
        }

        .player-slot.is-bye {
            opacity: 0.6;
            background-color: #f8fafc;
        }

        .player-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            overflow: hidden;
        }

        .player-seed {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: #e2e8f0;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .player-slot.is-winner .player-seed {
            background: #10b981;
            color: #ffffff;
        }

        .player-name-wrap {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .player-name {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--text-dark);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .player-balai-badge {
            display: inline-block;
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .player-score-ctrl {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .score-input {
            width: 44px;
            height: 34px;
            text-align: center;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-dark);
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .score-input:focus {
            border-color: var(--pupr-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(11, 60, 116, 0.15);
        }

        .btn-pick-winner {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.8rem;
        }

        .btn-pick-winner:hover {
            border-color: #10b981;
            color: #10b981;
            background: #ecfdf5;
        }

        .player-slot.is-winner .btn-pick-winner {
            background: #10b981;
            border-color: #10b981;
            color: #ffffff;
        }

        .match-actions-footer {
            background: #f8fafc;
            padding: 6px 12px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 6px;
        }

        .btn-save-match {
            font-size: 0.74rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            background: var(--pupr-blue);
            color: #ffffff;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-save-match:hover {
            background: #002244;
        }

        /* MODAL STYLES */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 640px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.25s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(12px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--pupr-navy);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            background: transparent;
            border: none;
            font-size: 1.25rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .modal-body {
            padding: 20px 24px;
            overflow-y: auto;
            flex: 1;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            font-size: 0.95rem;
            font-family: inherit;
        }
        .form-control:focus {
            border-color: var(--pupr-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(11, 60, 116, 0.15);
        }

        .participant-count-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
        }

        .btn-count-opt {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            background: #f8fafc;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-count-opt.active {
            background: var(--pupr-blue);
            color: #ffffff;
            border-color: var(--pupr-blue);
        }

        .quick-chips-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px;
            margin-bottom: 12px;
        }

        .quick-chip {
            font-size: 0.74rem;
            padding: 4px 10px;
            border-radius: 20px;
            background: #e2e8f0;
            color: #334155;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.15s ease;
        }

        .quick-chip:hover {
            background: var(--pupr-yellow);
            color: #001f3f;
        }

        .participant-inputs-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 280px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .participant-input-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .participant-num {
            width: 32px;
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--text-muted);
            text-align: right;
            flex-shrink: 0;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* FOOTER */
        .site-footer {
            text-align: center;
            padding: 24px;
            font-size: 0.82rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            background: #ffffff;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .site-header { padding: 14px 16px; }
            .header-titles h1 { font-size: 1.1rem; }
            .tournament-banner { flex-direction: column; align-items: flex-start; }
            .bracket-tree { gap: 28px; }
            .bracket-round-column { width: 280px; }
        }
    </style>
</head>
<body>

    <!-- TOP HEADER -->
    <header class="site-header">
        <div class="header-container">
            <div class="header-brand">
                <img src="<?= esc($logoUrl); ?>" alt="Logo Satker PPS" class="header-logo" onerror="this.src='https://via.placeholder.com/54?text=PUPR'">
                <div class="header-titles">
                    <h1><i class="fas fa-table-tennis-paddle-ball text-warning mr-1"></i> TURNAMEN <span>TENIS MEJA</span></h1>
                    <p><?= esc($officialName); ?> • <?= esc($subHeader); ?></p>
                </div>
            </div>

            <div class="header-actions">
                <!-- ACAK POSISI (SUPERADMINISTRATOR ONLY) -->
                <button type="button" class="btn-action btn-action-shuffle" id="btnAcakPosisi">
                    <i class="fas fa-shuffle"></i> Acak Posisi
                    <span style="font-size: 0.7rem; background: rgba(0,0,0,0.25); padding: 2px 6px; border-radius: 4px; margin-left: 2px;">
                        <i class="fas fa-lock"></i> Superadmin
                    </span>
                </button>

                <!-- EKSPOR PDF -->
                <a href="<?= site_url('tenismeja/pdf'); ?>" target="_blank" class="btn-action btn-action-pdf" id="btnEksporPdf">
                    <i class="fas fa-file-pdf"></i> Ekspor PDF
                </a>

                <!-- ATUR PESERTA -->
                <button type="button" class="btn-action btn-action-primary" id="btnOpenSetup">
                    <i class="fas fa-users-gear"></i> Atur Peserta
                </button>

                <!-- RESET SKOR -->
                <button type="button" class="btn-action btn-action-light" id="btnResetSkor" title="Reset Skor Pertandingan">
                    <i class="fas fa-rotate-left"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- MAIN BODY -->
    <main class="main-wrapper">

        <!-- TOURNAMENT INFO & PODIUM -->
        <section class="tournament-banner">
            <div class="tournament-meta">
                <div>
                    <h2 style="font-family: 'Montserrat', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--pupr-navy);" id="tournamentTitle">
                        <?= esc($tournament['title'] ?? 'Turnamen Tenis Meja Sistem Gugur'); ?>
                    </h2>
                    <div style="display: flex; gap: 8px; align-items: center; margin-top: 6px; flex-wrap: wrap;">
                        <span class="meta-pill">
                            <i class="fas fa-trophy"></i>
                            <span id="tournamentCategory"><?= esc($tournament['category'] ?? 'Tunggal Putra'); ?></span>
                        </span>
                        <span class="meta-pill">
                            <i class="fas fa-users"></i>
                            <span id="tournamentCount"><?= (int) ($tournament['participant_count'] ?? 0); ?> Peserta</span>
                        </span>
                        <span class="badge-status <?= ($tournament['status'] ?? 'ongoing') === 'completed' ? 'badge-completed' : 'badge-ongoing'; ?>" id="tournamentStatusBadge">
                            <i class="fas <?= ($tournament['status'] ?? 'ongoing') === 'completed' ? 'fa-check-circle' : 'fa-clock'; ?> mr-1"></i>
                            <?= ($tournament['status'] ?? 'ongoing') === 'completed' ? 'Turnamen Selesai' : 'Sedang Berlangsung'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- PODIUM CARDS -->
            <div class="podium-box" id="podiumContainer">
                <!-- JUARA 1 -->
                <div class="podium-item champion">
                    <div class="podium-medal">🥇</div>
                    <div class="podium-details">
                        <h4>Juara 1 (Champion)</h4>
                        <p id="podiumChampion"><?= esc($tournament['bracket_json']['podium']['champion']['name'] ?? '- Menunggu Final -'); ?></p>
                    </div>
                </div>

                <!-- JUARA 2 -->
                <div class="podium-item runner-up">
                    <div class="podium-medal">🥈</div>
                    <div class="podium-details">
                        <h4>Juara 2 (Runner-Up)</h4>
                        <p id="podiumRunnerUp"><?= esc($tournament['bracket_json']['podium']['runner_up']['name'] ?? '- Menunggu Final -'); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- INTERACTIVE BRACKET SECTION -->
        <section class="bracket-scroll-container">
            <div class="bracket-tree" id="bracketTree">
                <!-- RENDERED BY JAVASCRIPT / PHP -->
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="site-footer">
        <p>&copy; <?= date('Y'); ?> <?= esc($officialName); ?> • Satker Pelaksanaan Prasarana Permukiman Provinsi Riau. Sistem Gugur Turnamen Tenis Meja.</p>
    </footer>

    <!-- MODAL: ATUR / BUAT BAGAN TURNAMEN -->
    <div class="modal-overlay" id="setupModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fas fa-sitemap text-primary"></i> Atur Peserta & Buat Bagan Turnamen</h3>
                <button type="button" class="modal-close" id="btnCloseSetup">&times;</button>
            </div>
            <div class="modal-body">
                <form id="setupForm">
                    <div class="form-group">
                        <label for="formTitle">Nama Turnamen</label>
                        <input type="text" id="formTitle" class="form-control" value="<?= esc($tournament['title'] ?? 'Turnamen Tenis Meja Antar Balai PUPR'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="formCategory">Kategori</label>
                        <select id="formCategory" class="form-control">
                            <option value="Tunggal Putra" <?= ($tournament['category'] ?? '') === 'Tunggal Putra' ? 'selected' : ''; ?>>Tunggal Putra</option>
                            <option value="Tunggal Putri" <?= ($tournament['category'] ?? '') === 'Tunggal Putri' ? 'selected' : ''; ?>>Tunggal Putri</option>
                            <option value="Ganda Putra" <?= ($tournament['category'] ?? '') === 'Ganda Putra' ? 'selected' : ''; ?>>Ganda Putra</option>
                            <option value="Ganda Campuran" <?= ($tournament['category'] ?? '') === 'Ganda Campuran' ? 'selected' : ''; ?>>Ganda Campuran</option>
                            <option value="Beregu Antar Balai" <?= ($tournament['category'] ?? '') === 'Beregu Antar Balai' ? 'selected' : ''; ?>>Beregu Antar Balai</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Peserta</label>
                        <div class="participant-count-buttons">
                            <button type="button" class="btn-count-opt" data-count="4">4 Peserta</button>
                            <button type="button" class="btn-count-opt active" data-count="8">8 Peserta</button>
                            <button type="button" class="btn-count-opt" data-count="16">16 Peserta</button>
                            <button type="button" class="btn-count-opt" data-count="32">32 Peserta</button>
                        </div>
                        <input type="number" id="customCount" class="form-control" placeholder="Atau ketik jumlah peserta bebas (min 2, max 32)" min="2" max="32" value="<?= (int) ($tournament['participant_count'] ?? 8); ?>">
                    </div>

                    <div class="form-group">
                        <label>Klik Cepat Pilihan Balai PUPR (Opsional untuk Mengisi Nama Peserta):</label>
                        <div class="quick-chips-wrap">
                            <?php foreach ($defaultBalais as $b): ?>
                                <span class="quick-chip" onclick="fillNextEmptyParticipant('<?= esc($b); ?>')"><?= esc($b); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Daftar Nama Peserta / Asal Balai:</label>
                        <div class="participant-inputs-container" id="participantInputs">
                            <!-- Injected dynamically -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action btn-action-light" id="btnCancelSetup" style="color: #475569;">Batal</button>
                <button type="button" class="btn-action btn-action-primary" id="btnSubmitSetup">
                    <i class="fas fa-diagram-project"></i> Generate Bagan Turnamen
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // API ENDPOINTS
        const API_SETUP = '<?= site_url("tenismeja/api/setup"); ?>';
        const API_ACAK = '<?= site_url("tenismeja/api/acak-posisi"); ?>';
        const API_UPDATE_MATCH = '<?= site_url("tenismeja/api/update-match"); ?>';
        const API_RESET = '<?= site_url("tenismeja/api/reset"); ?>';
        const API_DATA = '<?= site_url("tenismeja/api/data"); ?>';

        // Initial Data injected from PHP
        let tournamentData = <?= json_encode($tournament ?? null, JSON_UNESCAPED_UNICODE); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            if (tournamentData) {
                renderBracket(tournamentData);
            }

            initEventHandlers();
        });

        // RENDER BRACKET FUNCTION
        function renderBracket(data) {
            if (!data || !data.bracket_json || !data.bracket_json.rounds) {
                document.getElementById('bracketTree').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #64748b; width: 100%;">
                        <i class="fas fa-sitemap" style="font-size: 3rem; margin-bottom: 12px; color: #94a3b8;"></i>
                        <p style="font-weight: 700; font-size: 1.1rem;">Belum ada bagan pertandingan yang aktif.</p>
                        <p style="font-size: 0.9rem;">Klik tombol "Atur Peserta" di atas untuk membuat bagan turnamen sistem gugur.</p>
                    </div>
                `;
                return;
            }

            // Update Header Meta
            document.getElementById('tournamentTitle').textContent = data.title || 'Turnamen Tenis Meja';
            document.getElementById('tournamentCategory').textContent = data.category || 'Tunggal Putra';
            document.getElementById('tournamentCount').textContent = (data.participant_count || 0) + ' Peserta';

            const statusBadge = document.getElementById('tournamentStatusBadge');
            if (data.status === 'completed') {
                statusBadge.className = 'badge-status badge-completed';
                statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Turnamen Selesai';
            } else {
                statusBadge.className = 'badge-status badge-ongoing';
                statusBadge.innerHTML = '<i class="fas fa-clock mr-1"></i> Sedang Berlangsung';
            }

            // Update Podium
            const podium = data.bracket_json.podium || {};
            document.getElementById('podiumChampion').textContent = podium.champion ? podium.champion.name : '- Menunggu Final -';
            document.getElementById('podiumRunnerUp').textContent = podium.runner_up ? podium.runner_up.name : '- Menunggu Final -';

            // Render Round Columns
            const rounds = data.bracket_json.rounds;
            const totalRounds = parseInt(data.bracket_json.total_rounds || Object.keys(rounds).length);
            const container = document.getElementById('bracketTree');
            container.innerHTML = '';

            for (let r = 1; r <= totalRounds; r++) {
                const roundData = rounds[r];
                if (!roundData) continue;

                const isFinalRound = (r === totalRounds);
                const col = document.createElement('div');
                col.className = 'bracket-round-column';

                let roundSub = (r === totalRounds) ? 'Perebutan Juara 1 & 2' : (roundData.matches.length + ' Pertandingan');

                col.innerHTML = `
                    <div class="round-header ${isFinalRound ? 'is-final' : ''}">
                        <h3>${escapeHtml(roundData.name || ('Babak ' + r))}</h3>
                        <span>${roundSub}</span>
                    </div>
                    <div class="round-matches-list" id="roundMatchesList_${r}"></div>
                `;

                container.appendChild(col);

                const matchesList = col.querySelector(`#roundMatchesList_${r}`);

                roundData.matches.forEach((m) => {
                    const matchCard = createMatchCardElement(m, isFinalRound);
                    matchesList.appendChild(matchCard);
                });
            }
        }

        // CREATE SINGLE MATCH CARD
        function createMatchCardElement(match, isFinal) {
            const card = document.createElement('div');
            card.className = `match-card ${isFinal ? 'is-final' : ''}`;
            card.id = `card_${match.id}`;

            const p1 = match.player1;
            const p2 = match.player2;

            const isP1Winner = match.winner && p1 && match.winner.name === p1.name;
            const isP2Winner = match.winner && p2 && match.winner.name === p2.name;

            const isByeMatch = match.is_bye || (p2 && p2.is_bye);

            card.innerHTML = `
                <div class="match-card-header">
                    <span><i class="fas fa-gamepad mr-1"></i> Match #${match.match_number}</span>
                    <span>${match.status === 'completed' ? '<span style="color: #10b981; font-weight: 800;"><i class="fas fa-check"></i> SELESAI</span>' : '<span style="color: #f59e0b;"><i class="fas fa-hourglass-half"></i> PENDING</span>'}</span>
                </div>

                <!-- PLAYER 1 -->
                <div class="player-slot ${isP1Winner ? 'is-winner' : ''} ${!p1 ? 'is-empty' : ''}">
                    <div class="player-info">
                        <div class="player-seed">${p1 ? (p1.seed || '•') : '-'}</div>
                        <div class="player-name-wrap">
                            <div class="player-name">${p1 ? escapeHtml(p1.name) : '<em style="color:#94a3b8">Menunggu Pemenang</em>'}</div>
                        </div>
                    </div>
                    <div class="player-score-ctrl">
                        <input type="number" min="0" max="99" class="score-input" id="score1_${match.id}" value="${match.score1 !== null ? match.score1 : ''}" placeholder="0" ${!p1 || isByeMatch ? 'disabled' : ''}>
                        <button type="button" class="btn-pick-winner" title="Tandai sebagai Pemenang" onclick="setMatchWinner('${match.id}', '${p1 ? escapeHtml(p1.name) : ''}')" ${!p1 || isByeMatch ? 'disabled' : ''}>
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>

                <!-- PLAYER 2 -->
                <div class="player-slot ${isP2Winner ? 'is-winner' : ''} ${!p2 ? 'is-empty' : ''} ${p2 && p2.is_bye ? 'is-bye' : ''}">
                    <div class="player-info">
                        <div class="player-seed">${p2 ? (p2.seed || '•') : '-'}</div>
                        <div class="player-name-wrap">
                            <div class="player-name">${p2 ? escapeHtml(p2.name) : '<em style="color:#94a3b8">Menunggu Pemenang</em>'}</div>
                        </div>
                    </div>
                    <div class="player-score-ctrl">
                        <input type="number" min="0" max="99" class="score-input" id="score2_${match.id}" value="${match.score2 !== null ? match.score2 : ''}" placeholder="0" ${!p2 || isByeMatch ? 'disabled' : ''}>
                        <button type="button" class="btn-pick-winner" title="Tandai sebagai Pemenang" onclick="setMatchWinner('${match.id}', '${p2 ? escapeHtml(p2.name) : ''}')" ${!p2 || isByeMatch ? 'disabled' : ''}>
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>

                ${!isByeMatch && p1 && p2 ? `
                <div class="match-actions-footer">
                    <button type="button" class="btn-save-match" onclick="saveMatchScores('${match.id}')">
                        <i class="fas fa-floppy-disk mr-1"></i> Simpan Skor
                    </button>
                </div>
                ` : ''}
            `;

            return card;
        }

        // SAVE MATCH SCORES
        async function saveMatchScores(matchId) {
            const score1Input = document.getElementById(`score1_${matchId}`);
            const score2Input = document.getElementById(`score2_${matchId}`);

            const s1 = score1Input ? score1Input.value.trim() : '';
            const s2 = score2Input ? score2Input.value.trim() : '';

            const formData = new FormData();
            formData.append('match_id', matchId);
            formData.append('score1', s1);
            formData.append('score2', s2);

            try {
                const response = await fetch(API_UPDATE_MATCH, {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.status === 'success') {
                    tournamentData = res.tournament;
                    renderBracket(tournamentData);
                    showToast('success', res.message || 'Skor pertandingan berhasil disimpan.');
                } else {
                    showToast('error', res.message || 'Gagal menyimpan skor pertandingan.');
                }
            } catch (err) {
                showToast('error', 'Terjadi kesalahan sistem saat menyimpan skor.');
            }
        }

        // SET MATCH WINNER DIRECTLY
        async function setMatchWinner(matchId, winnerName) {
            if (!winnerName) return;

            const score1Input = document.getElementById(`score1_${matchId}`);
            const score2Input = document.getElementById(`score2_${matchId}`);

            const s1 = score1Input ? score1Input.value.trim() : '';
            const s2 = score2Input ? score2Input.value.trim() : '';

            const formData = new FormData();
            formData.append('match_id', matchId);
            formData.append('winner', winnerName);
            formData.append('score1', s1);
            formData.append('score2', s2);

            try {
                const response = await fetch(API_UPDATE_MATCH, {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.status === 'success') {
                    tournamentData = res.tournament;
                    renderBracket(tournamentData);
                    showToast('success', `${winnerName} memenangkan pertandingan!`);
                } else {
                    showToast('error', res.message || 'Gagal menandai pemenang.');
                }
            } catch (err) {
                showToast('error', 'Terjadi kesalahan sistem saat memperbarui pemenang.');
            }
        }

        // TOAST NOTIFICATION HELPER (RULE 1: NOTIFIKASI TIDAK DOUBLE)
        let activeToastInstance = null;
        function showToast(icon, title) {
            if (activeToastInstance) {
                Swal.close();
            }
            activeToastInstance = Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 2800,
                timerProgressBar: true
            });
        }

        // INITIALIZE ALL EVENT HANDLERS
        function initEventHandlers() {
            // ACAK POSISI DENGAN PASSWORD SUPERADMINISTRATOR
            const btnAcak = document.getElementById('btnAcakPosisi');
            btnAcak.addEventListener('click', function () {
                Swal.fire({
                    title: '<span style="color:#002244; font-family: Montserrat, sans-serif; font-size:1.25rem;"><i class="fas fa-shield-halved text-danger mr-2"></i> Verifikasi Superadministrator</span>',
                    html: `
                        <p style="font-size:0.88rem; color:#64748b; margin-bottom:14px;">
                            Fitur pengacakan posisi bagan hanya dapat dilakukan oleh <strong>Superadministrator</strong>.<br>Silakan masukkan password akun superadministrator:
                        </p>
                        <div style="position:relative; width:100%; max-width:320px; margin:0 auto;">
                            <input type="password" id="swal_admin_pwd" class="swal2-input" placeholder="Password Superadministrator" style="width:100%; margin:0; padding-right:40px;">
                            <i class="fas fa-eye" id="togglePwdEye" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#94a3b8;"></i>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-shuffle mr-1"></i> Verifikasi & Acak Posisi',
                    cancelButtonText: 'Batal',
                    didOpen: () => {
                        const input = document.getElementById('swal_admin_pwd');
                        const eye = document.getElementById('togglePwdEye');
                        input.focus();
                        eye.addEventListener('click', () => {
                            if (input.type === 'password') {
                                input.type = 'text';
                                eye.className = 'fas fa-eye-slash text-danger';
                            } else {
                                input.type = 'password';
                                eye.className = 'fas fa-eye';
                            }
                        });
                        input.addEventListener('keyup', (e) => {
                            if (e.key === 'Enter') {
                                Swal.clickConfirm();
                            }
                        });
                    },
                    preConfirm: () => {
                        const pwd = document.getElementById('swal_admin_pwd').value.trim();
                        if (!pwd) {
                            Swal.showValidationMessage('Password Superadministrator wajib diisi.');
                            return false;
                        }
                        return pwd;
                    }
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        const pwd = result.value;

                        Swal.fire({
                            title: 'Memverifikasi Otorisasi...',
                            text: 'Memeriksa kredensial akun superadministrator.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        const formData = new FormData();
                        formData.append('password', pwd);

                        try {
                            const response = await fetch(API_ACAK, {
                                method: 'POST',
                                body: formData
                            });
                            const res = await response.json();

                            if (res.status === 'success') {
                                tournamentData = res.tournament;
                                renderBracket(tournamentData);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Diacak!',
                                    text: res.message || 'Posisi peserta turnamen tenis meja berhasil diacak.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Otorisasi Gagal!',
                                    text: res.message || 'Password Superadministrator salah.',
                                    confirmButtonColor: '#0b3c74'
                                });
                            }
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan Sistem',
                                text: 'Gagal terhubung dengan server untuk verifikasi otorisasi.',
                                confirmButtonColor: '#0b3c74'
                            });
                        }
                    }
                });
            });

            // RESET SKOR
            const btnReset = document.getElementById('btnResetSkor');
            btnReset.addEventListener('click', function () {
                Swal.fire({
                    title: 'Reset Skor Pertandingan?',
                    text: 'Seluruh skor dan pemenang babak akan diatur ulang kembali ke posisi awal. Nama dan posisi peserta tetap dipertahankan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-rotate-left mr-1"></i> Ya, Reset Skor',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await fetch(API_RESET, { method: 'POST' });
                            const res = await response.json();

                            if (res.status === 'success') {
                                tournamentData = res.tournament;
                                renderBracket(tournamentData);
                                showToast('success', res.message || 'Skor pertandingan berhasil direset.');
                            } else {
                                showToast('error', res.message || 'Gagal mereset skor.');
                            }
                        } catch (err) {
                            showToast('error', 'Gagal mereset skor pertandingan.');
                        }
                    }
                });
            });

            // MODAL SETUP OPEN / CLOSE
            const modal = document.getElementById('setupModal');
            const btnOpenSetup = document.getElementById('btnOpenSetup');
            const btnCloseSetup = document.getElementById('btnCloseSetup');
            const btnCancelSetup = document.getElementById('btnCancelSetup');

            btnOpenSetup.addEventListener('click', () => {
                populateParticipantInputs();
                modal.classList.add('active');
            });

            btnCloseSetup.addEventListener('click', () => modal.classList.remove('active'));
            btnCancelSetup.addEventListener('click', () => modal.classList.remove('active'));

            // PARTICIPANT COUNT BUTTONS
            const countButtons = document.querySelectorAll('.btn-count-opt');
            const customCountInput = document.getElementById('customCount');

            countButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    countButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const c = parseInt(this.dataset.count);
                    customCountInput.value = c;
                    generateInputFields(c);
                });
            });

            customCountInput.addEventListener('input', function () {
                let val = parseInt(this.value);
                if (val && val >= 2 && val <= 32) {
                    countButtons.forEach(b => {
                        if (parseInt(b.dataset.count) === val) {
                            b.classList.add('active');
                        } else {
                            b.classList.remove('active');
                        }
                    });
                    generateInputFields(val);
                }
            });

            // SUBMIT SETUP FORM
            document.getElementById('btnSubmitSetup').addEventListener('click', async function () {
                const title = document.getElementById('formTitle').value.trim();
                const category = document.getElementById('formCategory').value;

                const inputs = document.querySelectorAll('.participant-name-input');
                const participantList = [];

                inputs.forEach(inp => {
                    const v = inp.value.trim();
                    if (v) participantList.push(v);
                });

                if (participantList.length < 2) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peserta Kurang',
                        text: 'Silakan isi minimal 2 nama peserta atau balai.',
                        confirmButtonColor: '#0b3c74'
                    });
                    return;
                }

                modal.classList.remove('active');

                Swal.fire({
                    title: 'Membuat Bagan Turnamen...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const formData = new FormData();
                formData.append('title', title);
                formData.append('category', category);
                formData.append('participants', JSON.stringify(participantList));

                try {
                    const response = await fetch(API_SETUP, {
                        method: 'POST',
                        body: formData
                    });
                    const res = await response.json();

                    if (res.status === 'success') {
                        tournamentData = res.tournament;
                        renderBracket(tournamentData);
                        Swal.fire({
                            icon: 'success',
                            title: 'Bagan Berhasil Dibuat!',
                            text: res.message || 'Bagan turnamen tenis meja berhasil dibuat.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Membuat Bagan',
                            text: res.message || 'Terjadi kesalahan saat membuat bagan turnamen.',
                            confirmButtonColor: '#0b3c74'
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: 'Gagal menghubungi server untuk membuat bagan turnamen.',
                        confirmButtonColor: '#0b3c74'
                    });
                }
            });
        }

        // GENERATE INPUT FIELDS FOR MODAL
        function generateInputFields(count, existingNames = []) {
            const container = document.getElementById('participantInputs');
            container.innerHTML = '';

            for (let i = 1; i <= count; i++) {
                const val = existingNames[i - 1] || '';
                const row = document.createElement('div');
                row.className = 'participant-input-row';
                row.innerHTML = `
                    <div class="participant-num">#${i}</div>
                    <input type="text" class="form-control participant-name-input" id="partInput_${i}" placeholder="Nama Peserta atau Balai #${i}" value="${escapeHtml(val)}">
                `;
                container.appendChild(row);
            }
        }

        // POPULATE PARTICIPANT INPUTS ON MODAL OPEN
        function populateParticipantInputs() {
            let names = [];
            if (tournamentData && tournamentData.participants_json && Array.isArray(tournamentData.participants_json)) {
                names = tournamentData.participants_json.map(p => p.name);
            }
            const count = Math.max(names.length || 8, 4);
            document.getElementById('customCount').value = count;
            generateInputFields(count, names);
        }

        // FILL NEXT EMPTY PARTICIPANT CHIP CLICK
        function fillNextEmptyParticipant(name) {
            const inputs = document.querySelectorAll('.participant-name-input');
            for (let inp of inputs) {
                if (!inp.value.trim()) {
                    inp.value = name;
                    inp.focus();
                    return;
                }
            }
            // If none empty, set the last one or append
            if (inputs.length > 0) {
                const countInput = document.getElementById('customCount');
                let count = parseInt(countInput.value) || inputs.length;
                if (count < 32) {
                    count++;
                    countInput.value = count;
                    const existing = Array.from(inputs).map(inp => inp.value);
                    existing.push(name);
                    generateInputFields(count, existing);
                }
            }
        }

        // ESCAPE HTML HELPER
        function escapeHtml(string) {
            const str = String(string || '');
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
</body>
</html>
