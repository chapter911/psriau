<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Papan Skor & Timer Gateball'); ?> | Satker PPS Riau</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,600;0,700;0,800;0,900;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Orbitron:wght@700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <!-- DSEG 7-Segment Digital Font Library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dseg@0.46.0/css/dseg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --primary-navy: #002244;
            --secondary-navy: #0d3b66;
            --team-red: #d31e28;
            --team-red-dark: #991b1b;
            --team-white: #ffffff;
            --accent-gold: #f59e0b;
            --bg-canvas: #f0f4f8;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --led-bg: #090f1d;
            --timer-color: #38bdf8;
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
            background-size: 36px 36px;
            background-position: 0 0, 18px 18px;
        }

        /* Operator Visibility Rules */
        body.is-viewer .operator-only {
            display: none !important;
        }

        body:not(.is-viewer) .viewer-only {
            display: none !important;
        }

        .viewer-mode-notice {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 8px 18px;
            font-size: 0.85rem;
            color: #475569;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 4px auto 0 auto;
        }

        .btn-mode-operator {
            background: linear-gradient(135deg, #002244, #0d3b66) !important;
            color: #ffffff !important;
            border: 2px solid #002244 !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 12px rgba(0, 34, 68, 0.25) !important;
            padding: 8px 18px !important;
            font-size: 0.9rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        .btn-mode-operator:hover {
            background: #001529 !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 34, 68, 0.35) !important;
        }

        .btn-mode-operator-active {
            background: #ecfdf5 !important;
            color: #065f46 !important;
            border: 2px solid #10b981 !important;
            font-weight: 800 !important;
            padding: 8px 18px !important;
            font-size: 0.9rem !important;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.2) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .main-container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            border: 2px solid #e2e8f0;
            box-shadow: 0 10px 40px rgba(0, 31, 63, 0.12);
            padding: 24px 32px 32px 32px;
            position: relative;
            transition: all 0.3s ease;
        }

        /* Fullscreen Layout */
        :fullscreen body {
            padding: 0 !important;
            background: #ffffff !important;
        }
        :fullscreen .main-container {
            max-width: 100vw !important;
            min-height: 100vh !important;
            border-radius: 0 !important;
            border: none !important;
            box-shadow: none !important;
            padding: 20px 36px !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        :fullscreen .timer-display-7seg {
            font-size: 11.5rem !important;
            letter-spacing: 12px !important;
        }
        :fullscreen .score-7seg {
            font-size: 12.5rem !important;
            letter-spacing: 10px !important;
        }
        :fullscreen .score-bezel {
            min-width: 340px !important;
            padding: 18px 56px !important;
        }
        :fullscreen .seven-segment-bezel {
            padding: 18px 72px !important;
        }

        /* Top Action / Nav Bar */
        .top-nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding-bottom: 18px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 24px;
        }

        .btn-back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            color: #002244;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border: 1.5px solid #cbd5e1;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .btn-back-link:hover {
            background: #f8fafc;
            border-color: #002244;
            transform: translateX(-3px);
            color: #002244;
        }

        .match-badge-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge-category {
            background: linear-gradient(135deg, #002244, #0d3b66);
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 0.95rem;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 9999px;
            letter-spacing: 0.8px;
        }

        .badge-match-num {
            background: #f59e0b;
            color: #451a03;
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 0.95rem;
            padding: 6px 16px;
            border-radius: 9999px;
        }

        .live-pulse-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 9999px;
        }
        .status-ongoing {
            background: #fee2e2;
            color: #dc2626;
            border: 1.5px solid #f87171;
        }
        .status-completed {
            background: #dcfce7;
            color: #15803d;
            border: 1.5px solid #86efac;
        }
        .status-pending {
            background: #f1f5f9;
            color: #64748b;
            border: 1.5px solid #cbd5e1;
        }

        .pulse-dot-red {
            width: 8px;
            height: 8px;
            background-color: #dc2626;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
            animation: pulse-red 1.5s infinite;
        }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-hdr-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            color: #334155;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-hdr-action:hover {
            background: #f8fafc;
            border-color: #002244;
            color: #002244;
        }

        /* TIMER BOX - Sport Digital Stadium Clock */
        .timer-card {
            background: #ffffff;
            border: 2px solid #cbd5e1;
            border-radius: 18px;
            padding: 20px;
            text-align: center;
            margin-bottom: 28px;
            box-shadow: 0 4px 20px rgba(0, 31, 63, 0.06);
            position: relative;
        }

        .timer-label {
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 2px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        /* Seven Segment Timer Display */
        .seven-segment-bezel {
            background: var(--led-bg);
            border: 4px solid #1e293b;
            border-radius: 18px;
            display: inline-block;
            padding: 16px 60px;
            margin-bottom: 14px;
            box-shadow: inset 0 4px 16px rgba(0,0,0,0.85), 0 8px 24px rgba(0,0,0,0.18);
        }

        .timer-display-7seg {
            font-family: 'DSEG7-Classic', 'Orbitron', monospace;
            font-size: 8.5rem;
            font-weight: 700;
            color: #00f0ff;
            text-shadow: 0 0 30px rgba(0, 240, 255, 0.8), 0 0 60px rgba(0, 240, 255, 0.4);
            letter-spacing: 10px;
            line-height: 1;
        }
        .timer-display-7seg.time-warning {
            color: #ff3344 !important;
            text-shadow: 0 0 30px rgba(255, 51, 68, 0.95), 0 0 60px rgba(255, 51, 68, 0.6) !important;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            50% { opacity: 0.4; }
        }

        .timer-progress-wrap {
            width: 100%;
            max-width: 500px;
            height: 8px;
            background: #e2e8f0;
            border-radius: 9999px;
            margin: 0 auto 16px auto;
            overflow: hidden;
        }

        .timer-progress-bar {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #0284c7, #002244);
            border-radius: 9999px;
            transition: width 0.3s linear;
        }

        .timer-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-ctrl {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 0.95rem;
            padding: 10px 22px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-start {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.4);
        }

        .btn-pause {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .btn-pause:hover {
            transform: translateY(-2px);
        }

        .btn-reset-timer {
            background: #f1f5f9;
            color: #334155;
            border: 1.5px solid #cbd5e1;
        }
        .btn-reset-timer:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-time-adjust {
            background: #f8fafc;
            color: #334155;
            padding: 8px 14px;
            font-size: 0.85rem;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-time-adjust:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* SCOREBOARD ARENA */
        .scoreboard-grid {
            display: grid;
            grid-template-columns: 1fr 90px 1fr;
            gap: 24px;
            align-items: stretch;
            margin-bottom: 28px;
        }

        .team-box {
            border-radius: 18px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 31, 63, 0.08);
            border: 2.5px solid #cbd5e1;
        }

        /* Red Team Box */
        .team-red-box {
            border-color: #ef4444;
            background: linear-gradient(180deg, #ffffff 0%, #fff5f5 100%);
        }

        /* White Team Box */
        .team-white-box {
            border-color: #0284c7;
            background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%);
        }

        .team-jersey-badge {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 0.82rem;
            text-transform: uppercase;
            padding: 5px 18px;
            border-radius: 9999px;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }
        .jersey-red {
            background: #dc2626;
            color: #ffffff;
        }
        .jersey-white {
            background: #0284c7;
            color: #ffffff;
        }

        .team-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.2rem;
            font-weight: 900;
            color: #002244;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        /* Seven Segment Score Number Display */
        .score-bezel {
            background: var(--led-bg);
            border: 4px solid #1e293b;
            border-radius: 18px;
            padding: 16px 44px;
            margin-bottom: 16px;
            box-shadow: inset 0 4px 16px rgba(0,0,0,0.85), 0 8px 24px rgba(0,0,0,0.15);
            min-width: 270px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .score-7seg {
            font-family: 'DSEG7-Classic', 'Orbitron', monospace;
            font-size: 9.2rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 8px;
        }
        .score-7seg-red {
            color: #ff3344;
            text-shadow: 0 0 30px rgba(255, 51, 68, 0.85), 0 0 55px rgba(255, 51, 68, 0.45);
        }
        .score-7seg-white {
            color: #00f0ff;
            text-shadow: 0 0 30px rgba(0, 240, 255, 0.85), 0 0 55px rgba(0, 240, 255, 0.45);
        }

        /* BALL CLICK SECTION */
        .ball-click-container {
            width: 100%;
            background: #f8fafc;
            border-radius: 14px;
            padding: 16px;
            border: 1.5px solid #e2e8f0;
        }

        .ball-click-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .ball-click-title {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ball-click-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .ball-click-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.03);
            position: relative;
        }

        .ball-click-card:hover {
            border-color: #002244;
            transform: translateY(-3px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }
        .ball-click-card:active {
            transform: scale(0.92);
        }

        .ball-click-card.has-points-red {
            border-color: #ef4444;
            background: #fff5f5;
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.2);
        }

        .ball-click-card.has-points-white {
            border-color: #0284c7;
            background: #f0f9ff;
            box-shadow: 0 0 10px rgba(2, 132, 199, 0.2);
        }

        .ball-click-card.is-agari {
            border-color: #f59e0b !important;
            background: #fffbeb !important;
            box-shadow: 0 0 14px rgba(245, 158, 11, 0.35) !important;
        }

        .ball-sphere {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1.5rem;
            margin-bottom: 6px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
        }

        .ball-sphere-red {
            background: radial-gradient(circle at 35% 35%, #ff7878 0%, #dc2626 55%, #991b1b 100%);
            color: #ffffff;
        }

        .ball-sphere-white {
            background: radial-gradient(circle at 35% 35%, #ffffff 0%, #e2e8f0 55%, #64748b 100%);
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }

        .ball-score-tag {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1.05rem;
            color: #0f172a;
            background: #e2e8f0;
            padding: 3px 12px;
            border-radius: 9999px;
            margin-bottom: 5px;
        }

        .ball-click-card.has-points-red .ball-score-tag {
            background: #fee2e2;
            color: #b91c1c;
        }
        .ball-click-card.has-points-white .ball-score-tag {
            background: #e0f2fe;
            color: #0369a1;
        }
        .ball-click-card.is-agari .ball-score-tag {
            background: #fef3c7;
            color: #92400e;
        }

        /* Target Indicator Badge (Menuju Gate 1, 2, 3, Agari) */
        .ball-target-badge {
            font-size: 0.68rem;
            font-weight: 800;
            padding: 3px 6px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: -0.2px;
            margin-bottom: 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            width: 95%;
            justify-content: center;
            text-align: center;
            line-height: 1.1;
        }

        .target-g1 {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .target-g2 {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #7dd3fc;
        }
        .target-g3 {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde047;
        }
        .target-ag {
            background: #fce7f3;
            color: #be185d;
            border: 1px solid #f9a8d4;
            animation: pulse-red 2s infinite;
        }
        .target-done {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            font-weight: 900;
        }

        .ball-sub-actions {
            display: flex;
            gap: 4px;
            width: 100%;
            justify-content: center;
        }

        .btn-ball-reset {
            background: #ffffff;
            color: #64748b;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-ball-reset:hover {
            background: #fee2e2;
            border-color: #ef4444;
            color: #dc2626;
        }

        /* Center VS divider */
        .vs-center-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .vs-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #002244, #0d3b66);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1.25rem;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 31, 63, 0.2);
        }

        /* Bottom Action Footer */
        .bottom-action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }

        .btn-finish-match {
            background: linear-gradient(135deg, #10b981, #047857);
            color: #ffffff;
            font-weight: 800;
            font-size: 1rem;
            padding: 12px 28px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }
        .btn-finish-match:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
        }

        .match-nav-links {
            display: flex;
            gap: 10px;
        }

        @media (max-width: 992px) {
            .scoreboard-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .vs-center-col {
                display: none;
            }
            .timer-display-7seg {
                font-size: 4rem;
            }
            .score-7seg {
                font-size: 4.5rem;
            }
            .ball-click-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 6px;
            }
            .ball-sphere {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body class="is-viewer">

<div class="main-container" id="matchMainContainer">

    <!-- Top Header Navigation & Status -->
    <header class="top-nav-bar">
        <a href="<?= site_url('gateball#' . esc($match['category'])); ?>" class="btn-back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Jadwal & Klasemen</span>
        </a>

        <div class="match-badge-header">
            <span class="badge-category"><?= strtoupper(esc($match['category'])); ?></span>
            <span class="badge-match-num">Laga #<?= (int)$match['match_number']; ?></span>
            <div id="liveStatusBadge" class="live-pulse-badge status-<?= esc($match['status']); ?>">
                <?php if ($match['status'] === 'ongoing'): ?>
                    <span class="pulse-dot-red"></span> <span>SEDANG BERTANDING</span>
                <?php elseif ($match['status'] === 'completed'): ?>
                    <i class="fas fa-check-circle"></i> <span>SELESAI</span>
                <?php else: ?>
                    <i class="fas fa-clock"></i> <span>BELUM MULAI</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="header-controls">
            <!-- Mode Operator Login / Toggle Button -->
            <button type="button" class="btn-hdr-action btn-mode-operator" id="btnOperatorToggle" title="Masuk Mode Operator Turnamen">
                <i class="fas fa-key" style="color: #fbbf24;" id="operatorLockIcon"></i>
                <span id="operatorBtnText">Mode Operator</span>
            </button>
            <button type="button" class="btn-hdr-action" id="btnToggleFullscreen" title="Layar Penuh">
                <i class="fas fa-expand" id="fullscreenIcon"></i>
                <span id="fullscreenText">Fullscreen</span>
            </button>
            <button type="button" class="btn-hdr-action" id="btnToggleAudio" title="Suara Peluit / Buzzer">
                <i class="fas fa-volume-up" id="audioIcon"></i>
            </button>
            <button type="button" class="btn-hdr-action" id="btnSyncNow" title="Sinkronkan Sekarang">
                <i class="fas fa-sync-alt" id="syncIcon"></i>
                <small id="syncTimeText" style="margin-left: 2px;">Live</small>
            </button>
        </div>
    </header>

    <!-- Digital Large Timer (Seven Segment LED Display) -->
    <section class="timer-card">
        <div class="timer-label">WAKTU PERTANDINGAN (30 MENIT COUNTDOWN)</div>
        
        <div class="seven-segment-bezel">
            <div class="timer-display-7seg" id="timerDisplay">30:00</div>
        </div>
        
        <div class="timer-progress-wrap">
            <div class="timer-progress-bar" id="timerProgressBar" style="width: 100%;"></div>
        </div>

        <!-- Operator Timer Controls (Hidden when not authenticated) -->
        <div class="timer-controls operator-only" id="timerControlsWrap">
            <button type="button" class="btn-ctrl btn-start" id="btnStartTimer">
                <i class="fas fa-play"></i> <span>Mulai Timer</span>
            </button>
            <button type="button" class="btn-ctrl btn-pause" id="btnPauseTimer" style="display: none;">
                <i class="fas fa-pause"></i> <span>Jeda (Pause)</span>
            </button>
            <button type="button" class="btn-ctrl btn-reset-timer" id="btnResetTimer">
                <i class="fas fa-redo"></i> <span>Reset 30:00</span>
            </button>
            <button type="button" class="btn-time-adjust" id="btnAddOneMin">+1 Menit</button>
            <button type="button" class="btn-time-adjust" id="btnSubOneMin">-1 Menit</button>
            <button type="button" class="btn-ctrl btn-start" id="btnTestBuzzer" style="background: #fee2e2; border: 1.5px solid #f87171; color: #dc2626; padding: 8px 14px; font-size: 0.85rem;">
                <i class="fas fa-bullhorn"></i> Bunyikan Peluit
            </button>
        </div>
    </section>

    <!-- Live Scoreboard Arena (Seven Segment Scores) -->
    <section class="scoreboard-grid">

        <!-- Red Team / Tim 1 (Bola Ganjil 1, 3, 5, 7, 9) -->
        <div class="team-box team-red-box">
            <div class="team-jersey-badge jersey-red">TIM MERAH (BOLA GANJIL)</div>
            <h2 class="team-name"><?= esc($match['team1']); ?></h2>
            
            <div class="score-bezel">
                <div class="score-7seg score-7seg-red" id="score1Display"><?= (int)($match['score1'] ?? 0); ?></div>
            </div>

            <!-- Simplified Click-to-Score Ball Grid for Red Team (1, 3, 5, 7, 9) -->
            <div class="ball-click-container">
                <div class="ball-click-header">
                    <span class="ball-click-title"><i class="fas fa-bullseye text-primary"></i> Target & Poin Bola:</span>
                    <button type="button" class="btn-ball-reset operator-only" onclick="resetTeamBalls(1)" title="Reset Semua Bola Merah">
                        <i class="fas fa-redo"></i> Reset Tim
                    </button>
                </div>
                <div class="ball-click-grid">
                    <?php foreach ([1, 3, 5, 7, 9] as $bNum): ?>
                        <div class="ball-click-card" id="card_ball_<?= $bNum; ?>" onclick="clickBall(1, <?= $bNum; ?>)" title="Bola <?= $bNum; ?>">
                            <div class="ball-sphere ball-sphere-red"><?= $bNum; ?></div>
                            <div class="ball-score-tag" id="score_tag_<?= $bNum; ?>">0 Pts</div>
                            <div class="ball-target-badge target-g1" id="target_badge_<?= $bNum; ?>">
                                <i class="fas fa-bullseye"></i> Ke Gate 1
                            </div>
                            <div class="ball-sub-actions operator-only" onclick="event.stopPropagation()">
                                <button type="button" class="btn-ball-reset" onclick="resetSingleBall(1, <?= $bNum; ?>)" title="Reset bola ini ke 0">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Center VS Column -->
        <div class="vs-center-col">
            <div class="vs-circle">VS</div>
        </div>

        <!-- White Team / Tim 2 (Bola Genap 2, 4, 6, 8, 10) -->
        <div class="team-box team-white-box">
            <div class="team-jersey-badge jersey-white">TIM PUTIH (BOLA GENAP)</div>
            <h2 class="team-name"><?= esc($match['team2']); ?></h2>
            
            <div class="score-bezel">
                <div class="score-7seg score-7seg-white" id="score2Display"><?= (int)($match['score2'] ?? 0); ?></div>
            </div>

            <!-- Simplified Click-to-Score Ball Grid for White Team (2, 4, 6, 8, 10) -->
            <div class="ball-click-container">
                <div class="ball-click-header">
                    <span class="ball-click-title"><i class="fas fa-bullseye text-primary"></i> Target & Poin Bola:</span>
                    <button type="button" class="btn-ball-reset operator-only" onclick="resetTeamBalls(2)" title="Reset Semua Bola Putih">
                        <i class="fas fa-redo"></i> Reset Tim
                    </button>
                </div>
                <div class="ball-click-grid">
                    <?php foreach ([2, 4, 6, 8, 10] as $bNum): ?>
                        <div class="ball-click-card" id="card_ball_<?= $bNum; ?>" onclick="clickBall(2, <?= $bNum; ?>)" title="Bola <?= $bNum; ?>">
                            <div class="ball-sphere ball-sphere-white"><?= $bNum; ?></div>
                            <div class="ball-score-tag" id="score_tag_<?= $bNum; ?>">0 Pts</div>
                            <div class="ball-target-badge target-g1" id="target_badge_<?= $bNum; ?>">
                                <i class="fas fa-bullseye"></i> Ke Gate 1
                            </div>
                            <div class="ball-sub-actions operator-only" onclick="event.stopPropagation()">
                                <button type="button" class="btn-ball-reset" onclick="resetSingleBall(2, <?= $bNum; ?>)" title="Reset bola ini ke 0">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </section>

    <!-- Bottom Action Footer -->
    <footer class="bottom-action-bar">
        <!-- Operator Finish / Reset Match Controls (Hidden when not operator) -->
        <div class="operator-only" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <button type="button" class="btn-finish-match" id="btnFinishMatch">
                <i class="fas fa-flag-checkered"></i> <span>Selesaikan & Kunci Hasil Pertandingan</span>
            </button>
            <button type="button" class="btn-ctrl btn-reset-timer" id="btnResetMatch" style="color: #dc2626; border-color: #fca5a5; background: #fff5f5;">
                <i class="fas fa-trash-alt"></i> <span>Kosongkan Skor Pertandingan</span>
            </button>
        </div>

        <div class="match-nav-links">
            <?php if ($prevMatch): ?>
                <a href="<?= site_url('gateball/match/' . (int)$prevMatch['id']); ?>" class="btn-ctrl btn-reset-timer">
                    <i class="fas fa-chevron-left"></i> Laga #<?= (int)$prevMatch['match_number']; ?>
                </a>
            <?php endif; ?>
            <?php if ($nextMatch): ?>
                <a href="<?= site_url('gateball/match/' . (int)$nextMatch['id']); ?>" class="btn-ctrl btn-reset-timer">
                    Laga #<?= (int)$nextMatch['match_number']; ?> <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </footer>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const MATCH_ID = <?= (int)$match['id']; ?>;
    const API_MATCH_DATA = '<?= site_url("gateball/api/match/" . (int)$match['id']); ?>';
    const API_UPDATE_MATCH = '<?= site_url("gateball/api/match/" . (int)$match['id'] . "/update"); ?>';
    const API_URL_VERIFY = '<?= site_url("gateball/api/verify-auth"); ?>';
    const TOTAL_MATCH_SECONDS = 1800; // 30 mins

    let matchState = <?= json_encode($match); ?>;
    let timerSeconds = parseInt(matchState.timer_seconds) || 1800;
    let timerStatus = matchState.timer_status || 'stopped';
    let matchStatus = matchState.status || 'pending';
    let score1 = matchState.score1 !== null ? parseInt(matchState.score1) : 0;
    let score2 = matchState.score2 !== null ? parseInt(matchState.score2) : 0;
    
    // Ball points dictionary { 1: 0, 2: 0, ..., 10: 0 }
    let ballPoints = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0 };

    try {
        if (matchState.score_details_json) {
            const parsed = JSON.parse(matchState.score_details_json);
            if (typeof parsed === 'object') {
                for (let b = 1; b <= 10; b++) {
                    if (typeof parsed[b] === 'number') {
                        ballPoints[b] = parsed[b];
                    } else if (typeof parsed[b] === 'object') {
                        const d = parsed[b];
                        ballPoints[b] = (d.g1 ? 1 : 0) + (d.g2 ? 1 : 0) + (d.g3 ? 1 : 0) + (d.ag ? 2 : 0);
                    }
                }
            }
        }
    } catch(e) {
        console.error('Parse ball details error:', e);
    }

    let timerInterval = null;
    let isAudioEnabled = true;
    let lastUserActionTimestamp = 0;
    let isSavePending = false;
    let savedPassword = '';

    // Auto-closing Toast notification configuration (3.5s countdown timer)
    const MatchScoreToast = Swal.mixin({
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

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    // Match state signature for anti-double notification (Rule 1)
    let lastMatchStateSig = `${score1}_${score2}_${matchStatus}_${JSON.stringify(ballPoints)}`;

    // Render operator mode vs viewer mode (Hide/Show buttons based on password authentication)
    function renderOperatorState() {
        const isOp = Boolean(savedPassword);
        document.body.classList.toggle('is-viewer', !isOp);

        const btnOp = document.getElementById('btnOperatorToggle');
        const icon = document.getElementById('operatorLockIcon');
        const txt = document.getElementById('operatorBtnText');

        if (isOp) {
            btnOp.className = 'btn-hdr-action btn-mode-operator-active';
            icon.className = 'fas fa-unlock text-success';
            icon.style.color = '';
            txt.innerHTML = '<span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;margin-right:4px;"></span> Operator Aktif';
            btnOp.title = 'Klik untuk keluar dari Mode Operator';
        } else {
            btnOp.className = 'btn-hdr-action btn-mode-operator';
            icon.className = 'fas fa-key';
            icon.style.color = '#fbbf24';
            txt.textContent = 'Mode Operator';
            btnOp.title = 'Masuk Mode Operator Turnamen';
        }
    }

    // Always show password modal alert when requesting Operator Mode
    async function requestOperatorLogin() {
        const { value: pwd } = await Swal.fire({
            title: 'Verifikasi Mode Operator',
            text: 'Masukkan password otorisasi turnamen untuk mengontrol skor & timer:',
            input: 'password',
            inputPlaceholder: 'Masukkan Password',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-key"></i> Buka Kontrol',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#002244',
            customClass: {
                popup: 'swal2-border-radius'
            }
        });

        if (!pwd) return false;

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
                return false;
            }

            savedPassword = pwd;
            sessionStorage.setItem('gateball_pwd', pwd);
            renderOperatorState();
            return true;
        } catch(err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi bermasalah saat verifikasi.' });
            return false;
        }
    }

    // Operator Mode Toggle Button Click (Always prompts for password when in viewer mode)
    document.getElementById('btnOperatorToggle').addEventListener('click', async () => {
        if (savedPassword) {
            const confirmLogout = await Swal.fire({
                title: 'Keluar Mode Operator?',
                text: 'Tombol kontrol skor dan timer akan disembunyikan kembali.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Keluar Mode Operator',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#002244'
            });
            if (confirmLogout.isConfirmed) {
                savedPassword = '';
                sessionStorage.removeItem('gateball_pwd');
                renderOperatorState();
                Swal.fire({
                    icon: 'info',
                    title: 'Mode Penonton Aktif',
                    text: 'Tombol operator telah disembunyikan.',
                    timer: 1200,
                    showConfirmButton: false
                });
            }
        } else {
            const authSuccess = await requestOperatorLogin();
            if (authSuccess) {
                Swal.fire({
                    icon: 'success',
                    title: 'Mode Operator Aktif',
                    text: 'Seluruh tombol kontrol skor & timer sekarang dapat digunakan.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    });

    // Verify operator password before allowing modifications
    async function ensureOperatorAuth() {
        if (savedPassword) {
            return true;
        }
        return await requestOperatorLogin();
    }

    // Fullscreen Toggle
    const btnFullscreen = document.getElementById('btnToggleFullscreen');
    const fullscreenIcon = document.getElementById('fullscreenIcon');
    const fullscreenText = document.getElementById('fullscreenText');

    btnFullscreen.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.error(`Error enabling fullscreen: ${err.message}`);
            });
            fullscreenIcon.className = 'fas fa-compress';
            fullscreenText.textContent = 'Keluar Fullscreen';
        } else {
            document.exitFullscreen();
            fullscreenIcon.className = 'fas fa-expand';
            fullscreenText.textContent = 'Fullscreen';
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            fullscreenIcon.className = 'fas fa-expand';
            fullscreenText.textContent = 'Fullscreen';
        } else {
            fullscreenIcon.className = 'fas fa-compress';
            fullscreenText.textContent = 'Keluar Fullscreen';
        }
    });

    // Web Audio Synthesized Buzzer Whistle
    let audioCtx = null;
    function playBuzzer(freq = 600, duration = 0.5, type = 'sine') {
        if (!isAudioEnabled) return;
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();

            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, audioCtx.currentTime);

            gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);

            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + duration);
        } catch(e) {
            console.error('Audio error:', e);
        }
    }

    document.getElementById('btnTestBuzzer').addEventListener('click', () => {
        playBuzzer(880, 0.35, 'sawtooth');
        setTimeout(() => playBuzzer(880, 0.5, 'sawtooth'), 180);
    });

    document.getElementById('btnToggleAudio').addEventListener('click', () => {
        isAudioEnabled = !isAudioEnabled;
        const icon = document.getElementById('audioIcon');
        icon.className = isAudioEnabled ? 'fas fa-volume-up' : 'fas fa-volume-mute';
    });

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }

    function renderUI() {
        const display = document.getElementById('timerDisplay');
        display.textContent = formatTime(timerSeconds);
        
        if (timerSeconds <= 60 && timerSeconds > 0) {
            display.classList.add('time-warning');
        } else {
            display.classList.remove('time-warning');
        }

        const pct = Math.max(0, Math.min(100, (timerSeconds / TOTAL_MATCH_SECONDS) * 100));
        document.getElementById('timerProgressBar').style.width = `${pct}%`;

        document.getElementById('score1Display').textContent = score1.toString().padStart(2, '0');
        document.getElementById('score2Display').textContent = score2.toString().padStart(2, '0');

        const btnStart = document.getElementById('btnStartTimer');
        const btnPause = document.getElementById('btnPauseTimer');
        if (timerStatus === 'running') {
            btnStart.style.display = 'none';
            btnPause.style.display = 'inline-flex';
        } else {
            btnStart.style.display = 'inline-flex';
            btnPause.style.display = 'none';
        }

        renderBallCards();
        renderStatusBadge();
    }

    function renderStatusBadge() {
        const badge = document.getElementById('liveStatusBadge');
        badge.className = `live-pulse-badge status-${matchStatus}`;
        if (matchStatus === 'ongoing') {
            badge.innerHTML = '<span class="pulse-dot-red"></span> <span>SEDANG BERTANDING</span>';
        } else if (matchStatus === 'completed') {
            badge.innerHTML = '<i class="fas fa-check-circle"></i> <span>SELESAI</span>';
        } else {
            badge.innerHTML = '<i class="fas fa-clock"></i> <span>BELUM MULAI</span>';
        }
    }

    function renderBallCards() {
        for (let b = 1; b <= 10; b++) {
            const pts = ballPoints[b] || 0;
            const tag = document.getElementById(`score_tag_${b}`);
            const targetEl = document.getElementById(`target_badge_${b}`);
            const card = document.getElementById(`card_ball_${b}`);
            
            let label = `${pts} Pts`;
            let targetHtml = '';
            let targetClass = '';

            // Gateball Target Logic:
            // 0 Pts -> Menuju Gate 1
            // 1 Pt  -> Menuju Gate 2
            // 2 Pts -> Menuju Gate 3
            // 3 Pts -> Menuju Agari (Goal Pole)
            // 5 Pts -> Selesai / Finish (Agari)
            if (pts === 0) {
                label = '0 Pts';
                targetHtml = '<i class="fas fa-bullseye"></i> Ke Gate 1';
                targetClass = 'target-g1';
            } else if (pts === 1) {
                label = '1 Pt';
                targetHtml = '<i class="fas fa-bullseye"></i> Ke Gate 2';
                targetClass = 'target-g2';
            } else if (pts === 2) {
                label = '2 Pts';
                targetHtml = '<i class="fas fa-bullseye"></i> Ke Gate 3';
                targetClass = 'target-g3';
            } else if (pts === 3) {
                label = '3 Pts';
                targetHtml = '<i class="fas fa-flag-checkered"></i> Ke Agari';
                targetClass = 'target-ag';
            } else if (pts === 5) {
                label = '5 Pts';
                targetHtml = '<i class="fas fa-trophy"></i> Selesai (AG)';
                targetClass = 'target-done';
            }

            if (tag) tag.textContent = label;
            if (targetEl) {
                targetEl.innerHTML = targetHtml;
                targetEl.className = `ball-target-badge ${targetClass}`;
            }

            if (card) {
                card.classList.toggle('is-agari', pts === 5);
                if (b % 2 === 1) {
                    card.classList.toggle('has-points-red', pts > 0 && pts < 5);
                } else {
                    card.classList.toggle('has-points-white', pts > 0 && pts < 5);
                }
            }
        }
    }

    // Recalculate totals from balls
    function calculateTotalFromBalls() {
        let t1 = 0;
        let t2 = 0;
        for (let b = 1; b <= 10; b++) {
            const pts = ballPoints[b] || 0;
            if (b % 2 === 1) {
                t1 += pts;
            } else {
                t2 += pts;
            }
        }
        score1 = t1;
        score2 = t2;
    }

    // CLICK A BALL DIRECTLY: 0 -> 1 -> 2 -> 3 -> 5 (Maksimal 5, tidak balik ke 0)
    async function clickBall(team, ballNum) {
        if (!await ensureOperatorAuth()) return;
        lastUserActionTimestamp = Date.now();
        
        let current = ballPoints[ballNum] || 0;
        // Official Gateball rules progression: 1 -> 2 -> 3 -> 5 (Maksimal 5)
        if (current === 0) {
            ballPoints[ballNum] = 1;
        } else if (current === 1) {
            ballPoints[ballNum] = 2;
        } else if (current === 2) {
            ballPoints[ballNum] = 3;
        } else if (current === 3) {
            ballPoints[ballNum] = 5;
        } else if (current >= 5) {
            // Sudah 5 poin (Maksimal), tetap di 5 (gunakan tombol Reset untuk mengosongkan)
            playBuzzer(950, 0.1, 'sine');
            return;
        }

        calculateTotalFromBalls();
        if (matchStatus === 'pending') matchStatus = 'ongoing';

        playBuzzer(team === 1 ? 750 : 850, 0.15, 'sine');
        renderUI();
        saveLiveStateToServer();
    }

    // Reset a single ball's score to 0 with Confirmation Alert
    async function resetSingleBall(team, ballNum) {
        if (!await ensureOperatorAuth()) return;
        const teamColor = team === 1 ? 'Merah' : 'Putih';
        const confirm = await Swal.fire({
            title: `Reset Bola ${ballNum}?`,
            text: `Skor Bola ${ballNum} (${teamColor}) akan dikembalikan ke 0 (Target: Gate 1).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b'
        });

        if (!confirm.isConfirmed) return;

        lastUserActionTimestamp = Date.now();
        ballPoints[ballNum] = 0;

        calculateTotalFromBalls();
        playBuzzer(400, 0.12, 'sine');
        renderUI();
        saveLiveStateToServer();

        Swal.fire({
            icon: 'success',
            title: `Bola ${ballNum} Direset`,
            text: 'Target kembali ke Gate 1.',
            timer: 1200,
            showConfirmButton: false
        });
    }

    // Reset all balls for a team with Confirmation Alert
    async function resetTeamBalls(team) {
        if (!await ensureOperatorAuth()) return;
        const teamName = team === 1 ? matchState.team1 : matchState.team2;
        const teamColor = team === 1 ? 'Merah' : 'Putih';
        
        const confirm = await Swal.fire({
            title: `Reset Semua Bola Tim ${teamColor}?`,
            text: `Semua bola ${teamName} akan dikembalikan ke 0 (Target: Gate 1).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset Semua Bola',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b'
        });

        if (!confirm.isConfirmed) return;

        lastUserActionTimestamp = Date.now();
        const start = team === 1 ? 1 : 2;
        for (let b = start; b <= 10; b += 2) {
            ballPoints[b] = 0;
        }
        calculateTotalFromBalls();
        playBuzzer(400, 0.15, 'sine');
        renderUI();
        saveLiveStateToServer();

        Swal.fire({
            icon: 'success',
            title: `Semua Bola Tim ${teamColor} Direset`,
            timer: 1200,
            showConfirmButton: false
        });
    }

    // Internal Local Timer Ticker (Runs ticking on screen without requiring password)
    function runTimerTicker() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            if (timerStatus === 'running' && timerSeconds > 0) {
                timerSeconds--;
                renderUI();

                if (timerSeconds === 10) {
                    playBuzzer(600, 0.3);
                }

                if (timerSeconds === 0) {
                    stopTimerTicker();
                    timerStatus = 'stopped';
                    playBuzzer(880, 1.2, 'sawtooth');
                    renderUI();
                    Swal.fire({
                        title: 'WAKTU PERTANDINGAN HABIS!',
                        text: `Skor Akhir: ${matchState.team1} (${score1}) - (${score2}) ${matchState.team2}`,
                        icon: 'info',
                        confirmButtonColor: '#002244'
                    });
                }
            } else if (timerSeconds <= 0) {
                stopTimerTicker();
            }
        }, 1000);
    }

    function stopTimerTicker() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = null;
    }

    // Operator Timer Actions (Requires Password Verification on Click)
    document.getElementById('btnStartTimer').addEventListener('click', async () => {
        if (!await ensureOperatorAuth()) return;
        lastUserActionTimestamp = Date.now();
        timerStatus = 'running';
        if (matchStatus === 'pending') matchStatus = 'ongoing';

        playBuzzer(880, 0.4, 'sawtooth');
        runTimerTicker();
        renderUI();
        saveLiveStateToServer();
    });

    document.getElementById('btnPauseTimer').addEventListener('click', async () => {
        if (!await ensureOperatorAuth()) return;
        lastUserActionTimestamp = Date.now();
        stopTimerTicker();
        timerStatus = 'paused';
        renderUI();
        saveLiveStateToServer();
    });

    document.getElementById('btnResetTimer').addEventListener('click', async () => {
        if (!await ensureOperatorAuth()) return;
        const confirm = await Swal.fire({
            title: 'Reset Waktu Pertandingan?',
            text: 'Waktu akan dikembalikan ke 30:00 menit.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset Waktu',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#002244',
            cancelButtonColor: '#64748b'
        });

        if (!confirm.isConfirmed) return;

        lastUserActionTimestamp = Date.now();
        stopTimerTicker();
        timerSeconds = TOTAL_MATCH_SECONDS;
        timerStatus = 'stopped';
        renderUI();
        saveLiveStateToServer();
    });

    document.getElementById('btnAddOneMin').addEventListener('click', async () => {
        if (!await ensureOperatorAuth()) return;
        lastUserActionTimestamp = Date.now();
        timerSeconds = Math.min(3600, timerSeconds + 60);
        renderUI();
        saveLiveStateToServer();
    });

    document.getElementById('btnSubOneMin').addEventListener('click', async () => {
        if (!await ensureOperatorAuth()) return;
        lastUserActionTimestamp = Date.now();
        timerSeconds = Math.max(0, timerSeconds - 60);
        renderUI();
        saveLiveStateToServer();
    });

    // Finish Match
    document.getElementById('btnFinishMatch').addEventListener('click', async () => {
        if (!await ensureOperatorAuth()) return;
        lastUserActionTimestamp = Date.now();
        const confirmFinish = await Swal.fire({
            title: 'Selesaikan Pertandingan?',
            html: `
                Hasil Akhir:<br>
                <strong style="font-size: 1.3rem; color: #dc2626;">${matchState.team1} (${score1})</strong> VS <strong style="font-size: 1.3rem; color: #0284c7;">${matchState.team2} (${score2})</strong>
                <p style="margin-top: 10px; font-size: 0.85rem; color: #64748b;">Hasil pertandingan ini akan dikunci dan langsung memperbarui klasemen turnamen di semua monitor.</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesaikan & Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b'
        });

        if (confirmFinish.isConfirmed) {
            pauseTimer();
            matchStatus = 'completed';
            await saveLiveStateToServer();
            Swal.fire({
                title: 'Pertandingan Selesai!',
                text: 'Hasil dan klasemen telah diperbarui.',
                icon: 'success',
                confirmButtonColor: '#002244'
            });
        }
    });

    // Reset Match Entirely
    document.getElementById('btnResetMatch').addEventListener('click', async () => {
        if (!await ensureOperatorAuth()) return;
        lastUserActionTimestamp = Date.now();
        const confirmReset = await Swal.fire({
            title: 'Kosongkan Skor Pertandingan?',
            text: 'Semua poin dan timer pada pertandingan ini akan direset kembali ke awal.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kosongkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b'
        });

        if (confirmReset.isConfirmed) {
            pauseTimer();
            score1 = 0;
            score2 = 0;
            ballPoints = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0 };
            timerSeconds = TOTAL_MATCH_SECONDS;
            timerStatus = 'stopped';
            matchStatus = 'pending';

            const formData = new FormData();
            formData.append('password', savedPassword);
            formData.append('score1', '');
            formData.append('score2', '');
            formData.append('timer_seconds', TOTAL_MATCH_SECONDS);
            formData.append('timer_status', 'stopped');
            formData.append('status', 'pending');
            formData.append('score_details_json', JSON.stringify(ballPoints));

            await fetch(API_UPDATE_MATCH, { method: 'POST', body: formData });
            renderUI();
            Swal.fire({
                title: 'Berhasil Dikosongkan',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });

    // Save Live State to Server (Robust AJAX with Password)
    async function saveLiveStateToServer() {
        isSavePending = true;
        const formData = new FormData();
        formData.append('password', savedPassword);
        formData.append('score1', score1);
        formData.append('score2', score2);
        formData.append('timer_seconds', timerSeconds);
        formData.append('timer_status', timerStatus);
        formData.append('status', matchStatus);
        formData.append('score_details_json', JSON.stringify(ballPoints));

        try {
            const resp = await fetch(API_UPDATE_MATCH, {
                method: 'POST',
                body: formData
            });

            if (resp.status === 403) {
                savedPassword = '';
                sessionStorage.removeItem('gateball_pwd');
                renderOperatorState();
                Swal.fire({
                    icon: 'error',
                    title: 'Otorisasi Dibutuhkan',
                    text: 'Password otorisasi turnamen dibutuhkan untuk menyimpan perubahan.',
                    confirmButtonColor: '#002244'
                });
                return;
            }

            const res = await resp.json();
            if (res.status === 'success' && res.data) {
                matchState = res.data;
                lastMatchStateSig = `${score1}_${score2}_${matchStatus}_${JSON.stringify(ballPoints)}`;
            }
        } catch(e) {
            console.error('Save live state error:', e);
        } finally {
            isSavePending = false;
        }
    }

    // Polling Sync from Server (every 2.5 seconds)
    async function syncFromServer(silent = true) {
        if (Date.now() - lastUserActionTimestamp < 3500 || isSavePending) {
            return;
        }

        const syncIcon = document.getElementById('syncIcon');
        if (!silent && syncIcon) syncIcon.classList.add('fa-spin');

        try {
            const resp = await fetch(API_MATCH_DATA);
            const resJson = await resp.json();

            if (resJson.status === 'success' && resJson.data) {
                const s = resJson.data;

                if (s.timer_status === 'running') {
                    timerStatus = 'running';
                    timerSeconds = parseInt(s.timer_seconds) || 1800;
                    if (!timerInterval) runTimerTicker();
                } else if (Date.now() - lastUserActionTimestamp >= 3500) {
                    timerStatus = s.timer_status || 'stopped';
                    timerSeconds = parseInt(s.timer_seconds) || 1800;
                    stopTimerTicker();
                }

                if (Date.now() - lastUserActionTimestamp >= 3500 && !isSavePending) {
                    const newScore1 = s.score1 !== null ? parseInt(s.score1) : 0;
                    const newScore2 = s.score2 !== null ? parseInt(s.score2) : 0;
                    const newStatus = s.status || 'pending';
                    let newBallPoints = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0, 7: 0, 8: 0, 9: 0, 10: 0 };

                    try {
                        if (s.score_details_json) {
                            const parsed = JSON.parse(s.score_details_json);
                            if (typeof parsed === 'object') {
                                for (let b = 1; b <= 10; b++) {
                                    if (typeof parsed[b] === 'number') {
                                        newBallPoints[b] = parsed[b];
                                    }
                                }
                            }
                        }
                    } catch(e) {}

                    const newSig = `${newScore1}_${newScore2}_${newStatus}_${JSON.stringify(newBallPoints)}`;

                    // Detect external score update (Rule 1: no double notification)
                    if (lastMatchStateSig && lastMatchStateSig !== newSig && (newScore1 !== score1 || newScore2 !== score2 || newStatus !== matchStatus)) {
                        let statusTag = '';
                        if (newStatus === 'completed') statusTag = ' <span style="color:#15803d;font-size:0.75rem;font-weight:700;">(Selesai)</span>';
                        else if (newStatus === 'ongoing') statusTag = ' <span style="color:#dc2626;font-size:0.75rem;font-weight:700;">(🔴 Live)</span>';

                        MatchScoreToast.fire({
                            icon: 'info',
                            title: '<span style="font-size:0.95rem;font-weight:800;color:#002244;"><i class="fas fa-bell text-warning"></i> Skor Terupdate!</span>',
                            html: `<div style="font-size:0.88rem;margin-top:4px;"><strong>${escapeHtml(matchState.team1)}</strong> <span style="color:#dc2626;font-weight:800;">${newScore1}</span> - <span style="color:#0284c7;font-weight:800;">${newScore2}</span> <strong>${escapeHtml(matchState.team2)}</strong>${statusTag}</div>`
                        });
                    }

                    score1 = newScore1;
                    score2 = newScore2;
                    matchStatus = newStatus;
                    ballPoints = newBallPoints;
                    lastMatchStateSig = newSig;

                    renderUI();
                }

                const now = new Date();
                const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                document.getElementById('syncTimeText').textContent = timeStr;
            }
        } catch(err) {
            console.error('Sync error:', err);
        } finally {
            if (!silent && syncIcon) syncIcon.classList.remove('fa-spin');
        }
    }

    document.getElementById('btnSyncNow').addEventListener('click', () => syncFromServer(false));

    // Fast sync interval
    setInterval(() => {
        syncFromServer(true);
    }, 2500);

    // Initial render (Clean Viewer Mode, no password prompt on load)
    renderOperatorState();
    renderUI();
    if (timerStatus === 'running') {
        runTimerTicker();
    }
</script>

</body>
</html>
