<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Jadwal & Klasemen Pertandingan Gateball'); ?> | Satker PPS Riau</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --primary-navy: #002244;
            --secondary-navy: #0d3b66;
            --accent-yellow: #f4d03f;
            --accent-gold: #e5a93b;
            --pupr-yellow: #f7a81b;
            --pupr-blue: #0b3c74;
            --ri-red: #d31e28;
            --table-border: #163e72;
            --table-header-bg: #001f3f;
            --bg-canvas: #f0f4f8;
            --card-bg: #ffffff;
            --text-dark: #1e293b;
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
            padding: 16px;
            background-image: 
                radial-gradient(#cbd5e1 1px, transparent 1px),
                radial-gradient(#cbd5e1 1px, #f0f4f8 1px);
            background-size: 40px 40px;
            background-position: 0 0, 20px 20px;
        }

        .main-container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(0, 31, 63, 0.12), 0 1px 4px rgba(0,0,0,0.05);
            padding: 24px 32px 32px 32px;
            position: relative;
            overflow: hidden;
        }

        /* Top Action Bar (Admin / Toolbar) */
        .top-action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding-bottom: 16px;
            border-bottom: 1px dashed #e2e8f0;
            margin-bottom: 20px;
        }

        .live-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #059669;
            background: #ecfdf5;
            padding: 6px 14px;
            border-radius: 9999px;
            border: 1px solid #a7f3d0;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-ring 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .toolbar-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-update {
            background: linear-gradient(135deg, #0d3b66, #002244);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(13, 59, 102, 0.25);
        }
        .btn-update:hover {
            background: linear-gradient(135deg, #092a4a, #00152b);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13, 59, 102, 0.35);
        }

        .btn-outline-custom {
            background: #ffffff;
            color: #334155;
            border: 1.5px solid #cbd5e1;
        }
        .btn-outline-custom:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        /* Banner Header */
        .header-banner {
            display: grid;
            grid-template-columns: 280px 1fr 280px;
            align-items: center;
            gap: 20px;
            padding-bottom: 24px;
            border-bottom: 3px solid #002244;
            margin-bottom: 24px;
        }

        .pupr-logo-box {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .pupr-logo-img {
            height: 56px;
            max-height: 60px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.08));
        }

        .pupr-logo-text {
            line-height: 1.15;
        }

        .pupr-logo-text .title {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.88rem;
            font-weight: 900;
            color: #002244;
            letter-spacing: -0.2px;
            text-transform: uppercase;
        }

        .pupr-logo-text .subtitle {
            font-size: 0.68rem;
            font-weight: 700;
            color: #1e3a8a;
            margin-top: 3px;
            letter-spacing: -0.1px;
        }

        .header-center {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .banner-main-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.1rem;
            font-weight: 900;
            color: #002244;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
            margin-bottom: 12px;
        }

        /* Category Tabs */
        .category-tabs-container {
            display: inline-flex;
            background: #e2e8f0;
            padding: 5px;
            border-radius: 9999px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
            gap: 6px;
        }

        .category-tab-btn {
            font-family: 'Montserrat', sans-serif;
            border: none;
            background: transparent;
            padding: 8px 32px;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 1.05rem;
            color: #475569;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .category-tab-btn.active {
            background: linear-gradient(135deg, #002244, #0d3b66);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 34, 68, 0.35);
            transform: scale(1.02);
        }

        .category-tab-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.6);
            color: #0f172a;
        }

        /* RI 81 Logo Box */
        .ri81-logo-box {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
        }

        .ri81-badge-img {
            height: 58px;
            max-height: 62px;
            width: auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(211, 30, 40, 0.25);
            display: block;
        }

        .ri81-subtext {
            font-size: 0.64rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: -0.1px;
            line-height: 1.2;
        }

        /* Grid Layout (Side by Side) */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            margin-bottom: 28px;
        }

        .card-table-wrap {
            display: flex;
            flex-direction: column;
        }

        .table-title-pill {
            align-self: center;
            background: linear-gradient(135deg, #001f3f, #0d3b66);
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1.05rem;
            text-transform: uppercase;
            padding: 8px 36px;
            border-radius: 9999px;
            letter-spacing: 0.8px;
            margin-bottom: 14px;
            box-shadow: 0 4px 12px rgba(0, 31, 63, 0.25);
            border: 2px solid #ffffff;
        }

        /* Official Look Table */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border: 2px solid var(--table-border);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 31, 63, 0.06);
        }

        .table-custom thead th {
            background-color: var(--table-header-bg);
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 0.92rem;
            text-transform: uppercase;
            padding: 10px 8px;
            text-align: center;
            border: 1px solid var(--table-border);
            letter-spacing: 0.5px;
        }

        .table-custom tbody td {
            padding: 10px 8px;
            text-align: center;
            font-size: 0.95rem;
            font-weight: 600;
            border: 1px solid var(--table-border);
            color: #0f172a;
            vertical-align: middle;
        }

        .table-custom tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .table-custom tbody tr:hover {
            background-color: #f1f5f9;
        }

        .match-row-clickable {
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .match-row-clickable:hover {
            background-color: #e0f2fe !important;
            transform: scale(1.005);
            box-shadow: inset 0 0 0 1.5px #0284c7;
        }

        .match-live-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.68rem;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .tag-ongoing {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #f87171;
        }
        .tag-completed {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .tag-pending {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        .unor-badge {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 1.02rem;
            color: #002244;
            letter-spacing: 0.3px;
        }

        .vs-badge {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 0.88rem;
            color: #1e3a8a;
            background: #e2e8f0;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Standings Table Highlighting */
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            font-weight: 800;
            font-size: 0.88rem;
        }

        .rank-1 {
            background: linear-gradient(135deg, #ffd700, #d4af37);
            color: #5c4300;
            box-shadow: 0 2px 6px rgba(212, 175, 55, 0.4);
        }

        .rank-2 {
            background: linear-gradient(135deg, #e0e0e0, #bdbdbd);
            color: #374151;
            box-shadow: 0 2px 6px rgba(189, 189, 189, 0.4);
        }

        .rank-3 {
            background: linear-gradient(135deg, #cd7f32, #a05a2c);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(160, 90, 44, 0.4);
        }

        .point-highlight {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1.15rem;
            color: #002244;
        }

        .score-highlight {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 1.05rem;
        }
        .score-positive { color: #059669; }
        .score-negative { color: #dc2626; }
        .score-zero { color: #64748b; }

        .score-result-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 0.95rem;
        }
        .score-result-badge.completed {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1e40af;
        }
        .score-result-badge.ongoing {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        /* Bottom Section */
        .bottom-section {
            display: grid;
            grid-template-columns: 1fr 1.2fr 1fr;
            gap: 20px;
            align-items: center;
            margin-top: 10px;
            position: relative;
        }

        .info-card-box {
            border: 2px solid var(--table-border);
            border-radius: 12px;
            padding: 14px 18px;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 31, 63, 0.05);
            position: relative;
        }

        .info-card-title {
            background: #001f3f;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 0.78rem;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .info-list {
            list-style: none;
            font-size: 0.82rem;
            line-height: 1.5;
            color: #1e293b;
            font-weight: 600;
        }

        .info-list li {
            margin-bottom: 4px;
            position: relative;
            padding-left: 14px;
        }

        .info-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            font-weight: bold;
            color: #002244;
        }

        .highlight-red {
            color: #dc2626;
            font-weight: 800;
        }

        /* Center Gateball Pitch Graphic */
        .gateball-graphic-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            min-height: 140px;
        }

        .grass-patch {
            width: 85%;
            height: 80px;
            background: radial-gradient(ellipse at center, #78a741 0%, #5b872b 70%, transparent 75%);
            border-radius: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gateball-pitch-svg {
            width: 220px;
            height: 120px;
            position: absolute;
            top: -20px;
        }

        .indonesia-ribbon-wave {
            position: absolute;
            bottom: -15px;
            right: -25px;
            width: 190px;
            height: 70px;
            pointer-events: none;
        }

        /* Modal Styles */
        .modal-score-item {
            display: grid;
            grid-template-columns: 36px 1fr 40px 1fr 40px;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin-bottom: 10px;
        }

        .modal-score-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 800;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            outline: none;
            transition: all 0.2s;
        }
        .modal-score-input:focus {
            border-color: #0d3b66;
            box-shadow: 0 0 0 3px rgba(13, 59, 102, 0.15);
            background: #ffffff;
        }

        /* Tab panels visibility */
        .tab-pane {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }
        .tab-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive styles */
        @media (max-width: 1100px) {
            .header-banner {
                grid-template-columns: 1fr;
                gap: 16px;
                text-align: center;
            }
            .pupr-logo-box, .ri81-logo-box {
                justify-content: center;
                align-items: center;
                text-align: center;
            }
            .content-grid {
                grid-template-columns: 1fr;
            }
            .bottom-section {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 8px;
            }
            .main-container {
                padding: 16px;
            }
            .banner-main-title {
                font-size: 1.4rem;
            }
            .category-tab-btn {
                padding: 6px 20px;
                font-size: 0.9rem;
            }
            .table-custom thead th, .table-custom tbody td {
                padding: 6px 4px;
                font-size: 0.8rem;
            }
            .unor-badge {
                font-size: 0.85rem;
            }
        }

        /* Print styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .main-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .top-action-bar {
                display: none !important;
            }
            .content-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 16px !important;
            }
            .bottom-section {
                grid-template-columns: 1fr 1.2fr 1fr !important;
            }
            .tab-pane {
                display: block !important;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

<div class="main-container" id="printableArea">
    
    <!-- Top Action Toolbar -->
    <div class="top-action-bar">
        <div class="live-status-pill">
            <span class="pulse-dot"></span>
            <span>Live Standings Real-time</span>
            <small id="lastUpdatedTime" style="color: #64748b; font-weight: 500;">(Sync Aktif)</small>
        </div>

        <div class="toolbar-buttons">
            <button type="button" class="btn-action btn-update" id="btnOpenUpdateModal">
                <i class="fas fa-lock"></i>
                <span>Update Skor Cepat</span>
            </button>
            <button type="button" class="btn-action btn-outline-custom" id="btnRefreshData" title="Muat Ulang Data">
                <i class="fas fa-sync-alt" id="refreshIcon"></i>
                <span>Refresh</span>
            </button>
            <button type="button" class="btn-action btn-outline-custom" onclick="window.print()" title="Cetak Jadwal & Klasemen">
                <i class="fas fa-print"></i>
                <span>Cetak PDF</span>
            </button>
            <button type="button" class="btn-action btn-outline-custom" id="btnToggleFullscreen" title="Layar Penuh">
                <i class="fas fa-expand"></i>
                <span>Fullscreen</span>
            </button>
        </div>
    </div>

    <!-- Official Header Banner -->
    <header class="header-banner">
        <!-- Application Logo Left -->
        <div class="pupr-logo-box">
            <img src="<?= esc($logoUrl); ?>" alt="Logo <?= esc($officialName ?? 'Kementerian Pekerjaan Umum'); ?>" class="pupr-logo-img">
            <div class="pupr-logo-text">
                <div class="title">KEMENTERIAN<br>PEKERJAAN UMUM</div>
                <div class="subtitle">SIGAP MEMBANGUN NEGERI<br>UNTUK RAKYAT</div>
            </div>
        </div>

        <!-- Center Title & Tab Navigation -->
        <div class="header-center">
            <h1 class="banner-main-title">JADWAL PERTANDINGAN GATE BALL</h1>
            
            <!-- Category Tabs on Top -->
            <div class="category-tabs-container" role="tablist">
                <button type="button" class="category-tab-btn active" id="tabPutraBtn" data-target="panePutra" role="tab" aria-selected="true">
                    <i class="fas fa-mars"></i> PUTRA
                </button>
                <button type="button" class="category-tab-btn" id="tabPutriBtn" data-target="panePutri" role="tab" aria-selected="false">
                    <i class="fas fa-venus"></i> PUTRI
                </button>
            </div>
        </div>

        <!-- RI 81 Emblem Right -->
        <div class="ri81-logo-box">
            <img src="<?= base_url('assets/img/logo_81_badge.png'); ?>" alt="81 Indonesia Berdaulat Adil dan Makmur" class="ri81-badge-img">
            <div class="ri81-subtext">
                DIRGAHAYU REPUBLIK INDONESIA<br>17 AGUSTUS 1945 - 17 AGUSTUS 2026
            </div>
        </div>
    </header>

    <!-- PUTRA TAB PANE -->
    <div class="tab-pane active" id="panePutra">
        <div class="content-grid">
            
            <!-- Left: Jadwal Pertandingan Putra -->
            <div class="card-table-wrap">
                <div class="table-title-pill">JADWAL PERTANDINGAN</div>
                <table class="table-custom" id="tableJadwalPutra">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No.</th>
                            <th style="width: 25%;">PERTANDINGAN</th>
                            <th style="width: 25%;">UNOR 1</th>
                            <th style="width: 15%;">VS</th>
                            <th style="width: 25%;">UNOR 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($putraMatches as $match): ?>
                            <?php 
                                $s1 = $match['score1'];
                                $s2 = $match['score2'];
                                $mStatus = $match['status'];
                                $isCompleted = ($s1 !== null && $s2 !== null && $mStatus === 'completed');
                                $isOngoing = ($mStatus === 'ongoing');
                            ?>
                            <tr class="match-row-clickable" data-match-id="<?= (int) $match['id']; ?>" data-match-num="<?= (int) $match['match_number']; ?>" onclick="openMatchScoreboard(<?= (int) $match['id']; ?>)" title="Klik untuk membuka Timer & Papan Skor Pertandingan #<?= (int) $match['match_number']; ?>">
                                <td><strong><?= (int) $match['match_number']; ?>.</strong></td>
                                <td>
                                    <?php if ($isCompleted): ?>
                                        <span class="score-result-badge completed">
                                            <strong><?= (int) $s1; ?></strong> - <strong><?= (int) $s2; ?></strong>
                                        </span>
                                        <span class="match-live-tag tag-completed">Selesai</span>
                                    <?php elseif ($isOngoing): ?>
                                        <span class="score-result-badge ongoing">
                                            <strong><?= (int) $s1; ?></strong> - <strong><?= (int) $s2; ?></strong>
                                        </span>
                                        <span class="match-live-tag tag-ongoing"><span class="pulse-dot" style="background:#ef4444; width:6px; height:6px;"></span> Live</span>
                                    <?php else: ?>
                                        <span class="score-result-badge" style="color: #94a3b8;">
                                            -
                                        </span>
                                        <span class="match-live-tag tag-pending">Siap</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="unor-badge"><?= esc($match['team1']); ?></span></td>
                                <td><span class="vs-badge">VS</span></td>
                                <td><span class="unor-badge"><?= esc($match['team2']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Right: Klasemen Putra -->
            <div class="card-table-wrap">
                <div class="table-title-pill">KLASEMEN / HASIL PERTANDINGAN</div>
                <table class="table-custom" id="tableKlasemenPutra">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No.</th>
                            <th style="width: 26%;">UNOR</th>
                            <th style="width: 12%;">M</th>
                            <th style="width: 12%;">K</th>
                            <th style="width: 12%;">S</th>
                            <th style="width: 14%;">POINT</th>
                            <th style="width: 14%;">SCORE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($putraStandings as $row): ?>
                            <?php 
                                $rank = (int) $row['rank'];
                                $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : ''));
                                $scoreDiff = (int) $row['score'];
                                $scoreClass = $scoreDiff > 0 ? 'score-positive' : ($scoreDiff < 0 ? 'score-negative' : 'score-zero');
                                $scoreSign = $scoreDiff > 0 ? '+' . $scoreDiff : (string) $scoreDiff;
                            ?>
                            <tr>
                                <td>
                                    <?php if ($rankClass !== ''): ?>
                                        <span class="rank-badge <?= $rankClass; ?>"><?= $rank; ?></span>
                                    <?php else: ?>
                                        <strong><?= $rank; ?>.</strong>
                                    <?php endif; ?>
                                </td>
                                <td><span class="unor-badge"><?= esc($row['team']); ?></span></td>
                                <td><?= (int) $row['m']; ?></td>
                                <td><?= (int) $row['k']; ?></td>
                                <td><?= (int) $row['s']; ?></td>
                                <td><span class="point-highlight"><?= (int) $row['point']; ?></span></td>
                                <td><span class="score-highlight <?= $scoreClass; ?>"><?= esc($scoreSign); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- PUTRI TAB PANE -->
    <div class="tab-pane" id="panePutri">
        <div class="content-grid">
            
            <!-- Left: Jadwal Pertandingan Putri -->
            <div class="card-table-wrap">
                <div class="table-title-pill">JADWAL PERTANDINGAN</div>
                <table class="table-custom" id="tableJadwalPutri">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No.</th>
                            <th style="width: 25%;">PERTANDINGAN</th>
                            <th style="width: 25%;">UNOR 1</th>
                            <th style="width: 15%;">VS</th>
                            <th style="width: 25%;">UNOR 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($putriMatches as $match): ?>
                            <?php 
                                $s1 = $match['score1'];
                                $s2 = $match['score2'];
                                $mStatus = $match['status'];
                                $isCompleted = ($s1 !== null && $s2 !== null && $mStatus === 'completed');
                                $isOngoing = ($mStatus === 'ongoing');
                            ?>
                            <tr class="match-row-clickable" data-match-id="<?= (int) $match['id']; ?>" data-match-num="<?= (int) $match['match_number']; ?>" onclick="openMatchScoreboard(<?= (int) $match['id']; ?>)" title="Klik untuk membuka Timer & Papan Skor Pertandingan #<?= (int) $match['match_number']; ?>">
                                <td><strong><?= (int) $match['match_number']; ?>.</strong></td>
                                <td>
                                    <?php if ($isCompleted): ?>
                                        <span class="score-result-badge completed">
                                            <strong><?= (int) $s1; ?></strong> - <strong><?= (int) $s2; ?></strong>
                                        </span>
                                        <span class="match-live-tag tag-completed">Selesai</span>
                                    <?php elseif ($isOngoing): ?>
                                        <span class="score-result-badge ongoing">
                                            <strong><?= (int) $s1; ?></strong> - <strong><?= (int) $s2; ?></strong>
                                        </span>
                                        <span class="match-live-tag tag-ongoing"><span class="pulse-dot" style="background:#ef4444; width:6px; height:6px;"></span> Live</span>
                                    <?php else: ?>
                                        <span class="score-result-badge" style="color: #94a3b8;">
                                            -
                                        </span>
                                        <span class="match-live-tag tag-pending">Siap</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="unor-badge"><?= esc($match['team1']); ?></span></td>
                                <td><span class="vs-badge">VS</span></td>
                                <td><span class="unor-badge"><?= esc($match['team2']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Right: Klasemen Putri -->
            <div class="card-table-wrap">
                <div class="table-title-pill">KLASEMEN / HASIL PERTANDINGAN</div>
                <table class="table-custom" id="tableKlasemenPutri">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No.</th>
                            <th style="width: 26%;">UNOR</th>
                            <th style="width: 12%;">M</th>
                            <th style="width: 12%;">K</th>
                            <th style="width: 12%;">S</th>
                            <th style="width: 14%;">POINT</th>
                            <th style="width: 14%;">SCORE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($putriStandings as $row): ?>
                            <?php 
                                $rank = (int) $row['rank'];
                                $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : ''));
                                $scoreDiff = (int) $row['score'];
                                $scoreClass = $scoreDiff > 0 ? 'score-positive' : ($scoreDiff < 0 ? 'score-negative' : 'score-zero');
                                $scoreSign = $scoreDiff > 0 ? '+' . $scoreDiff : (string) $scoreDiff;
                            ?>
                            <tr>
                                <td>
                                    <?php if ($rankClass !== ''): ?>
                                        <span class="rank-badge <?= $rankClass; ?>"><?= $rank; ?></span>
                                    <?php else: ?>
                                        <strong><?= $rank; ?>.</strong>
                                    <?php endif; ?>
                                </td>
                                <td><span class="unor-badge"><?= esc($row['team']); ?></span></td>
                                <td><?= (int) $row['m']; ?></td>
                                <td><?= (int) $row['k']; ?></td>
                                <td><?= (int) $row['s']; ?></td>
                                <td><span class="point-highlight"><?= (int) $row['point']; ?></span></td>
                                <td><span class="score-highlight <?= $scoreClass; ?>"><?= esc($scoreSign); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Bottom Section: Keterangan & Gateball Pitch Art -->
    <footer class="bottom-section">
        <!-- Keterangan Pertandingan (Kiri) -->
        <div class="info-card-box">
            <div class="info-card-title">KETERANGAN PERTANDINGAN :</div>
            <ul class="info-list">
                <li>Menang mendapatkan <span class="highlight-red">3 point</span></li>
                <li>Seri mendapatkan <span class="highlight-red">1 point</span></li>
                <li>Kalah mendapatkan <span class="highlight-red">0 point</span></li>
                <li>SCORE adalah selisih skor (Memasukkan - Kemasukan)</li>
            </ul>
        </div>

        <!-- Center: Gateball Vector Turf Illustration -->
        <div class="gateball-graphic-center">
            <div class="grass-patch">
                <svg class="gateball-pitch-svg" viewBox="0 0 220 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Gate Frame -->
                    <path d="M 85 70 L 85 28 C 85 24, 88 22, 92 22 L 128 22 C 132 22, 135 24, 135 28 L 135 70" stroke="#E2E8F0" stroke-width="5" stroke-linecap="round"/>
                    <path d="M 90 22 L 130 22" stroke="#DC2626" stroke-width="5" stroke-linecap="round"/>
                    <!-- Ball 1 (Red Ball) -->
                    <circle cx="82" cy="78" r="12" fill="url(#redBallGrad)"/>
                    <circle cx="79" cy="74" r="3" fill="#ffffff" fill-opacity="0.6"/>
                    <!-- Ball 2 (White Ball) -->
                    <circle cx="110" cy="74" r="12" fill="url(#whiteBallGrad)"/>
                    <circle cx="107" cy="70" r="3" fill="#ffffff" fill-opacity="0.9"/>
                    <!-- Mallet Stick -->
                    <line x1="175" y1="15" x2="135" y2="78" stroke="#1E293B" stroke-width="4" stroke-linecap="round"/>
                    <rect x="120" y="70" width="30" height="14" rx="4" transform="rotate(-30 135 77)" fill="#0F172A"/>
                    <defs>
                        <radialGradient id="redBallGrad" cx="35%" cy="35%" r="65%">
                            <stop offset="0%" stop-color="#FF6B6B"/>
                            <stop offset="50%" stop-color="#DC2626"/>
                            <stop offset="100%" stop-color="#7F1D1D"/>
                        </radialGradient>
                        <radialGradient id="whiteBallGrad" cx="35%" cy="35%" r="65%">
                            <stop offset="0%" stop-color="#FFFFFF"/>
                            <stop offset="60%" stop-color="#E2E8F0"/>
                            <stop offset="100%" stop-color="#94A3B8"/>
                        </radialGradient>
                    </defs>
                </svg>
            </div>
            <!-- Indonesian Flag Ribbon -->
            <svg class="indonesia-ribbon-wave" viewBox="0 0 190 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Red part -->
                <path d="M 10 55 C 60 15, 110 65, 185 20 L 185 32 C 110 77, 60 27, 10 67 Z" fill="#DC2626"/>
                <!-- White part -->
                <path d="M 10 67 C 60 27, 110 77, 185 32 L 185 44 C 110 89, 60 39, 10 79 Z" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="0.5"/>
            </svg>
        </div>

        <!-- Keterangan Singkat Kolom (Kanan) -->
        <div class="info-card-box">
            <div class="info-card-title">KETERANGAN SINGKAT KOLOM :</div>
            <div class="info-list" style="font-size: 0.8rem; line-height: 1.45;">
                <div style="display: grid; grid-template-columns: 65px 1fr; gap: 4px; margin-bottom: 2px;">
                    <strong>M</strong> <span>: Menang</span>
                    <strong>K</strong> <span>: Kalah</span>
                    <strong>S</strong> <span>: Seri</span>
                    <strong>POINT</strong> <div>: Menang = <span class="highlight-red">3 point</span><br>&nbsp; Seri = <span class="highlight-red">1 point</span><br>&nbsp; Kalah = <span class="highlight-red">0 point</span></div>
                    <strong>SCORE</strong> <span>: Selisih skor (Memasukkan - Kemasukan)</span>
                </div>
            </div>
        </div>
    </footer>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const API_URL_DATA = '<?= site_url("gateball/api/data"); ?>';
    const API_URL_UPDATE = '<?= site_url("gateball/api/update-score"); ?>';
    const API_URL_BATCH = '<?= site_url("gateball/api/batch-update"); ?>';
    const API_URL_RESET = '<?= site_url("gateball/api/reset"); ?>';
    const API_URL_VERIFY = '<?= site_url("gateball/api/verify-auth"); ?>';
    const MATCH_PAGE_URL = '<?= site_url("gateball/match/"); ?>';

    let currentCategory = 'putra';
    let savedPassword = sessionStorage.getItem('gateball_pwd') || '';
    let matchesData = {
        putra: <?= json_encode($putraMatches); ?>,
        putri: <?= json_encode($putriMatches); ?>
    };
    let standingsData = {
        putra: <?= json_encode($putraStandings); ?>,
        putri: <?= json_encode($putriStandings); ?>
    };

    // Auto-closing Toast notification configuration (3.5s countdown timer)
    const ScoreToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        background: '#ffffff',
        color: '#0f172a',
        customClass: {
            popup: 'swal2-border-radius shadow-lg'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    // Match signature map to track score updates and prevent double notifications (Rule 1)
    let matchSignatures = {};
    function buildMatchSignatures(data) {
        const sigs = {};
        ['putra', 'putri'].forEach(cat => {
            if (data[cat] && Array.isArray(data[cat])) {
                data[cat].forEach(m => {
                    sigs[m.id] = `${m.score1 !== null ? m.score1 : '-'}_${m.score2 !== null ? m.score2 : '-'}_${m.status}`;
                });
            }
        });
        return sigs;
    }
    matchSignatures = buildMatchSignatures(matchesData);

    // Tab Switching Logic
    const tabPutraBtn = document.getElementById('tabPutraBtn');
    const tabPutriBtn = document.getElementById('tabPutriBtn');
    const panePutra = document.getElementById('panePutra');
    const panePutri = document.getElementById('panePutri');

    function switchCategory(cat) {
        currentCategory = cat;
        if (cat === 'putra') {
            tabPutraBtn.classList.add('active');
            tabPutriBtn.classList.remove('active');
            panePutra.classList.add('active');
            panePutri.classList.remove('active');
            window.location.hash = 'putra';
        } else {
            tabPutriBtn.classList.add('active');
            tabPutraBtn.classList.remove('active');
            panePutri.classList.add('active');
            panePutra.classList.remove('active');
            window.location.hash = 'putri';
        }
    }

    tabPutraBtn.addEventListener('click', () => switchCategory('putra'));
    tabPutriBtn.addEventListener('click', () => switchCategory('putri'));

    // Check hash on load
    if (window.location.hash === '#putri') {
        switchCategory('putri');
    }

    // Refresh Data Function (Polling sync with auto-closing alert on score updates)
    async function fetchData(silent = false) {
        const refreshIcon = document.getElementById('refreshIcon');
        if (!silent && refreshIcon) refreshIcon.classList.add('fa-spin');

        try {
            const resp = await fetch(API_URL_DATA);
            const result = await resp.json();
            if (result.status === 'success' && result.data) {
                const newMatches = {
                    putra: result.data.putra.matches,
                    putri: result.data.putri.matches
                };

                // Track and detect score or status changes
                const updatedItems = [];
                ['putra', 'putri'].forEach(cat => {
                    if (newMatches[cat]) {
                        newMatches[cat].forEach(m => {
                            const newSig = `${m.score1 !== null ? m.score1 : '-'}_${m.score2 !== null ? m.score2 : '-'}_${m.status}`;
                            const oldSig = matchSignatures[m.id];

                            if (oldSig && oldSig !== newSig) {
                                const catName = cat.toUpperCase();
                                const s1 = m.score1 !== null ? m.score1 : 0;
                                const s2 = m.score2 !== null ? m.score2 : 0;
                                let statusTag = '';
                                if (m.status === 'completed') statusTag = ' <span style="color:#15803d;font-size:0.75rem;font-weight:700;">(Selesai)</span>';
                                else if (m.status === 'ongoing') statusTag = ' <span style="color:#dc2626;font-size:0.75rem;font-weight:700;">(🔴 Live)</span>';

                                updatedItems.push(`<div><strong>Laga #${m.match_number} (${catName})</strong>: ${escapeHtml(m.team1)} <strong>${s1} - ${s2}</strong> ${escapeHtml(m.team2)}${statusTag}</div>`);
                            }
                            matchSignatures[m.id] = newSig;
                        });
                    }
                });

                matchesData.putra = result.data.putra.matches;
                standingsData.putra = result.data.putra.standings;
                matchesData.putri = result.data.putri.matches;
                standingsData.putri = result.data.putri.standings;

                renderAllTables();

                const now = new Date();
                const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                document.getElementById('lastUpdatedTime').textContent = `(Sinkron ${timeStr})`;

                // Show auto-closing notification alert (Rule 1: Pastikan Notifikasinya tidak double)
                if (updatedItems.length > 0) {
                    ScoreToast.fire({
                        icon: 'info',
                        title: '<span style="font-size:0.95rem;font-weight:800;color:#002244;"><i class="fas fa-bell text-warning"></i> Skor Terupdate!</span>',
                        html: `<div style="font-size:0.85rem;line-height:1.4;margin-top:4px;">${updatedItems.join('')}</div>`
                    });
                }
            }
        } catch (err) {
            console.error('Fetch data error:', err);
        } finally {
            if (!silent && refreshIcon) refreshIcon.classList.remove('fa-spin');
        }
    }

    document.getElementById('btnRefreshData').addEventListener('click', () => {
        fetchData(false);
    });

    // Auto poll every 3 seconds for instant real-time multi-device sync
    setInterval(() => {
        fetchData(true);
    }, 3000);

    // Render HTML tables from local state
    function renderAllTables() {
        renderMatchesTable('putra');
        renderStandingsTable('putra');
        renderMatchesTable('putri');
        renderStandingsTable('putri');
    }

    function renderMatchesTable(cat) {
        const tbody = document.querySelector(`#tableJadwal${cat.charAt(0).toUpperCase() + cat.slice(1)} tbody`);
        if (!tbody) return;

        const matches = matchesData[cat] || [];
        let html = '';
        matches.forEach(m => {
            const s1 = m.score1;
            const s2 = m.score2;
            const mStatus = m.status;
            const isCompleted = (s1 !== null && s2 !== null && mStatus === 'completed');
            const isOngoing = (mStatus === 'ongoing');
            
            html += `
                <tr class="match-row-clickable" data-match-id="${m.id}" data-match-num="${m.match_number}" onclick="openMatchScoreboard(${m.id})" title="Klik untuk membuka Timer & Papan Skor Pertandingan #${m.match_number}">
                    <td><strong>${m.match_number}.</strong></td>
                    <td>
                        ${isCompleted ? `
                            <span class="score-result-badge completed">
                                <strong>${s1}</strong> - <strong>${s2}</strong>
                            </span>
                            <span class="match-live-tag tag-completed">Selesai</span>
                        ` : (isOngoing ? `
                            <span class="score-result-badge ongoing">
                                <strong>${s1 !== null ? s1 : 0}</strong> - <strong>${s2 !== null ? s2 : 0}</strong>
                            </span>
                            <span class="match-live-tag tag-ongoing"><span class="pulse-dot" style="background:#ef4444; width:6px; height:6px;"></span> Live</span>
                        ` : `
                            <span class="score-result-badge" style="color: #94a3b8;">-</span>
                            <span class="match-live-tag tag-pending">Siap</span>
                        `)}
                    </td>
                    <td><span class="unor-badge">${escapeHtml(m.team1)}</span></td>
                    <td><span class="vs-badge">VS</span></td>
                    <td><span class="unor-badge">${escapeHtml(m.team2)}</span></td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function renderStandingsTable(cat) {
        const tbody = document.querySelector(`#tableKlasemen${cat.charAt(0).toUpperCase() + cat.slice(1)} tbody`);
        if (!tbody) return;

        const standings = standingsData[cat] || [];
        let html = '';
        standings.forEach(row => {
            const rank = parseInt(row.rank);
            const rankClass = rank === 1 ? 'rank-1' : (rank === 2 ? 'rank-2' : (rank === 3 ? 'rank-3' : ''));
            const scoreDiff = parseInt(row.score);
            const scoreClass = scoreDiff > 0 ? 'score-positive' : (scoreDiff < 0 ? 'score-negative' : 'score-zero');
            const scoreSign = scoreDiff > 0 ? '+' + scoreDiff : scoreDiff.toString();

            html += `
                <tr>
                    <td>
                        ${rankClass !== '' ? `<span class="rank-badge ${rankClass}">${rank}</span>` : `<strong>${rank}.</strong>`}
                    </td>
                    <td><span class="unor-badge">${escapeHtml(row.team)}</span></td>
                    <td>${parseInt(row.m)}</td>
                    <td>${parseInt(row.k)}</td>
                    <td>${parseInt(row.s)}</td>
                    <td><span class="point-highlight">${parseInt(row.point)}</span></td>
                    <td><span class="score-highlight ${scoreClass}">${scoreSign}</span></td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    // Open single match scoreboard page directly (tanpa perlu password untuk melihat jadwal/laga)
    function openMatchScoreboard(matchId) {
        window.location.href = MATCH_PAGE_URL + matchId;
    }

    // Modal Update Skor Cepat - Selalu minta verifikasi password setiap kali diklik
    document.getElementById('btnOpenUpdateModal').addEventListener('click', async () => {
        const { value: pwd } = await Swal.fire({
            title: 'Verifikasi Akses Update Skor',
            text: 'Masukkan password otorisasi turnamen untuk mengubah data skor:',
            input: 'password',
            inputPlaceholder: 'Masukkan Password',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-key"></i> Verifikasi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#002244',
            customClass: {
                popup: 'swal2-border-radius'
            }
        });

        if (!pwd) return;

        try {
            const formData = new FormData();
            formData.append('password', pwd);

            const verifyRes = await fetch(API_URL_VERIFY, {
                method: 'POST',
                body: formData
            });
            const resJson = await verifyRes.json();

            if (!verifyRes.ok || resJson.status !== 'success') {
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak',
                    text: 'Password otorisasi tidak sesuai.',
                    confirmButtonColor: '#002244'
                });
                return;
            }

            savedPassword = pwd;
            sessionStorage.setItem('gateball_pwd', pwd);
            openScoreEditorModal();
        } catch(e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi bermasalah saat verifikasi.' });
            return;
        }
    });

    function openScoreEditorModal() {
        const cat = currentCategory;
        const matches = matchesData[cat] || [];
        const catName = cat.toUpperCase();

        let rowsHtml = '';
        matches.forEach(m => {
            const s1 = m.score1 !== null ? m.score1 : '';
            const s2 = m.score2 !== null ? m.score2 : '';

            rowsHtml += `
                <div class="modal-score-item">
                    <div style="font-weight: 800; color: #002244;">#${m.match_number}</div>
                    <div style="text-align: right; font-weight: 700;">
                        <label for="sc1_${m.id}" style="display:block; font-size:0.75rem; color:#64748b; margin-bottom:2px;">${escapeHtml(m.team1)}</label>
                        <input type="number" id="sc1_${m.id}" class="modal-score-input" value="${s1}" min="0" max="99" placeholder="0">
                    </div>
                    <div style="text-align: center; font-weight: 800; color: #94a3b8; font-size: 0.9rem;">VS</div>
                    <div style="text-align: left; font-weight: 700;">
                        <label for="sc2_${m.id}" style="display:block; font-size:0.75rem; color:#64748b; margin-bottom:2px;">${escapeHtml(m.team2)}</label>
                        <input type="number" id="sc2_${m.id}" class="modal-score-input" value="${s2}" min="0" max="99" placeholder="0">
                    </div>
                    <div style="text-align: center;">
                        <a href="${MATCH_PAGE_URL + m.id}" target="_blank" title="Buka Timer & Papan Skor Live" style="color: #0284c7; font-size: 1.1rem;">
                            <i class="fas fa-stopwatch"></i>
                        </a>
                    </div>
                </div>
            `;
        });

        Swal.fire({
            title: `Update Skor Gateball - ${catName}`,
            html: `
                <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 14px;">
                    Masukkan skor untuk masing-masing pertandingan. Klik icon stopwatch <i class="fas fa-stopwatch text-info"></i> untuk membuka Timer & Papan Skor Live per laga.
                </div>
                <div style="max-height: 400px; overflow-y: auto; padding-right: 6px;" id="modalScoreList">
                    ${rowsHtml}
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                    <button type="button" id="btnResetCatScores" style="background: none; border: none; color: #dc2626; font-size: 0.82rem; font-weight: 700; cursor: pointer;">
                        <i class="fas fa-trash-alt"></i> Reset Skor ${catName}
                    </button>
                    <small style="color: #64748b; font-size: 0.78rem;">Sistem poin & klasemen otomatis dikalkulasi.</small>
                </div>
            `,
            width: '640px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Simpan Semua Skor',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#002244',
            didOpen: () => {
                const resetBtn = document.getElementById('btnResetCatScores');
                if (resetBtn) {
                    resetBtn.addEventListener('click', async () => {
                        const confirmReset = await Swal.fire({
                            title: `Reset Semua Skor ${catName}?`,
                            text: 'Semua skor yang telah diinput pada kategori ini akan dikosongkan kembali.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Reset Skor',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#dc2626'
                        });

                        if (confirmReset.isConfirmed) {
                            try {
                                const formData = new FormData();
                                formData.append('password', savedPassword);
                                formData.append('category', cat);

                                const res = await fetch(API_URL_RESET, {
                                    method: 'POST',
                                    body: formData
                                });
                                const resJson = await res.json();

                                if (resJson.status === 'success') {
                                    await fetchData(false);
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil Direset',
                                        text: resJson.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    if (res.status === 403) {
                                        savedPassword = '';
                                        sessionStorage.removeItem('gateball_pwd');
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: resJson.message || 'Gagal mereset data.',
                                        confirmButtonColor: '#002244'
                                    });
                                }
                            } catch (e) {
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi bermasalah.' });
                            }
                        }
                    });
                }
            },
            preConfirm: async () => {
                const scores = {};
                matches.forEach(m => {
                    const sc1Input = document.getElementById(`sc1_${m.id}`);
                    const sc2Input = document.getElementById(`sc2_${m.id}`);
                    if (sc1Input && sc2Input) {
                        scores[m.id] = {
                            score1: sc1Input.value.trim(),
                            score2: sc2Input.value.trim()
                        };
                    }
                });

                const formData = new FormData();
                formData.append('password', savedPassword);
                formData.append('category', cat);
                
                Object.keys(scores).forEach(matchId => {
                    formData.append(`scores[${matchId}][score1]`, scores[matchId].score1);
                    formData.append(`scores[${matchId}][score2]`, scores[matchId].score2);
                });

                try {
                    const res = await fetch(API_URL_BATCH, {
                        method: 'POST',
                        body: formData
                    });
                    const resJson = await res.json();
                    if (!res.ok || resJson.status !== 'success') {
                        if (res.status === 403) {
                            savedPassword = '';
                            sessionStorage.removeItem('gateball_pwd');
                        }
                        Swal.showValidationMessage(resJson.message || 'Gagal menyimpan data skor.');
                        return false;
                    }
                    return resJson;
                } catch (e) {
                    Swal.showValidationMessage('Terjadi kesalahan jaringan.');
                    return false;
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                fetchData(false);
                Swal.fire({
                    icon: 'success',
                    title: 'Skor Berhasil Disimpan!',
                    text: 'Klasemen dan hasil pertandingan telah diperbarui.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }

    // Toggle Fullscreen display
    const btnFullscreen = document.getElementById('btnToggleFullscreen');
    btnFullscreen.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.error(`Error enabling fullscreen: ${err.message}`);
            });
            btnFullscreen.innerHTML = '<i class="fas fa-compress"></i> <span>Exit Fullscreen</span>';
        } else {
            document.exitFullscreen();
            btnFullscreen.innerHTML = '<i class="fas fa-expand"></i> <span>Fullscreen</span>';
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            btnFullscreen.innerHTML = '<i class="fas fa-expand"></i> <span>Fullscreen</span>';
        }
    });
</script>

</body>
</html>
