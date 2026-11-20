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
            --accent-pink: #ec4899;
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
            padding: 18px 24px;
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
            height: 52px;
            width: auto;
            border-radius: 8px;
            background: #ffffff;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            object-fit: contain;
        }

        .header-titles h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.3rem;
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
            font-size: 0.82rem;
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
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 0.86rem;
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

        .btn-action-print {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-action-print:hover {
            background-color: #0369a1;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
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

        .btn-action-fit {
            background-color: #059669;
            color: #ffffff;
        }
        .btn-action-fit:hover {
            background-color: #047857;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
        }
        .btn-action-fit.active {
            background-color: #10b981;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.4);
        }

        /* CATEGORY TABS */
        .category-nav-bar {
            background: #ffffff;
            border-bottom: 2px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        .category-nav-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            gap: 12px;
        }

        .category-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 24px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-muted);
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .category-tab-btn:hover {
            color: var(--pupr-blue);
        }

        .category-tab-btn.active.tab-putra,
        .category-tab-btn.active.tab-ganda-putra {
            color: var(--pupr-blue);
            border-bottom-color: var(--pupr-blue);
        }

        .category-tab-btn.active.tab-putri,
        .category-tab-btn.active.tab-ganda-putri {
            color: var(--accent-pink);
            border-bottom-color: var(--accent-pink);
        }

        .category-badge-count {
            font-size: 0.72rem;
            padding: 2px 8px;
            border-radius: 12px;
            background: #e2e8f0;
            color: #475569;
            font-weight: 700;
        }

        .category-tab-btn.active.tab-putra .category-badge-count,
        .category-tab-btn.active.tab-ganda-putra .category-badge-count {
            background: #e0e7ff;
            color: #3730a3;
        }

        .category-tab-btn.active.tab-putri .category-badge-count,
        .category-tab-btn.active.tab-ganda-putri .category-badge-count {
            background: #fce7f3;
            color: #9d174d;
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
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .meta-pill.pill-putra i { color: #2563eb; }
        .meta-pill.pill-putri i { color: #ec4899; }

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
            min-height: 520px;
        }

        .bracket-svg-canvas {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
        }

        .bracket-tree {
            display: flex;
            gap: 45px;
            min-width: max-content;
            align-items: stretch;
            padding: 10px 0;
            position: relative;
            transform-origin: top left;
            transition: transform 0.2s ease;
        }

        .bracket-round-column {
            display: flex;
            flex-direction: column;
            width: 270px;
            flex-shrink: 0;
            position: relative;
            z-index: 2;
        }

        /* 10 PESERTA COMPACT (PAS LAYAR & TANPA PERLU GESER-GESER) */
        .bracket-tree.is-10p {
            gap: 22px;
        }

        .bracket-tree.is-10p .bracket-round-column {
            width: 235px;
        }

        .bracket-tree.is-10p .match-card {
            border-radius: 8px;
        }

        .bracket-tree.is-10p .match-card-header {
            padding: 4px 8px;
            font-size: 0.7rem;
        }

        .bracket-tree.is-10p .player-slot {
            padding: 4px 8px;
            gap: 6px;
        }

        .bracket-tree.is-10p .player-seed {
            width: 20px;
            height: 20px;
            font-size: 0.68rem;
            border-radius: 4px;
        }

        .bracket-tree.is-10p .player-name {
            font-size: 0.82rem;
        }

        .bracket-tree.is-10p .balai-badge-pill {
            font-size: 0.62rem;
            padding: 1px 4px;
        }

        .bracket-tree.is-10p .score-input {
            width: 34px;
            height: 26px;
            font-size: 0.92rem;
        }

        .bracket-tree.is-10p .score-display-static {
            min-width: 30px;
            height: 26px;
            font-size: 0.88rem;
            padding: 0 4px;
        }

        .bracket-tree.is-10p .winner-check-badge {
            width: 20px;
            height: 20px;
            font-size: 0.65rem;
        }

        .bracket-tree.is-10p .btn-pick-winner {
            width: 24px;
            height: 24px;
            font-size: 0.7rem;
        }

        .bracket-tree.is-10p .round-header {
            padding: 8px 12px;
            margin-bottom: 12px;
            border-radius: 6px;
        }

        .bracket-tree.is-10p .round-header h3 {
            font-size: 0.88rem;
        }

        .bracket-tree.is-10p .round-header span {
            font-size: 0.68rem;
        }

        .bracket-tree.is-10p .round-matches-list {
            gap: 8px;
        }

        .bracket-tree.is-10p .match-note-banner {
            padding: 3px 6px;
            font-size: 0.68rem;
        }

        .bracket-tree.is-10p .match-actions-footer {
            padding: 3px 8px 5px;
        }

        .bracket-tree.is-10p .btn-save-match {
            padding: 3px 10px;
            font-size: 0.72rem;
        }

        /* PRINT STAGE CONTAINERS (TRANSPARAN PADA MODE WEB NORMAL) */
        .print-stage-wrapper,
        .print-stage-inner {
            display: flex;
            flex-direction: column;
            flex: 1;
            width: 100%;
        }

        /* PRINT PREVIEW & CETAK/PDF 1 HALAMAN PENUH (IDENTIK TAMPILAN WEB) */
        body.print-preview-mode *,
        body.print-preview-mode *::before,
        body.print-preview-mode *::after {
            transition: none !important;
            animation: none !important;
        }

        body.print-preview-mode,
        body.print-preview-mode html {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            min-height: 0 !important;
            overflow: hidden !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            break-after: avoid !important;
            break-inside: avoid !important;
        }

        body.print-preview-mode {
            display: block !important;
        }

        body.print-preview-mode > *:not(.print-stage-wrapper) {
            display: none !important;
        }

        body.print-preview-mode .header-actions,
        body.print-preview-mode .category-nav-bar,
        body.print-preview-mode .site-footer,
        body.print-preview-mode .btn-save-match,
        body.print-preview-mode .btn-pick-winner,
        body.print-preview-mode .score-input,
        body.print-preview-mode .modal-overlay,
        body.print-preview-mode .match-actions-footer {
            display: none !important;
        }

        body.print-preview-mode .score-display-static,
        body.print-preview-mode .score-print-display {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 38px !important;
            height: 32px !important;
            padding: 0 6px !important;
            text-align: center !important;
            font-size: 1rem !important;
            font-weight: 800 !important;
            color: #1e293b !important;
            background: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 6px !important;
        }

        body.print-preview-mode .bracket-tree.is-10p .score-display-static,
        body.print-preview-mode .bracket-tree.is-10p .score-print-display {
            min-width: 30px !important;
            height: 26px !important;
            font-size: 0.88rem !important;
            padding: 0 4px !important;
        }

        body.print-preview-mode .score-display-static.is-winner,
        body.print-preview-mode .score-print-display.is-winner {
            background: #dcfce7 !important;
            color: #15803d !important;
            border-color: #86efac !important;
        }

        body.print-preview-mode .winner-check-badge,
        body.print-preview-mode .winner-print-check {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 22px !important;
            height: 22px !important;
            border-radius: 50% !important;
            background: #10b981 !important;
            color: #ffffff !important;
            font-size: 0.72rem !important;
            margin-left: 2px !important;
            flex-shrink: 0 !important;
        }

        body.print-preview-mode .bracket-tree.is-10p .winner-check-badge,
        body.print-preview-mode .bracket-tree.is-10p .winner-print-check {
            width: 20px !important;
            height: 20px !important;
            font-size: 0.65rem !important;
        }

        body.print-preview-mode .print-stage-inner {
            display: flex !important;
            flex-direction: column !important;
            flex: none !important;
            background-color: var(--bg-canvas) !important;
            background-image:
                radial-gradient(#cbd5e1 1px, transparent 1px),
                radial-gradient(#cbd5e1 1px, #f8fafc 1px) !important;
            background-size: 32px 32px !important;
            background-position: 0 0, 16px 16px !important;
            padding: 10px 14px !important;
            box-sizing: border-box !important;
        }

        body.print-preview-mode .site-header {
            position: static !important;
            width: 100% !important;
            border-radius: 12px !important;
            padding: 12px 20px !important;
            margin-bottom: 10px !important;
            flex-shrink: 0 !important;
        }

        body.print-preview-mode .header-container {
            max-width: 100% !important;
            width: 100% !important;
        }

        body.print-preview-mode .main-wrapper {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
        }

        body.print-preview-mode .tournament-banner {
            width: 100% !important;
            margin-bottom: 10px !important;
            padding: 14px 20px !important;
            flex-shrink: 0 !important;
        }

        body.print-preview-mode .bracket-scroll-container {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            min-height: 0 !important;
            padding: 16px 20px !important;
            margin: 0 !important;
            overflow: visible !important;
        }

        body.print-preview-mode .bracket-tree {
            flex: 1 !important;
            width: 100% !important;
            min-width: 100% !important;
            height: 100% !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: stretch !important;
            padding: 4px 0 !important;
            transform: none !important;
            zoom: 1 !important;
            gap: 48px !important;
        }

        body.print-preview-mode .bracket-tree.is-10p {
            gap: 30px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree {
            gap: 32px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree.is-10p {
            gap: 22px !important;
        }

        body.print-preview-mode .bracket-round-column {
            flex: 1 !important;
            width: auto !important;
            min-width: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
        }

        body.print-preview-mode .round-matches-list {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            position: relative !important;
        }

        /* PENYESUAIAN PROPORSIONAL PORTRAIT AGAR KERTAS TERISI PENUH ATAS-BAWAH */
        body.print-preview-mode.print-portrait-mode .site-header {
            padding: 16px 22px !important;
            margin-bottom: 12px !important;
        }

        body.print-preview-mode.print-portrait-mode .tournament-banner {
            padding: 16px 22px !important;
            margin-bottom: 12px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-scroll-container {
            padding: 20px 22px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree:not(.is-10p) .round-header {
            padding: 16px 14px !important;
            margin-bottom: 24px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree:not(.is-10p) .match-card-header {
            padding: 10px 14px !important;
            font-size: 0.82rem !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree:not(.is-10p) .player-slot {
            padding: 18px 14px !important;
            gap: 12px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree:not(.is-10p) .player-seed {
            width: 28px !important;
            height: 28px !important;
            font-size: 0.84rem !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree:not(.is-10p) .player-name {
            font-size: 1rem !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree:not(.is-10p) .balai-badge-pill {
            font-size: 0.76rem !important;
            padding: 2px 8px !important;
            margin-top: 2px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree:not(.is-10p) .match-note-banner {
            padding: 9px 14px !important;
            font-size: 0.78rem !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree.is-10p .round-header {
            padding: 11px 12px !important;
            margin-bottom: 16px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree.is-10p .match-card-header {
            padding: 6px 10px !important;
            font-size: 0.74rem !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree.is-10p .player-slot {
            padding: 9px 10px !important;
            gap: 8px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree.is-10p .player-name {
            font-size: 0.86rem !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree.is-10p .balai-badge-pill {
            font-size: 0.66rem !important;
            padding: 2px 6px !important;
        }

        body.print-preview-mode.print-portrait-mode .bracket-tree.is-10p .match-note-banner {
            padding: 5px 10px !important;
            font-size: 0.72rem !important;
        }

        /* MODAL ORIENTATION SELECT CARDS */
        .orientation-select-card {
            border: 2px solid #cbd5e1;
            border-radius: 10px;
            padding: 18px 14px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .orientation-select-card:hover {
            border-color: #0b3c74;
            background: #f0f7ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(11, 60, 116, 0.12);
        }

        .orient-icon-box {
            font-size: 2.2rem;
            margin-bottom: 8px;
        }

        .orient-card-title {
            font-weight: 800;
            font-size: 1rem;
            color: #002244;
        }

        .orient-card-sub {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 4px;
            flex-grow: 1;
        }

        .bracket-line {
            fill: none;
            stroke: #0b3c74;
            stroke-width: 3px;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: all 0.25s ease;
        }

        .bracket-line.winner {
            stroke: #10b981;
            stroke-width: 3.5px;
            filter: drop-shadow(0 2px 4px rgba(16, 185, 129, 0.35));
        }

        .bracket-dot {
            fill: #0b3c74;
            transition: all 0.25s ease;
        }

        .bracket-dot.winner {
            fill: #10b981;
        }

        .round-header {
            text-align: center;
            background: linear-gradient(135deg, #002244 0%, #0b3c74 100%);
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            box-shadow: 0 3px 8px rgba(0, 34, 68, 0.15);
            min-height: 58px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
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
            justify-content: space-between;
            flex-grow: 1;
            gap: 18px;
            position: relative;
        }

        .match-unit-wrap {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .match-unit-wrap.group-bawah-first-wrap {
            margin-top: 10px;
        }

        /* MATCH CARD */
        .match-card {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
            position: relative;
        }

        .match-card:hover {
            border-color: var(--pupr-blue);
            box-shadow: 0 6px 16px rgba(11, 60, 116, 0.12);
            transform: translateY(-2px);
        }

        /* BYE STATUS CARDS */
        .bye-slot-card {
            background: #ffffff;
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .bye-slot-card:hover {
            border-color: #94a3b8;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .bye-slot-card.bye-slot-finalist {
            background: #f0fdf4;
            border: 1.5px dashed #86efac;
        }

        .bye-slot-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bye-slot-body {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .match-card.is-final {
            border-color: var(--pupr-yellow);
            border-width: 2px;
            box-shadow: 0 4px 16px rgba(247, 168, 27, 0.2);
        }

        .match-card.is-bye-card {
            border-style: dashed;
            border-color: #86efac;
            background: #fafdfb;
        }

        .match-card.group-atas {
            border-left: 4px solid #3b82f6;
        }

        .match-card.group-bawah {
            border-left: 4px solid #10b981;
        }

        .match-card.group-grand-final {
            border-left: 4px solid #f59e0b;
        }

        .bracket-group-section-title {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            width: fit-content;
            text-transform: uppercase;
        }

        .bracket-group-section-title.group-atas {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .bracket-group-section-title.group-bawah {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .bracket-group-section-title.group-grand-final {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .group-mini-pill {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        .group-atas-pill {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .group-bawah-pill {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .group-gf-pill {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .match-card.is-bye-card .match-card-header {
            background: #ecfdf5;
            color: #065f46;
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
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .player-name {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--text-dark);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .balai-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            color: #475569;
            background: #f1f5f9;
            padding: 1px 6px;
            border-radius: 4px;
            font-weight: 600;
            width: fit-content;
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

        /* STATIC SCORE DISPLAY (UNTUK PENGUNJUNG TANPA LOGIN) */
        .score-display-static {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 32px;
            padding: 0 6px;
            text-align: center;
            font-size: 1rem;
            font-weight: 800;
            color: #1e293b;
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
        }

        .score-display-static.is-winner {
            background: #dcfce7;
            color: #15803d;
            border-color: #86efac;
        }

        .winner-check-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #10b981;
            color: #ffffff;
            font-size: 0.72rem;
            margin-left: 2px;
            flex-shrink: 0;
        }

        /* PRINT COMPATIBILITY SCORE DISPLAY (SELARAS TAMPILAN WEB) */
        .score-print-display,
        .winner-print-check {
            display: none;
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

        .match-note-banner {
            background: #eff6ff;
            color: #1e40af;
            font-size: 0.73rem;
            font-weight: 700;
            padding: 5px 12px;
            border-top: 1px solid #dbeafe;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge-bye-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            color: #0369a1;
            background: #e0f2fe;
            padding: 1px 6px;
            border-radius: 4px;
            font-weight: 700;
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
            max-width: 760px;
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
            padding: 18px 24px;
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
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            font-size: 0.92rem;
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
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .btn-count-opt {
            padding: 6px 14px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            background: #f8fafc;
            font-weight: 700;
            font-size: 0.85rem;
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
            margin-top: 4px;
            margin-bottom: 12px;
        }

        .quick-chip {
            font-size: 0.74rem;
            padding: 3px 9px;
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
            gap: 8px;
            max-height: 320px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .participant-input-row {
            display: grid;
            grid-template-columns: 32px 1fr 1fr 34px;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            padding: 6px 8px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .participant-num {
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--text-muted);
            text-align: center;
        }

        .btn-del-participant {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: #fee2e2;
            color: #ef4444;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.15s ease;
        }
        .btn-del-participant:hover {
            background: #ef4444;
            color: #ffffff;
        }

        .btn-add-participant-row {
            margin-top: 10px;
            background: #f1f5f9;
            color: var(--pupr-blue);
            border: 1.5px dashed #cbd5e1;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.86rem;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .btn-add-participant-row:hover {
            background: #e2e8f0;
            border-color: var(--pupr-blue);
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
            .bracket-round-column { width: 290px; }
            .participant-input-row {
                grid-template-columns: 24px 1fr 1fr 30px;
                gap: 4px;
            }
        }
        @media print {
            *,
            *::before,
            *::after {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                transition: none !important;
                animation: none !important;
            }

            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                min-height: 0 !important;
                overflow: hidden !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-after: avoid !important;
                break-inside: avoid !important;
            }

            body {
                display: block !important;
            }

            body > *:not(.print-stage-wrapper) {
                display: none !important;
            }

            .header-actions,
            .category-nav-bar,
            .site-footer,
            .btn-save-match,
            .btn-pick-winner,
            .score-input,
            .modal-overlay,
            .match-actions-footer {
                display: none !important;
            }

            .score-display-static,
            .score-print-display {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-width: 38px !important;
                height: 32px !important;
                padding: 0 6px !important;
                text-align: center !important;
                font-size: 1rem !important;
                font-weight: 800 !important;
                color: #1e293b !important;
                background: #f8fafc !important;
                border: 1.5px solid #cbd5e1 !important;
                border-radius: 6px !important;
            }

            .bracket-tree.is-10p .score-display-static,
            .bracket-tree.is-10p .score-print-display {
                min-width: 30px !important;
                height: 26px !important;
                font-size: 0.88rem !important;
                padding: 0 4px !important;
            }

            .score-display-static.is-winner,
            .score-print-display.is-winner {
                background: #dcfce7 !important;
                color: #15803d !important;
                border-color: #86efac !important;
            }

            .winner-check-badge,
            .winner-print-check {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 22px !important;
                height: 22px !important;
                border-radius: 50% !important;
                background: #10b981 !important;
                color: #ffffff !important;
                font-size: 0.72rem !important;
                margin-left: 2px !important;
                flex-shrink: 0 !important;
            }

            .bracket-tree.is-10p .winner-check-badge,
            .bracket-tree.is-10p .winner-print-check {
                width: 20px !important;
                height: 20px !important;
                font-size: 0.65rem !important;
            }

            .print-stage-inner {
                display: flex !important;
                flex-direction: column !important;
                flex: none !important;
                background-color: var(--bg-canvas) !important;
                background-image:
                    radial-gradient(#cbd5e1 1px, transparent 1px),
                    radial-gradient(#cbd5e1 1px, #f8fafc 1px) !important;
                background-size: 32px 32px !important;
                background-position: 0 0, 16px 16px !important;
                padding: 10px 14px !important;
                box-sizing: border-box !important;
            }

            .site-header {
                position: static !important;
                width: 100% !important;
                border-radius: 12px !important;
                padding: 12px 20px !important;
                margin-bottom: 10px !important;
                flex-shrink: 0 !important;
            }

            .header-container {
                max-width: 100% !important;
                width: 100% !important;
            }

            .main-wrapper {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                min-height: 0 !important;
            }

            .tournament-banner {
                width: 100% !important;
                margin-bottom: 10px !important;
                padding: 14px 20px !important;
                flex-shrink: 0 !important;
            }

            .bracket-scroll-container {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                min-height: 0 !important;
                padding: 16px 20px !important;
                margin: 0 !important;
                overflow: visible !important;
            }

            .bracket-tree {
                flex: 1 !important;
                width: 100% !important;
                min-width: 100% !important;
                height: 100% !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: stretch !important;
                padding: 4px 0 !important;
                transform: none !important;
                zoom: 1 !important;
                gap: 48px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .bracket-tree.is-10p {
                gap: 30px !important;
            }

            body.print-portrait-mode .bracket-tree {
                gap: 32px !important;
            }

            body.print-portrait-mode .bracket-tree.is-10p {
                gap: 22px !important;
            }

            .bracket-round-column {
                flex: 1 !important;
                width: auto !important;
                min-width: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                height: 100% !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .round-matches-list {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                position: relative !important;
            }

            .match-card {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            @page {
                size: A4 landscape;
                margin: 4mm;
            }
        }
    </style>
    <!-- Dynamic Print Orientation Tag -->
    <style id="printOrientationStyle"></style>
</head>
<body>

    <div id="printStageWrapper" class="print-stage-wrapper">
        <div id="printStageInner" class="print-stage-inner">

            <!-- TOP HEADER -->
            <header class="site-header">
                <div class="header-container">
                    <div class="header-brand">
                        <img src="<?= esc($logoUrl); ?>" alt="Logo Satker PPS" class="header-logo" onerror="this.src='https://via.placeholder.com/52?text=PUPR'">
                        <div class="header-titles">
                            <h1><i class="fas fa-table-tennis-paddle-ball text-warning mr-1"></i> TURNAMEN <span>TENIS MEJA</span></h1>
                        </div>
                    </div>

                    <div class="header-actions">
                        <?php if (! empty($isLoggedIn)): ?>
                            <!-- ACAK POSISI (SUPERADMINISTRATOR ONLY - HANYA KETIKA LOGIN) -->
                            <button type="button" class="btn-action btn-action-shuffle" id="btnAcakPosisi">
                                <i class="fas fa-shuffle"></i> Acak Posisi
                                <span style="font-size: 0.7rem; background: rgba(0,0,0,0.25); padding: 2px 6px; border-radius: 4px; margin-left: 2px;">
                                    <i class="fas fa-lock"></i> Superadmin
                                </span>
                            </button>

                            <!-- ATUR PESERTA (HANYA KETIKA LOGIN) -->
                            <button type="button" class="btn-action btn-action-primary" id="btnOpenSetup">
                                <i class="fas fa-users-gear"></i> Atur Peserta
                            </button>

                            <!-- RESET SKOR (HANYA KETIKA LOGIN) -->
                            <button type="button" class="btn-action btn-action-light" id="btnResetSkor" title="Reset Skor Pertandingan">
                                <i class="fas fa-rotate-left"></i>
                            </button>
                        <?php else: ?>
                            <!-- TOMBOL MASUK UNTUK PENGELOLA TURNAMEN -->
                            <a href="<?= site_url('masuk'); ?>" class="btn-action btn-action-primary" title="Masuk (Login) untuk mengelola skor, pemenang, dan bagan turnamen">
                                <i class="fas fa-right-to-bracket"></i> Masuk
                            </a>
                        <?php endif; ?>

                        <!-- PAS LAYAR (TANPA PERLU GESER-GESER) -->
                        <button type="button" class="btn-action btn-action-fit active" id="btnFitScreen" onclick="toggleFitScreen()" title="Mode Pas Layar (Otomatis menyesuaikan layar tanpa perlu menggeser)">
                            <i class="fas fa-compress"></i> Pas Layar
                        </button>

                        <!-- CETAK & EKSPOR PDF (DIGABUNGKAN - PILIHAN LANDSCAPE / PORTRAIT) -->
                        <button type="button" class="btn-action btn-action-print" id="btnCetakPdf" onclick="openPrintModal()" title="Cetak Bagan atau Ekspor PDF 1 Halaman Penuh (Pilihan Landscape / Portrait)">
                            <i class="fas fa-print"></i> <i class="fas fa-file-pdf" style="margin-left: -2px;"></i> Cetak / Ekspor PDF
                        </button>

                        <?php if (! empty($isLoggedIn)): ?>
                            <!-- SHORTCUT DASHBOARD ADMIN -->
                            <a href="<?= site_url('admin/dashboard'); ?>" class="btn-action btn-action-light" title="Buka Dashboard Admin Satker PPS">
                                <i class="fas fa-gauge-high"></i> Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- CATEGORY TABS (PUTRA, PUTRI, GANDA PUTRA, GANDA PUTRI) -->
            <nav class="category-nav-bar">
                <div class="category-nav-container">
                    <button type="button" class="category-tab-btn tab-putra active" id="tabBtnPutra" onclick="switchCategory('putra')">
                        <i class="fas fa-mars text-primary"></i> Putra
                        <span class="category-badge-count" id="countBadgePutra"><?= (int) ($putraTournament['participant_count'] ?? 5); ?> Peserta</span>
                    </button>
                    <button type="button" class="category-tab-btn tab-putri" id="tabBtnPutri" onclick="switchCategory('putri')">
                        <i class="fas fa-venus" style="color: #ec4899;"></i> Putri
                        <span class="category-badge-count" id="countBadgePutri"><?= (int) ($putriTournament['participant_count'] ?? 5); ?> Peserta</span>
                    </button>
                    <button type="button" class="category-tab-btn tab-ganda-putra" id="tabBtnGandaPutra" onclick="switchCategory('ganda_putra')">
                        <i class="fas fa-user-group text-primary"></i> Ganda Putra
                        <span class="category-badge-count" id="countBadgeGandaPutra"><?= (int) ($gandaPutraTournament['participant_count'] ?? 5); ?> Peserta</span>
                    </button>
                    <button type="button" class="category-tab-btn tab-ganda-putri" id="tabBtnGandaPutri" onclick="switchCategory('ganda_putri')">
                        <i class="fas fa-user-group" style="color: #ec4899;"></i> Ganda Putri
                        <span class="category-badge-count" id="countBadgeGandaPutri"><?= (int) ($gandaPutriTournament['participant_count'] ?? 5); ?> Peserta</span>
                    </button>
                </div>
            </nav>

            <!-- MAIN BODY -->
            <main class="main-wrapper">

                <!-- TOURNAMENT INFO & PODIUM -->
                <section class="tournament-banner">
                    <div class="tournament-meta">
                        <div>
                            <h2 style="font-family: 'Montserrat', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--pupr-navy);" id="tournamentTitle">
                                Turnamen Tenis Meja Putra
                            </h2>
                            <div style="display: flex; gap: 8px; align-items: center; margin-top: 6px; flex-wrap: wrap;">
                                <span class="meta-pill pill-putra" id="metaCategoryPill">
                                    <i class="fas fa-mars"></i>
                                    <span id="tournamentCategoryLabel">Kategori Putra</span>
                                </span>
                                <span class="meta-pill">
                                    <i class="fas fa-users"></i>
                                    <span id="tournamentCount">5 Peserta</span>
                                </span>
                                <span class="badge-status badge-ongoing" id="tournamentStatusBadge">
                                    <i class="fas fa-clock mr-1"></i> Sedang Berlangsung
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
                                <p id="podiumChampion">- Menunggu Final -</p>
                            </div>
                        </div>

                        <!-- JUARA 2 -->
                        <div class="podium-item runner-up">
                            <div class="podium-medal">🥈</div>
                            <div class="podium-details">
                                <h4>Juara 2 (Runner-Up)</h4>
                                <p id="podiumRunnerUp">- Menunggu Final -</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- INTERACTIVE BRACKET SECTION -->
                <section class="bracket-scroll-container" id="bracketScrollContainer">
                    <div class="bracket-tree" id="bracketTree">
                        <svg id="bracketSvgCanvas" class="bracket-svg-canvas"></svg>
                        <!-- RENDERED DYNAMICALLY BY JAVASCRIPT -->
                    </div>
                </section>

            </main>

        </div>
    </div>

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
                        <label for="formCategory">Kategori Turnamen</label>
                        <select id="formCategory" class="form-control" onchange="onModalCategoryChange(this.value)">
                            <option value="putra">Turnamen Putra</option>
                            <option value="putri">Turnamen Putri</option>
                            <option value="ganda_putra">Turnamen Ganda Putra</option>
                            <option value="ganda_putri">Turnamen Ganda Putri</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="formTitle">Judul Turnamen</label>
                        <input type="text" id="formTitle" class="form-control" value="Turnamen Tenis Meja Antar Balai PUPR" required>
                    </div>

                    <div class="form-group">
                        <label>Pilihan Cepat Jumlah Peserta</label>
                        <div class="participant-count-buttons">
                            <button type="button" class="btn-count-opt active" data-count="5">5 Peserta (Bagan Atas)</button>
                            <button type="button" class="btn-count-opt" data-count="10">10 Peserta (Gabungan Atas &amp; Bawah)</button>
                            <button type="button" class="btn-count-opt" data-count="4">4 Peserta</button>
                            <button type="button" class="btn-count-opt" data-count="8">8 Peserta</button>
                            <button type="button" class="btn-count-opt" data-count="16">16 Peserta</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Klik Cepat Asal Balai (Untuk Mengisi Kolom Balai):</label>
                        <div class="quick-chips-wrap">
                            <?php foreach ($defaultBalais as $b): ?>
                                <span class="quick-chip" onclick="fillNextEmptyBalai('<?= esc($b); ?>')"><?= esc($b); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="margin: 0;">Daftar Peserta (Nama Peserta &amp; Asal Balai):</label>
                            <span style="font-size: 0.76rem; color: #64748b;">(1 Balai bisa memiliki lebih dari 1 nama peserta)</span>
                        </div>
                        <div class="participant-inputs-container" id="participantInputs">
                            <!-- Injected dynamically -->
                        </div>
                        <button type="button" class="btn-add-participant-row" id="btnAddParticipantRow">
                            <i class="fas fa-plus-circle"></i> Tambah Peserta Baru
                        </button>
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

    <!-- MODAL GABUNGAN: PILIH ORIENTASI CETAK & EKSPOR PDF BAGAN (LANDSCAPE / PORTRAIT 1 HALAMAN PENUH) -->
    <div class="modal-overlay" id="printModal">
        <div class="modal-card" style="max-width: 540px;">
            <div class="modal-header">
                <h3><i class="fas fa-print text-primary mr-1"></i> <i class="fas fa-file-pdf text-danger mr-1"></i> Cetak &amp; Ekspor PDF Bagan</h3>
                <button type="button" class="modal-close" onclick="closePrintModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p style="font-size: 0.88rem; color: #475569; margin-bottom: 16px;">
                    Pilih orientasi kertas (<strong>Landscape</strong> atau <strong>Portrait</strong>) untuk mencetak langsung ke printer atau menyimpan sebagai dokumen <strong>PDF 1 Halaman Penuh</strong> dengan tampilan persis seperti web:
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <!-- LANDSCAPE CARD -->
                    <div class="orientation-select-card" onclick="executePrint('landscape')">
                        <div class="orient-icon-box" style="color: #0b3c74;">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="orient-card-title">Landscape (Mendatar)</div>
                        <div class="orient-card-sub">1 Halaman A4 Penuh Mendatar (Memenuhi seluruh lebar &amp; tinggi kertas)</div>
                        <button type="button" class="btn-action btn-action-primary" style="margin-top: 12px; width: 100%; justify-content: center;">
                            <i class="fas fa-print"></i> <i class="fas fa-file-pdf"></i> Cetak / PDF Landscape
                        </button>
                    </div>

                    <!-- PORTRAIT CARD -->
                    <div class="orientation-select-card" onclick="executePrint('portrait')">
                        <div class="orient-icon-box" style="color: #0b3c74;">
                            <i class="fas fa-file-lines"></i>
                        </div>
                        <div class="orient-card-title">Portrait (Tegak)</div>
                        <div class="orient-card-sub">1 Halaman A4 Penuh Tegak (Memenuhi seluruh lebar &amp; tinggi kertas)</div>
                        <button type="button" class="btn-action btn-action-fit" style="margin-top: 12px; width: 100%; justify-content: center; background: #0b3c74; color: white;">
                            <i class="fas fa-print"></i> <i class="fas fa-file-pdf"></i> Cetak / PDF Portrait
                        </button>
                    </div>
                </div>
                <div style="margin-top: 14px; padding: 10px 12px; background: #f0f7ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 0.78rem; color: #1e40af;">
                    <i class="fas fa-circle-info mr-1"></i> <strong>Panduan Cetak &amp; Simpan PDF 1 Halaman Penuh:</strong><br>
                    Pada dialog yang terbuka, pilih <strong>Printer</strong> untuk mencetak langsung atau pilih <strong>Save as PDF / Simpan sebagai PDF</strong> untuk mengunduh file PDF. Pastikan <strong>Layout</strong> sesuai pilihan (Landscape / Portrait) dan <strong>Margins</strong> diatur ke <em>Default</em> atau <em>Minimum</em>.
                </div>
            </div>
            <div class="modal-footer" style="justify-content: flex-end;">
                <button type="button" class="btn-action btn-action-light" onclick="closePrintModal()" style="color: #475569;">Tutup</button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // STATUS OTORISASI LOGIN & API ENDPOINTS
        const IS_LOGGED_IN = <?= ! empty($isLoggedIn) ? 'true' : 'false'; ?>;
        const LOGIN_URL = '<?= site_url("masuk"); ?>';
        const API_SETUP = '<?= site_url("tenismeja/api/setup"); ?>';
        const API_ACAK = '<?= site_url("tenismeja/api/acak-posisi"); ?>';
        const API_UPDATE_MATCH = '<?= site_url("tenismeja/api/update-match"); ?>';
        const API_RESET = '<?= site_url("tenismeja/api/reset"); ?>';
        const API_DATA = '<?= site_url("tenismeja/api/data"); ?>';
        const PDF_BASE_URL = '<?= site_url("tenismeja/pdf"); ?>';

        // Initial Data injected from PHP
        let allTournaments = {
            putra: <?= json_encode($putraTournament ?? null, JSON_UNESCAPED_UNICODE); ?>,
            putri: <?= json_encode($putriTournament ?? null, JSON_UNESCAPED_UNICODE); ?>,
            ganda_putra: <?= json_encode($gandaPutraTournament ?? null, JSON_UNESCAPED_UNICODE); ?>,
            ganda_putri: <?= json_encode($gandaPutriTournament ?? null, JSON_UNESCAPED_UNICODE); ?>
        };

        // Active Category: 'putra', 'putri', 'ganda_putra', 'ganda_putri'
        let currentCategory = 'putra';

        function getCategoryName(cat) {
            switch(cat) {
                case 'putri': return 'Putri';
                case 'ganda_putra': return 'Ganda Putra';
                case 'ganda_putri': return 'Ganda Putri';
                default: return 'Putra';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const catParam = urlParams.get('category');
            const hash = window.location.hash.replace('#', '');

            if (catParam && ['putra', 'putri', 'ganda_putra', 'ganda_putri'].includes(catParam)) {
                currentCategory = catParam;
            } else if (['putra', 'putri', 'ganda_putra', 'ganda_putri'].includes(hash)) {
                currentCategory = hash;
            } else {
                currentCategory = 'putra';
            }

            updateCategoryTabUI(currentCategory);
            renderBracket(allTournaments[currentCategory]);
            initEventHandlers();

            const autoPrintOrient = urlParams.get('print');
            if (autoPrintOrient && ['landscape', 'portrait'].includes(autoPrintOrient.toLowerCase())) {
                setTimeout(() => {
                    executePrint(autoPrintOrient.toLowerCase());
                }, 250);
            }
        });

        // SWITCH CATEGORY
        function switchCategory(cat) {
            currentCategory = cat;
            window.location.hash = cat;
            updateCategoryTabUI(cat);
            renderBracket(allTournaments[cat]);
        }

        function updateCategoryTabUI(cat) {
            const tabs = {
                putra: document.getElementById('tabBtnPutra'),
                putri: document.getElementById('tabBtnPutri'),
                ganda_putra: document.getElementById('tabBtnGandaPutra'),
                ganda_putri: document.getElementById('tabBtnGandaPutri')
            };

            Object.keys(tabs).forEach(k => {
                if (tabs[k]) {
                    if (k === cat) tabs[k].classList.add('active');
                    else tabs[k].classList.remove('active');
                }
            });

            // Update tab badge counts
            const updateCount = (key, elemId) => {
                const el = document.getElementById(elemId);
                if (el) {
                    const count = allTournaments[key] ? (allTournaments[key].participant_count || 0) : 0;
                    el.textContent = `${count} Peserta`;
                }
            };
            updateCount('putra', 'countBadgePutra');
            updateCount('putri', 'countBadgePutri');
            updateCount('ganda_putra', 'countBadgeGandaPutra');
            updateCount('ganda_putri', 'countBadgeGandaPutri');
        }

        // RENDER BRACKET FUNCTION
        function renderBracket(data) {
            const container = document.getElementById('bracketTree');
            const catLabel = getCategoryName(currentCategory);

            if (!data || !data.bracket_json || !data.bracket_json.rounds) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #64748b; width: 100%;">
                        <i class="fas fa-sitemap" style="font-size: 3rem; margin-bottom: 12px; color: #94a3b8;"></i>
                        <p style="font-weight: 700; font-size: 1.1rem;">Belum ada bagan turnamen ${catLabel} yang aktif.</p>
                        <p style="font-size: 0.9rem;">Klik tombol "Atur Peserta" di atas untuk membuat bagan turnamen tenis meja ${catLabel}.</p>
                    </div>
                `;
                return;
            }

            // Update Header Meta
            document.getElementById('tournamentTitle').textContent = data.title || `Turnamen Tenis Meja ${catLabel}`;
            const pill = document.getElementById('metaCategoryPill');

            if (pill) {
                if (currentCategory === 'putri') {
                    pill.className = 'meta-pill pill-putri';
                    pill.innerHTML = '<i class="fas fa-venus" style="color:#ec4899;"></i> <span>Kategori Putri</span>';
                } else if (currentCategory === 'ganda_putri') {
                    pill.className = 'meta-pill pill-putri';
                    pill.innerHTML = '<i class="fas fa-user-group" style="color:#ec4899;"></i> <span>Kategori Ganda Putri</span>';
                } else if (currentCategory === 'ganda_putra') {
                    pill.className = 'meta-pill pill-putra';
                    pill.innerHTML = '<i class="fas fa-user-group" style="color:#2563eb;"></i> <span>Kategori Ganda Putra</span>';
                } else {
                    pill.className = 'meta-pill pill-putra';
                    pill.innerHTML = '<i class="fas fa-mars" style="color:#2563eb;"></i> <span>Kategori Putra</span>';
                }
            }

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
            document.getElementById('podiumChampion').textContent = podium.champion ? `${podium.champion.name} (${podium.champion.balai || '-'})` : '- Menunggu Final -';
            document.getElementById('podiumRunnerUp').textContent = podium.runner_up ? `${podium.runner_up.name} (${podium.runner_up.balai || '-'})` : '- Menunggu Final -';

            // Render Round Columns
            const rounds = data.bracket_json.rounds;
            const totalRounds = parseInt(data.bracket_json.total_rounds || Object.keys(rounds).length);
            container.innerHTML = '<svg id="bracketSvgCanvas" class="bracket-svg-canvas"></svg>';

            if (parseInt(data.participant_count) === 10) {
                container.classList.add('is-10p');
            } else {
                container.classList.remove('is-10p');
            }

            for (let r = 1; r <= totalRounds; r++) {
                const roundData = rounds[r];
                if (!roundData) continue;

                const isFinalRound = (r === totalRounds);
                const col = document.createElement('div');
                col.className = 'bracket-round-column';
                col.id = `roundCol_${r}`;

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

                let currentGroup = null;
                roundData.matches.forEach((m, mIdx) => {
                    const unitWrap = document.createElement('div');
                    unitWrap.className = 'match-unit-wrap';
                    unitWrap.id = `wrap_${m.id}`;

                    if (parseInt(data.participant_count) === 10 && m.group && m.group !== currentGroup) {
                        currentGroup = m.group;
                        if (currentGroup === 'bawah' && r === 1 && mIdx > 0) {
                            unitWrap.classList.add('group-bawah-first-wrap');
                        }
                    }

                    const matchCard = createMatchCardElement(m, isFinalRound);
                    unitWrap.appendChild(matchCard);
                    matchesList.appendChild(unitWrap);
                });
            }

            // Align bracket cards across rounds & draw visible SVG connector lines
            requestAnimationFrame(() => {
                if (isFitScreenMode) {
                    applyScreenFit();
                } else {
                    alignBracketCardsAndDrawLines(data);
                }
            });
            setTimeout(() => {
                if (isFitScreenMode) {
                    applyScreenFit();
                } else {
                    alignBracketCardsAndDrawLines(data);
                }
            }, 60);
        }

        // FUNGSI HITUNG POSISI ELEMEN RELATIF TERHADAP ELEMEN ROOT (BEBAS DISTORSI TRANSFORM)
        function getCardLocalPos(el, root) {
            if (!el || !root) {
                return { left: 0, top: 0, right: 0, bottom: 0, width: 0, height: 0 };
            }

            const elRect = el.getBoundingClientRect();
            const rootRect = root.getBoundingClientRect();

            // Hitung rasio skala visual aktual terhadap ukuran layout CSS (offsetWidth / offsetHeight)
            const scaleX = (root.offsetWidth > 0 && rootRect.width > 0) ? (rootRect.width / root.offsetWidth) : 1;
            const scaleY = (root.offsetHeight > 0 && rootRect.height > 0) ? (rootRect.height / root.offsetHeight) : 1;

            const left = (elRect.left - rootRect.left) / scaleX;
            const top = (elRect.top - rootRect.top) / scaleY;
            const width = elRect.width / scaleX;
            const height = elRect.height / scaleY;

            return {
                left: left,
                top: top,
                right: left + width,
                bottom: top + height,
                width: width,
                height: height
            };
        }

        // SEJAJARKAN POSISI VERTIKAL KARTU ANTAR BABAK & GAMBAR GARIS ALIR SVG PRESISI 100%
        function alignBracketCardsAndDrawLines(data) {
            if (!data || !data.bracket_json || !data.bracket_json.rounds) {
                return;
            }

            const rounds = data.bracket_json.rounds;
            const totalRounds = parseInt(data.bracket_json.total_rounds || Object.keys(rounds).length);

            // Reset ukuran SVG terlebih dahulu agar tidak mempengaruhi kalkulasi tinggi kontainer
            const svg = document.getElementById('bracketSvgCanvas');
            if (svg) {
                svg.innerHTML = '';
                svg.setAttribute('width', '0');
                svg.setAttribute('height', '0');
                svg.style.width = '0px';
                svg.style.height = '0px';
            }

            // 1. Reset posisi inline agar Babak 1 menentukan tinggi kolom secara natural / flex
            for (let r = 1; r <= totalRounds; r++) {
                const listEl = document.getElementById('roundMatchesList_' + r);
                if (!listEl) continue;
                listEl.style.minHeight = '';
                listEl.style.height = '';
                const wraps = listEl.querySelectorAll('.match-unit-wrap');
                wraps.forEach(w => {
                    w.style.position = '';
                    w.style.top = '';
                    w.style.left = '';
                    w.style.right = '';
                    w.style.width = '';
                });
            }

            const list1 = document.getElementById('roundMatchesList_1');
            if (list1) {
                const r1Count = (rounds[1] && rounds[1].matches) ? rounds[1].matches.length : 0;
                list1.style.justifyContent = (r1Count <= 1) ? 'center' : 'space-between';
                void list1.offsetHeight;
            }

            const baseListHeight = list1 ? list1.offsetHeight : 0;

            // Kunci koordinat vertikal kartu Babak 1 secara absolut agar 100% sinkron dan tidak bergeser saat cetak
            if (list1 && baseListHeight > 0) {
                const r1Wraps = Array.from(list1.querySelectorAll('.match-unit-wrap'));
                const r1Tops = r1Wraps.map(w => Math.round(getCardLocalPos(w, list1).top));
                list1.style.position = 'relative';
                list1.style.height = baseListHeight + 'px';
                list1.style.minHeight = baseListHeight + 'px';
                r1Wraps.forEach((w, idx) => {
                    w.style.position = 'absolute';
                    w.style.left = '0';
                    w.style.right = '0';
                    w.style.width = '100%';
                    w.style.top = r1Tops[idx] + 'px';
                });
                void list1.offsetHeight;
            }

            // 2. Untuk Babak 2 s/d Final, sejajarkan setiap kartu tepat di titik tengah (atau slot) kartu pengumpannya
            for (let r = 2; r <= totalRounds; r++) {
                const listEl = document.getElementById('roundMatchesList_' + r);
                if (!listEl) continue;

                listEl.style.position = 'relative';
                if (baseListHeight > 0) {
                    listEl.style.height = baseListHeight + 'px';
                    listEl.style.minHeight = baseListHeight + 'px';
                }
                void listEl.offsetHeight;

                const prevMatches = (rounds[r - 1] && rounds[r - 1].matches) ? rounds[r - 1].matches : [];
                const currMatches = (rounds[r] && rounds[r].matches) ? rounds[r].matches : [];

                const placements = [];
                currMatches.forEach((m) => {
                    const wrapEl = document.getElementById('wrap_' + m.id);
                    const cardEl = document.getElementById('card_' + m.id);
                    if (!wrapEl || !cardEl) return;

                    const wrapPos = getCardLocalPos(wrapEl, listEl);
                    const cardPos = getCardLocalPos(cardEl, listEl);
                    const slots = cardEl.querySelectorAll('.player-slot');

                    const slot0Pos = (slots && slots[0]) ? getCardLocalPos(slots[0], listEl) : null;
                    const slot1Pos = (slots && slots[1]) ? getCardLocalPos(slots[1], listEl) : null;

                    const slot0Offset = slot0Pos
                        ? ((slot0Pos.top + slot0Pos.height / 2) - wrapPos.top)
                        : ((cardPos.top - wrapPos.top) + cardPos.height * 0.35);
                    const slot1Offset = slot1Pos
                        ? ((slot1Pos.top + slot1Pos.height / 2) - wrapPos.top)
                        : ((cardPos.top - wrapPos.top) + cardPos.height * 0.70);
                    const slotsMidOffset = (slot0Offset + slot1Offset) / 2;

                    const feeders = prevMatches.filter(pm => pm.next_match_id === m.id);
                    let idealTop = wrapPos.top;

                    if (feeders.length === 1) {
                        const f = feeders[0];
                        const fCard = document.getElementById('card_' + f.id);
                        if (fCard) {
                            const fPos = getCardLocalPos(fCard, listEl);
                            const fCenterY = fPos.top + (fPos.height / 2);
                            const targetSlotOffset = (f.next_match_slot === 1) ? slot0Offset : slot1Offset;
                            idealTop = fCenterY - targetSlotOffset;
                        }
                    } else if (feeders.length >= 2) {
                        const fCenters = [];
                        feeders.forEach(f => {
                            const fCard = document.getElementById('card_' + f.id);
                            if (fCard) {
                                const fPos = getCardLocalPos(fCard, listEl);
                                fCenters.push(fPos.top + (fPos.height / 2));
                            }
                        });
                        if (fCenters.length > 0) {
                            const avgCenterY = fCenters.reduce((a, b) => a + b, 0) / fCenters.length;
                            idealTop = avgCenterY - slotsMidOffset;
                        }
                    }

                    placements.push({
                        wrapEl: wrapEl,
                        height: wrapPos.height,
                        idealTop: idealTop
                    });
                });

                // Cegah tumpang tindih antar kartu pada babak yang sama
                const minGap = 8;
                let cursorBottom = 0;
                placements.forEach((p, idx) => {
                    let top = Math.max(0, p.idealTop);
                    if (idx > 0 && top < cursorBottom + minGap) {
                        top = cursorBottom + minGap;
                    }
                    p.finalTop = top;
                    cursorBottom = top + p.height;
                });

                placements.forEach(p => {
                    p.wrapEl.style.position = 'absolute';
                    p.wrapEl.style.left = '0';
                    p.wrapEl.style.right = '0';
                    p.wrapEl.style.width = '100%';
                    p.wrapEl.style.top = Math.round(p.finalTop) + 'px';
                });

                void listEl.offsetHeight;
            }

            drawBracketLines(data);
        }

        // DRAW VISIBLE BRACKET CONNECTOR LINES VIA SVG (PRESISI 100% BEBAS DARI DISTORSI ZOOM)
        function drawBracketLines(data) {
            const tree = document.getElementById('bracketTree');
            if (!tree) return;

            let svg = document.getElementById('bracketSvgCanvas');
            if (!svg) {
                svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.id = 'bracketSvgCanvas';
                svg.className = 'bracket-svg-canvas';
                tree.insertBefore(svg, tree.firstChild);
            }

            svg.innerHTML = '';

            if (!data || !data.bracket_json || !data.bracket_json.rounds) {
                return;
            }

            const scrollW = Math.max(tree.scrollWidth, tree.offsetWidth);
            const scrollH = Math.max(tree.scrollHeight, tree.offsetHeight);

            svg.setAttribute('width', scrollW);
            svg.setAttribute('height', scrollH);
            svg.style.width = scrollW + 'px';
            svg.style.height = scrollH + 'px';

            const rounds = data.bracket_json.rounds;
            const totalRounds = parseInt(data.bracket_json.total_rounds || Object.keys(rounds).length);

            const normalElements = [];
            const winnerElements = [];

            for (let r = 1; r < totalRounds; r++) {
                const roundData = rounds[r];
                if (!roundData || !roundData.matches) continue;

                roundData.matches.forEach((m) => {
                    if (!m.next_match_id) return;

                    const cardA = document.getElementById('card_' + m.id);
                    const cardB = document.getElementById('card_' + m.next_match_id);

                    if (!cardA || !cardB) return;

                    const posA = getCardLocalPos(cardA, tree);
                    const posB = getCardLocalPos(cardB, tree);

                    const x1 = Math.round(posA.right);
                    let y1 = Math.round(posA.top + (posA.height / 2));

                    const x2 = Math.round(posB.left);

                    // Pinpoint target slot in card B
                    const slotsB = cardB.querySelectorAll('.player-slot');
                    const targetSlotIdx = (m.next_match_slot === 1) ? 0 : 1;
                    let y2;
                    if (slotsB && slotsB[targetSlotIdx]) {
                        const slotPos = getCardLocalPos(slotsB[targetSlotIdx], tree);
                        y2 = Math.round(slotPos.top + (slotPos.height / 2));
                    } else {
                        const slotRatio = (m.next_match_slot === 1) ? 0.35 : 0.70;
                        y2 = Math.round(posB.top + (posB.height * slotRatio));
                    }

                    // Jika selisih vertikal sangat kecil (<= 2px), ratakan agar menjadi garis lurus sempurna
                    if (Math.abs(y1 - y2) <= 2) {
                        y1 = y2;
                    }

                    const midX = Math.round(x1 + (x2 - x1) * 0.5);

                    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    const d = (y1 === y2)
                        ? `M ${x1} ${y2} H ${x2}`
                        : `M ${x1} ${y1} H ${midX} V ${y2} H ${x2}`;
                    path.setAttribute('d', d);

                    const isWinner = !!(m.winner && m.status === 'completed');
                    path.setAttribute('class', 'bracket-line' + (isWinner ? ' winner' : ''));

                    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    circle.setAttribute('cx', x2);
                    circle.setAttribute('cy', y2);
                    circle.setAttribute('r', isWinner ? '4.5' : '3.5');
                    circle.setAttribute('class', 'bracket-dot' + (isWinner ? ' winner' : ''));

                    if (isWinner) {
                        winnerElements.push(path, circle);
                    } else {
                        normalElements.push(path, circle);
                    }
                });
            }

            // Render garis biasa terlebih dahulu, lalu garis pemenang di atasnya agar tidak tertutup
            normalElements.forEach(el => svg.appendChild(el));
            winnerElements.forEach(el => svg.appendChild(el));
        }

        // FIT LAYAR / PAS LAYAR (OTOMATIS TANPA PERLU MENGGESER-GESER LAYAR)
        let isFitScreenMode = true;
        let isExecutingCustomPrint = false;
        let currentPrintOrientation = 'landscape';

        function toggleFitScreen() {
            isFitScreenMode = !isFitScreenMode;
            const btn = document.getElementById('btnFitScreen');
            if (btn) {
                if (isFitScreenMode) {
                    btn.classList.add('active');
                    btn.innerHTML = '<i class="fas fa-compress"></i> Pas Layar';
                } else {
                    btn.classList.remove('active');
                    btn.innerHTML = '<i class="fas fa-expand"></i> Skala Penuh (Geser)';
                }
            }
            applyScreenFit();
        }

        function applyScreenFit() {
            if (isExecutingCustomPrint || document.body.classList.contains('print-preview-mode')) {
                return;
            }

            const tree = document.getElementById('bracketTree');
            const scrollContainer = document.querySelector('.bracket-scroll-container');
            if (!tree || !scrollContainer) return;

            if (!isFitScreenMode) {
                tree.style.transform = '';
                tree.style.zoom = '';
                scrollContainer.style.overflowX = 'auto';
                scrollContainer.style.overflowY = 'visible';
                scrollContainer.style.height = '';
                if (allTournaments[currentCategory]) {
                    alignBracketCardsAndDrawLines(allTournaments[currentCategory]);
                }
                return;
            }

            // Reset sejenak untuk kalkulasi dimensi aktual kontainer & sejajarkan kartu
            tree.style.transform = '';
            tree.style.zoom = '';
            scrollContainer.style.height = '';

            if (allTournaments[currentCategory]) {
                alignBracketCardsAndDrawLines(allTournaments[currentCategory]);
            }

            const containerW = scrollContainer.clientWidth - 20;
            const contentW = tree.scrollWidth;

            if (contentW > containerW && containerW > 320) {
                const scale = containerW / contentW;
                tree.style.transformOrigin = 'top left';
                tree.style.transform = `scale(${scale})`;
                scrollContainer.style.overflowX = 'hidden';
                scrollContainer.style.overflowY = 'hidden';
                scrollContainer.style.height = (tree.scrollHeight * scale + 24) + 'px';
            } else {
                tree.style.transform = '';
                scrollContainer.style.overflowX = 'hidden';
                scrollContainer.style.overflowY = 'hidden';
                scrollContainer.style.height = '';
            }
        }

        window.addEventListener('resize', () => {
            if (isExecutingCustomPrint || document.body.classList.contains('print-preview-mode')) {
                return;
            }
            if (isFitScreenMode) {
                applyScreenFit();
            } else if (allTournaments[currentCategory]) {
                alignBracketCardsAndDrawLines(allTournaments[currentCategory]);
            }
        });

        // CETAK & EKSPOR PDF MODAL CONTROLS (DIGABUNGKAN - PILIHAN LANDSCAPE & PORTRAIT 1 HALAMAN PENUH)
        let originalDocTitle = '';

        function getCategoryPrintTitle(cat) {
            return 'Turnamen Tenis Meja ' + getCategoryName(cat);
        }

        function openPrintModal() {
            document.getElementById('printModal').classList.add('active');
        }

        function closePrintModal() {
            document.getElementById('printModal').classList.remove('active');
        }

        function printBracket() {
            openPrintModal();
        }

        function preparePrintStage(orientation) {
            const orient = (orientation === 'portrait') ? 'portrait' : 'landscape';
            currentPrintOrientation = orient;
            isExecutingCustomPrint = true;

            // Simpan title asli dokumen & ubah title persis sesuai format penamaan file yang diminta
            if (!originalDocTitle) {
                originalDocTitle = document.title;
            }
            document.title = getCategoryPrintTitle(currentCategory);

            // Ukuran area cetak bersih 1 halaman A4 (margin 4mm) pada 96 DPI:
            // Landscape: 289mm x 202mm -> 1080px x 754px
            // Portrait : 202mm x 289mm -> 754px x 1080px
            const PAGE_W = (orient === 'portrait') ? 754 : 1080;
            const PAGE_H = (orient === 'portrait') ? 1080 : 754;
            const pageAspect = PAGE_W / PAGE_H;

            let styleTag = document.getElementById('printOrientationStyle');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'printOrientationStyle';
                document.head.appendChild(styleTag);
            }

            styleTag.textContent = `
                @page {
                    size: A4 ${orient};
                    margin: 4mm;
                }
                @media print {
                    html, body {
                        width: ${PAGE_W}px !important;
                        height: ${PAGE_H}px !important;
                        max-height: ${PAGE_H}px !important;
                        overflow: hidden !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #ffffff !important;
                    }
                }
            `;

            document.body.classList.add('print-preview-mode');
            if (orient === 'portrait') {
                document.body.classList.add('print-portrait-mode');
                document.body.classList.remove('print-landscape-mode');
            } else {
                document.body.classList.add('print-landscape-mode');
                document.body.classList.remove('print-portrait-mode');
            }

            const stageWrapper = document.getElementById('printStageWrapper');
            const stageInner = document.getElementById('printStageInner');
            const tree = document.getElementById('bracketTree');
            const scrollContainer = document.querySelector('.bracket-scroll-container');

            if (!stageWrapper || !stageInner || !tree) {
                return;
            }

            // Reset gaya inline & kunci position:absolute pada stageInner SEBELUM kalkulasi agar tinggi flex 100% akurat
            stageWrapper.removeAttribute('style');
            stageInner.removeAttribute('style');
            tree.style.transform = 'none';
            tree.style.zoom = '1';
            if (scrollContainer) {
                scrollContainer.style.height = '';
                scrollContainer.style.overflow = 'visible';
            }

            stageWrapper.style.width = PAGE_W + 'px';
            stageWrapper.style.height = PAGE_H + 'px';
            stageWrapper.style.maxHeight = PAGE_H + 'px';
            stageWrapper.style.overflow = 'hidden';
            stageWrapper.style.position = 'relative';
            stageWrapper.style.margin = '0 auto';

            stageInner.style.position = 'absolute';
            stageInner.style.top = '0';
            stageInner.style.left = '0';
            stageInner.style.transformOrigin = 'top left';
            stageInner.style.transform = 'none';

            // Reset ukuran SVG agar tidak mempengaruhi scrollHeight
            const svg = document.getElementById('bracketSvgCanvas');
            if (svg) {
                svg.innerHTML = '';
                svg.setAttribute('width', '0');
                svg.setAttribute('height', '0');
                svg.style.width = '0px';
                svg.style.height = '0px';
            }

            const currentData = allTournaments[currentCategory];
            const rounds = (currentData && currentData.bracket_json && currentData.bracket_json.rounds) ? currentData.bracket_json.rounds : {};
            const totalRounds = parseInt((currentData && currentData.bracket_json && currentData.bracket_json.total_rounds) || Object.keys(rounds).length || 3);
            const pCount = (currentData && currentData.participant_count) ? parseInt(currentData.participant_count) : 5;

            // Reset posisi kartu agar tinggi natural Babak 1 dapat diukur akurat
            for (let r = 1; r <= totalRounds; r++) {
                const listEl = document.getElementById('roundMatchesList_' + r);
                if (!listEl) continue;
                listEl.style.minHeight = '';
                listEl.style.height = '';
                const wraps = listEl.querySelectorAll('.match-unit-wrap');
                wraps.forEach(w => {
                    w.style.position = '';
                    w.style.top = '';
                    w.style.left = '';
                    w.style.right = '';
                    w.style.width = '';
                });
            }

            // Tentukan lebar desain minimum (W_min) agar kartu memiliki proporsi ideal seperti di web
            let W_min;
            if (orient === 'portrait') {
                if (totalRounds <= 2) W_min = 780;
                else if (totalRounds === 3) W_min = 900;
                else if (totalRounds === 4) W_min = (pCount === 10) ? 1040 : 1080;
                else W_min = totalRounds * 265;
            } else {
                if (totalRounds <= 2) W_min = 1080;
                else if (totalRounds === 3) W_min = 1220;
                else if (totalRounds === 4) W_min = (pCount === 10) ? 1420 : 1440;
                else W_min = totalRounds * 330;
            }

            // Ukur tinggi natural konten pada lebar W_min
            stageInner.style.width = W_min + 'px';
            stageInner.style.height = 'auto';
            void stageInner.offsetHeight;

            let H_min = stageInner.scrollHeight + 4;
            let W_canvas = Math.max(W_min, Math.ceil(H_min * pageAspect));

            // Jika W_canvas melebar dari W_min, ukur ulang tinggi natural pada lebar W_canvas (karena teks kartu bisa menjadi 1 baris)
            if (W_canvas > W_min) {
                stageInner.style.width = W_canvas + 'px';
                stageInner.style.height = 'auto';
                void stageInner.offsetHeight;
                H_min = stageInner.scrollHeight + 4;
                W_canvas = Math.max(W_min, Math.ceil(H_min * pageAspect));
            }

            let H_canvas = Math.max(H_min, Math.ceil(W_canvas / pageAspect));

            stageInner.style.width = W_canvas + 'px';
            stageInner.style.height = H_canvas + 'px';
            void stageInner.offsetHeight;

            if (stageInner.scrollHeight > H_canvas) {
                H_canvas = stageInner.scrollHeight + 4;
                W_canvas = Math.ceil(H_canvas * pageAspect);
                stageInner.style.width = W_canvas + 'px';
                stageInner.style.height = H_canvas + 'px';
                void stageInner.offsetHeight;
            }

            // Sejajarkan kartu setiap babak (termasuk mengunci kartu Babak 1 secara absolut) & gambar garis SVG pada dimensi final (W_canvas x H_canvas)
            if (currentData) {
                alignBracketCardsAndDrawLines(currentData);
            }

            // Skala penuh (100% lebar & tinggi kertas) tepat dalam 1 halaman A4 tanpa mengubah posisi layout lagi
            const scaleX = PAGE_W / W_canvas;
            const scaleY = PAGE_H / H_canvas;
            stageInner.style.transform = `scale(${scaleX}, ${scaleY})`;
        }

        function restoreAfterPrint() {
            isExecutingCustomPrint = false;
            document.body.classList.remove('print-preview-mode');
            document.body.classList.remove('print-portrait-mode');
            document.body.classList.remove('print-landscape-mode');

            if (originalDocTitle) {
                document.title = originalDocTitle;
            }

            const styleTag = document.getElementById('printOrientationStyle');
            if (styleTag) {
                styleTag.textContent = '';
            }

            const stageWrapper = document.getElementById('printStageWrapper');
            const stageInner = document.getElementById('printStageInner');
            if (stageWrapper) stageWrapper.removeAttribute('style');
            if (stageInner) stageInner.removeAttribute('style');

            if (isFitScreenMode) {
                applyScreenFit();
            } else if (allTournaments[currentCategory]) {
                alignBracketCardsAndDrawLines(allTournaments[currentCategory]);
            }
        }

        function executePrint(orientation) {
            closePrintModal();
            preparePrintStage(orientation);

            requestAnimationFrame(() => {
                setTimeout(() => {
                    window.print();
                }, 100);
            });
        }

        window.addEventListener('beforeprint', () => {
            if (!isExecutingCustomPrint) {
                preparePrintStage(currentPrintOrientation || 'landscape');
            }
        });

        window.addEventListener('afterprint', () => {
            restoreAfterPrint();
        });

        // CREATE SINGLE MATCH CARD
        function createMatchCardElement(match, isFinal) {
            const hasWinner = !!(match.winner && match.status === 'completed');
            const card = document.createElement('div');

            let groupBadge = '';
            let groupClass = '';
            if (match.group === 'atas') {
                groupClass = 'group-atas';
                groupBadge = '<span class="group-mini-pill group-atas-pill">Bagan Atas</span>';
            } else if (match.group === 'bawah') {
                groupClass = 'group-bawah';
                groupBadge = '<span class="group-mini-pill group-bawah-pill">Bagan Bawah</span>';
            } else if (match.group === 'grand_final') {
                groupClass = 'group-grand-final';
                groupBadge = '<span class="group-mini-pill group-gf-pill">Grand Final</span>';
            }

            card.className = `match-card ${isFinal ? 'is-final' : ''} ${hasWinner ? 'has-winner' : ''} ${match.is_bye ? 'is-bye-card' : ''} ${groupClass}`;
            card.id = `card_${match.id}`;

            const p1 = match.player1;
            const p2 = match.player2;

            const isP1Winner = match.winner && p1 && match.winner.name === p1.name;
            const isP2Winner = match.winner && p2 && match.winner.name === p2.name;

            const isByeMatch = match.is_bye || (p2 && p2.is_bye);

            card.innerHTML = `
                <div class="match-card-header">
                    <span style="display:flex; align-items:center; gap:6px;">
                        ${match.is_bye ? '<i class="fas fa-bolt text-warning mr-1"></i> Menang Bye' : '<i class="fas fa-gamepad mr-1"></i> Match #' + match.match_number}
                        ${groupBadge}
                    </span>
                    <span>
                        ${match.is_bye ? '<span style="color: #10b981; font-weight: 800;"><i class="fas fa-check-circle"></i> ' + (match.status === 'completed' ? 'MENANG BYE' : 'MENUNGGU') + '</span>' : (match.status === 'completed' ? '<span style="color: #10b981; font-weight: 800;"><i class="fas fa-check"></i> SELESAI</span>' : '<span style="color: #f59e0b;"><i class="fas fa-hourglass-half"></i> PENDING</span>')}
                    </span>
                </div>

                <!-- PLAYER 1 -->
                <div class="player-slot ${isP1Winner ? 'is-winner' : ''} ${!p1 ? 'is-empty' : ''}">
                    <div class="player-info">
                        <div class="player-seed">${p1 ? (p1.seed || '•') : '-'}</div>
                        <div class="player-name-wrap">
                            <div class="player-name">${p1 ? escapeHtml(p1.name) : '<em style="color:#94a3b8">Menunggu Pemenang Match #1</em>'}</div>
                            ${p1 && p1.balai ? `<span class="balai-badge-pill"><i class="fas fa-building"></i> ${escapeHtml(p1.balai)}</span>` : ''}
                        </div>
                    </div>
                    <div class="player-score-ctrl">
                        ${isByeMatch ? `
                            <span class="badge-bye-pill" style="background:#ecfdf5; color:#065f46; font-size:0.75rem;"><i class="fas fa-check"></i> Lolos</span>
                        ` : (IS_LOGGED_IN ? `
                            <input type="number" min="0" max="99" class="score-input" id="score1_${match.id}" value="${match.score1 !== null ? match.score1 : ''}" placeholder="0" ${!p1 ? 'disabled' : ''}>
                            <button type="button" class="btn-pick-winner" title="Tandai sebagai Pemenang" onclick="setMatchWinner('${match.id}', '${p1 ? escapeHtml(p1.name) : ''}')" ${!p1 ? 'disabled' : ''}>
                                <i class="fas fa-check"></i>
                            </button>
                            <span class="score-print-display ${isP1Winner ? 'is-winner' : ''}">${match.score1 !== null ? match.score1 : '0'}</span>
                            ${isP1Winner ? '<span class="winner-print-check"><i class="fas fa-check"></i></span>' : ''}
                        ` : `
                            <span class="score-display-static ${isP1Winner ? 'is-winner' : ''}">${match.score1 !== null ? match.score1 : '0'}</span>
                            ${isP1Winner ? '<span class="winner-check-badge" title="Pemenang Pertandingan"><i class="fas fa-check"></i></span>' : ''}
                        `)}
                    </div>
                </div>

                <!-- PLAYER 2 -->
                <div class="player-slot ${isP2Winner ? 'is-winner' : ''} ${!p2 ? 'is-empty' : ''} ${p2 && p2.is_bye ? 'is-bye' : ''}">
                    <div class="player-info">
                        <div class="player-seed">${p2 ? (p2.seed || '•') : '-'}</div>
                        <div class="player-name-wrap">
                            <div class="player-name">${p2 && p2.is_bye ? '<span style="color:#64748b; font-style:italic;">(BYE) - Lolos Otomatis</span>' : (p2 ? escapeHtml(p2.name) : '<em style="color:#94a3b8">Menunggu Pemenang</em>')}</div>
                            ${p2 && p2.balai && !p2.is_bye ? `<span class="balai-badge-pill"><i class="fas fa-building"></i> ${escapeHtml(p2.balai)}</span>` : ''}
                        </div>
                    </div>
                    <div class="player-score-ctrl">
                        ${isByeMatch ? `
                            <span class="badge-bye-pill" style="opacity:0.6; font-size:0.75rem;">BYE</span>
                        ` : (IS_LOGGED_IN ? `
                            <input type="number" min="0" max="99" class="score-input" id="score2_${match.id}" value="${match.score2 !== null ? match.score2 : ''}" placeholder="0" ${!p2 ? 'disabled' : ''}>
                            <button type="button" class="btn-pick-winner" title="Tandai sebagai Pemenang" onclick="setMatchWinner('${match.id}', '${p2 ? escapeHtml(p2.name) : ''}')" ${!p2 ? 'disabled' : ''}>
                                <i class="fas fa-check"></i>
                            </button>
                            <span class="score-print-display ${isP2Winner ? 'is-winner' : ''}">${match.score2 !== null ? match.score2 : '0'}</span>
                            ${isP2Winner ? '<span class="winner-print-check"><i class="fas fa-check"></i></span>' : ''}
                        ` : `
                            <span class="score-display-static ${isP2Winner ? 'is-winner' : ''}">${match.score2 !== null ? match.score2 : '0'}</span>
                            ${isP2Winner ? '<span class="winner-check-badge" title="Pemenang Pertandingan"><i class="fas fa-check"></i></span>' : ''}
                        `)}
                    </div>
                </div>

                ${match.note ? `
                <div class="match-note-banner">
                    <i class="fas fa-info-circle text-primary"></i> ${escapeHtml(match.note)}
                </div>
                ` : ''}

                ${IS_LOGGED_IN && !isByeMatch && p1 && p2 ? `
                <div class="match-actions-footer">
                    <button type="button" class="btn-save-match" onclick="saveMatchScores('${match.id}')">
                        <i class="fas fa-floppy-disk mr-1"></i> Simpan Skor
                    </button>
                </div>
                ` : ''}
            `;

            return card;
        }

        // SAVE MATCH SCORES (HANYA KETIKA LOGIN)
        async function saveMatchScores(matchId) {
            if (!IS_LOGGED_IN) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perlu Masuk (Login)',
                    text: 'Mengubah skor pertandingan hanya dapat dilakukan setelah masuk ke akun resmi.',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-right-to-bracket mr-1"></i> Masuk Sekarang',
                    confirmButtonColor: '#0b3c74',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.href = LOGIN_URL;
                    }
                });
                return;
            }

            const score1Input = document.getElementById(`score1_${matchId}`);
            const score2Input = document.getElementById(`score2_${matchId}`);

            const s1 = score1Input ? score1Input.value.trim() : '';
            const s2 = score2Input ? score2Input.value.trim() : '';

            const formData = new FormData();
            formData.append('category', currentCategory);
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
                    allTournaments[currentCategory] = res.tournament;
                    renderBracket(allTournaments[currentCategory]);
                    showToast('success', res.message || 'Skor pertandingan berhasil disimpan.');
                } else {
                    showToast('error', res.message || 'Gagal menyimpan skor pertandingan.');
                }
            } catch (err) {
                showToast('error', 'Terjadi kesalahan sistem saat menyimpan skor.');
            }
        }

        // SET MATCH WINNER DIRECTLY (HANYA KETIKA LOGIN)
        async function setMatchWinner(matchId, winnerName) {
            if (!IS_LOGGED_IN) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perlu Masuk (Login)',
                    text: 'Menentukan pemenang pertandingan hanya dapat dilakukan setelah masuk ke akun resmi.',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-right-to-bracket mr-1"></i> Masuk Sekarang',
                    confirmButtonColor: '#0b3c74',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.href = LOGIN_URL;
                    }
                });
                return;
            }

            if (!winnerName) return;

            const score1Input = document.getElementById(`score1_${matchId}`);
            const score2Input = document.getElementById(`score2_${matchId}`);

            const s1 = score1Input ? score1Input.value.trim() : '';
            const s2 = score2Input ? score2Input.value.trim() : '';

            const formData = new FormData();
            formData.append('category', currentCategory);
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
                    allTournaments[currentCategory] = res.tournament;
                    renderBracket(allTournaments[currentCategory]);
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
            if (btnAcak) {
                btnAcak.addEventListener('click', function () {
                const catLabel = (currentCategory === 'putri') ? 'Putri' : 'Putra';

                Swal.fire({
                    title: `<span style="color:#002244; font-family: Montserrat, sans-serif; font-size:1.25rem;"><i class="fas fa-shield-halved text-danger mr-2"></i> Verifikasi Superadministrator</span>`,
                    html: `
                        <p style="font-size:0.88rem; color:#64748b; margin-bottom:14px;">
                            Fitur pengacakan posisi bagan turnamen <strong>${catLabel}</strong> hanya dapat dilakukan oleh <strong>Superadministrator</strong>.<br>Silakan masukkan password akun superadministrator:
                        </p>
                        <div style="position:relative; width:100%; max-width:320px; margin:0 auto;">
                            <input type="password" id="swal_admin_pwd" class="swal2-input" placeholder="Password Superadministrator" style="width:100%; margin:0; padding-right:40px;">
                            <i class="fas fa-eye" id="togglePwdEye" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#94a3b8;"></i>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: `<i class="fas fa-shuffle mr-1"></i> Verifikasi & Acak Posisi (${catLabel})`,
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
                        formData.append('category', currentCategory);

                        try {
                            const response = await fetch(API_ACAK, {
                                method: 'POST',
                                body: formData
                            });
                            const res = await response.json();

                            if (res.status === 'success') {
                                allTournaments[currentCategory] = res.tournament;
                                renderBracket(allTournaments[currentCategory]);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Diacak!',
                                    text: res.message || `Posisi peserta turnamen tenis meja ${catLabel} berhasil diacak.`,
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
            }

            // RESET SKOR
            const btnReset = document.getElementById('btnResetSkor');
            if (btnReset) {
                btnReset.addEventListener('click', function () {
                const catLabel = getCategoryName(currentCategory);

                Swal.fire({
                    title: `Reset Skor Turnamen ${catLabel}?`,
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
                            const formData = new FormData();
                            formData.append('category', currentCategory);

                            const response = await fetch(API_RESET, {
                                method: 'POST',
                                body: formData
                            });
                            const res = await response.json();

                            if (res.status === 'success') {
                                allTournaments[currentCategory] = res.tournament;
                                renderBracket(allTournaments[currentCategory]);
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
            }

            // MODAL SETUP OPEN / CLOSE
            const modal = document.getElementById('setupModal');
            const btnOpenSetup = document.getElementById('btnOpenSetup');
            const btnCloseSetup = document.getElementById('btnCloseSetup');
            const btnCancelSetup = document.getElementById('btnCancelSetup');

            if (btnOpenSetup && modal) {
                btnOpenSetup.addEventListener('click', () => {
                    document.getElementById('formCategory').value = currentCategory;
                    populateParticipantInputs(currentCategory);
                    modal.classList.add('active');
                });
            }

            if (btnCloseSetup && modal) {
                btnCloseSetup.addEventListener('click', () => modal.classList.remove('active'));
            }
            if (btnCancelSetup && modal) {
                btnCancelSetup.addEventListener('click', () => modal.classList.remove('active'));
            }

            // PARTICIPANT COUNT BUTTONS
            const countButtons = document.querySelectorAll('.btn-count-opt');
            countButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    countButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const c = parseInt(this.dataset.count);
                    adjustParticipantRowsCount(c);
                });
            });

            // ADD PARTICIPANT ROW BUTTON
            document.getElementById('btnAddParticipantRow').addEventListener('click', function () {
                const container = document.getElementById('participantInputs');
                const nextNum = container.children.length + 1;
                addParticipantRow(nextNum, '', '');
                container.scrollTop = container.scrollHeight;
            });

            // SUBMIT SETUP FORM
            const btnSubmitSetup = document.getElementById('btnSubmitSetup');
            if (btnSubmitSetup) {
                btnSubmitSetup.addEventListener('click', async function () {
                    if (!IS_LOGGED_IN) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perlu Masuk (Login)',
                            text: 'Mengatur peserta turnamen hanya dapat dilakukan setelah masuk ke akun resmi.',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-right-to-bracket mr-1"></i> Masuk Sekarang',
                            confirmButtonColor: '#0b3c74',
                            cancelButtonText: 'Batal'
                        }).then((res) => {
                            if (res.isConfirmed) {
                                window.location.href = LOGIN_URL;
                            }
                        });
                        return;
                    }

                    const category = document.getElementById('formCategory').value;
                    const title = document.getElementById('formTitle').value.trim();

                const rows = document.querySelectorAll('.participant-input-row');
                const participantList = [];

                rows.forEach(r => {
                    const nameInp = r.querySelector('.participant-name-field');
                    const balaiInp = r.querySelector('.participant-balai-field');
                    const nameVal = nameInp ? nameInp.value.trim() : '';
                    const balaiVal = balaiInp ? balaiInp.value.trim() : '';

                    if (nameVal || balaiVal) {
                        participantList.push({
                            name: nameVal || balaiVal,
                            balai: balaiVal || nameVal
                        });
                    }
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
                        allTournaments[category] = res.tournament;
                        currentCategory = category;
                        switchCategory(category);
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
        }

        // ON MODAL CATEGORY CHANGE
        function onModalCategoryChange(selectedCat) {
            populateParticipantInputs(selectedCat);
        }

        // POPULATE PARTICIPANTS IN MODAL
        function populateParticipantInputs(cat) {
            const data = allTournaments[cat];
            const container = document.getElementById('participantInputs');
            container.innerHTML = '';

            const catLabel = getCategoryName(cat);
            document.getElementById('formTitle').value = data ? (data.title || `Turnamen Tenis Meja ${catLabel}`) : `Turnamen Tenis Meja ${catLabel}`;

            let list = (data && data.participants_json && Array.isArray(data.participants_json)) ? data.participants_json : [];

            if (list.length === 0) {
                const prefix = (cat === 'ganda_putra' || cat === 'ganda_putri') ? 'Pasangan' : 'Peserta';
                list = [
                    { name: `${prefix} 1 ${catLabel}`, balai: 'BWS Sumatera III' },
                    { name: `${prefix} 2 ${catLabel}`, balai: 'BPJN Riau' },
                    { name: `${prefix} 3 ${catLabel}`, balai: 'BPPW Riau' },
                    { name: `${prefix} 4 ${catLabel}`, balai: 'BP2JK Riau' },
                    { name: `${prefix} 5 ${catLabel}`, balai: 'Satker PPS Riau' },
                ];
            }

            list.forEach((item, idx) => {
                addParticipantRow(idx + 1, item.name || '', item.balai || '');
            });

            // Sync quick count buttons active state
            const countButtons = document.querySelectorAll('.btn-count-opt');
            countButtons.forEach(b => {
                if (parseInt(b.dataset.count) === list.length) {
                    b.classList.add('active');
                } else {
                    b.classList.remove('active');
                }
            });
        }

        // ADD SINGLE ROW TO MODAL INPUTS
        function addParticipantRow(num, nameVal = '', balaiVal = '') {
            const container = document.getElementById('participantInputs');
            const row = document.createElement('div');
            row.className = 'participant-input-row';
            row.innerHTML = `
                <div class="participant-num">#${num}</div>
                <div>
                    <input type="text" class="form-control participant-name-field" placeholder="Nama Peserta / Pasangan #${num}" value="${escapeHtml(nameVal)}">
                </div>
                <div>
                    <input type="text" class="form-control participant-balai-field" placeholder="Asal Balai #${num}" value="${escapeHtml(balaiVal)}">
                </div>
                <div>
                    <button type="button" class="btn-del-participant" title="Hapus Baris" onclick="removeParticipantRow(this)">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
        }

        // REMOVE PARTICIPANT ROW
        function removeParticipantRow(btn) {
            const container = document.getElementById('participantInputs');
            if (container.children.length <= 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Minimal 2 Peserta',
                    text: 'Turnamen sistem gugur memerlukan minimal 2 peserta.',
                    confirmButtonColor: '#0b3c74'
                });
                return;
            }
            const row = btn.closest('.participant-input-row');
            row.remove();
            renumberParticipantRows();
        }

        function renumberParticipantRows() {
            const container = document.getElementById('participantInputs');
            Array.from(container.children).forEach((row, idx) => {
                const numEl = row.querySelector('.participant-num');
                if (numEl) numEl.textContent = `#${idx + 1}`;
            });
        }

        // ADJUST PARTICIPANT ROWS COUNT ACCORDING TO QUICK BUTTONS
        function adjustParticipantRowsCount(targetCount) {
            const container = document.getElementById('participantInputs');
            const currentRows = container.children.length;
            const currentCat = document.getElementById('formCategory').value || currentCategory;
            const catLabel = getCategoryName(currentCat);
            const prefix = (currentCat === 'ganda_putra' || currentCat === 'ganda_putri') ? 'Pasangan' : 'Peserta';
            const defaultBalaiList = ['BWS Sumatera III', 'BPJN Riau', 'BPPW Riau', 'BP2JK Riau', 'Satker PPS Riau'];

            if (currentRows < targetCount) {
                for (let i = currentRows + 1; i <= targetCount; i++) {
                    const balaiDefault = defaultBalaiList[(i - 1) % defaultBalaiList.length];
                    const nameDefault = `${prefix} ${i} ${catLabel}`;
                    addParticipantRow(i, nameDefault, balaiDefault);
                }
            } else if (currentRows > targetCount) {
                while (container.children.length > targetCount && container.children.length > 2) {
                    container.lastElementChild.remove();
                }
            }
            renumberParticipantRows();
        }

        // FILL NEXT EMPTY BALAI INPUT
        function fillNextEmptyBalai(balaiName) {
            const balaiInputs = document.querySelectorAll('.participant-balai-field');
            for (let inp of balaiInputs) {
                if (!inp.value.trim()) {
                    inp.value = balaiName;
                    inp.focus();
                    return;
                }
            }

            // If all balai inputs filled, add a new row and fill
            const container = document.getElementById('participantInputs');
            const nextNum = container.children.length + 1;
            addParticipantRow(nextNum, '', balaiName);
            renumberParticipantRows();
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
