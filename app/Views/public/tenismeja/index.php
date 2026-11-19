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

        /* PRINT PREVIEW MODE STYLES (DIAPLIKASIKAN SEBELUM DIALOG CETAK TERBUKA) */
        body.print-preview-mode .bracket-tree {
            transform: none !important;
            zoom: 1 !important;
        }
        body.print-preview-mode .bracket-tree.is-10p {
            gap: 20px !important;
        }
        body.print-preview-mode .bracket-tree.is-10p .bracket-round-column {
            width: 220px !important;
        }
        body.print-preview-mode .bracket-tree:not(.is-10p) {
            gap: 40px !important;
        }
        body.print-preview-mode .bracket-tree:not(.is-10p) .bracket-round-column {
            width: 260px !important;
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
            margin-top: 14px;
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
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
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

            .score-print-display {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 36px !important;
                height: 28px !important;
                border: 1.5px solid #cbd5e1 !important;
                border-radius: 6px !important;
                font-weight: 800 !important;
                font-size: 0.95rem !important;
                color: #0f172a !important;
                background: #f8fafc !important;
            }

            .score-print-display.is-winner {
                background: #d1fae5 !important;
                border-color: #10b981 !important;
                color: #065f46 !important;
            }

            .winner-print-check {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 24px !important;
                height: 24px !important;
                border-radius: 50% !important;
                background: #10b981 !important;
                color: #ffffff !important;
                font-size: 0.72rem !important;
                margin-left: 2px !important;
            }

            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                height: 100% !important;
                overflow: hidden !important;
            }

            .main-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }

            .site-header {
                padding: 8px 14px !important;
                background: #002244 !important;
                border-radius: 6px !important;
                margin-bottom: 6px !important;
            }

            .site-header h1 {
                font-size: 1rem !important;
            }

            .tournament-banner {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px !important;
                margin-bottom: 8px !important;
                padding: 6px 12px !important;
            }

            .tournament-banner h2 {
                font-size: 1rem !important;
            }

            .bracket-scroll-container {
                overflow: visible !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                min-height: auto !important;
                height: auto !important;
            }

            .bracket-tree-wrapper {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .bracket-tree {
                min-width: 100% !important;
                padding: 0 !important;
                transform: none !important;
                zoom: 1 !important;
            }

            .bracket-tree.is-10p {
                gap: 20px !important;
            }

            .bracket-tree.is-10p .bracket-round-column {
                width: 220px !important;
            }

            .bracket-tree.is-10p .round-matches-list {
                gap: 6px !important;
            }

            .bracket-tree.is-10p .player-slot {
                padding: 3px 6px !important;
            }

            .bracket-tree:not(.is-10p) {
                gap: 40px !important;
            }

            .bracket-tree:not(.is-10p) .bracket-round-column {
                width: 260px !important;
            }

            @page {
                size: A4 landscape;
                margin: 5mm 8mm;
            }
        }
    </style>
</head>
<body>

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
                <!-- ACAK POSISI (SUPERADMINISTRATOR ONLY) -->
                <button type="button" class="btn-action btn-action-shuffle" id="btnAcakPosisi">
                    <i class="fas fa-shuffle"></i> Acak Posisi
                    <span style="font-size: 0.7rem; background: rgba(0,0,0,0.25); padding: 2px 6px; border-radius: 4px; margin-left: 2px;">
                        <i class="fas fa-lock"></i> Superadmin
                    </span>
                </button>

                <!-- PAS LAYAR (TANPA PERLU GESER-GESER) -->
                <button type="button" class="btn-action btn-action-fit active" id="btnFitScreen" onclick="toggleFitScreen()" title="Mode Pas Layar (Otomatis menyesuaikan layar tanpa perlu menggeser)">
                    <i class="fas fa-compress"></i> Pas Layar
                </button>

                <!-- CETAK BAGAN (TAMPILAN WEB) -->
                <button type="button" class="btn-action btn-action-print" onclick="printBracket()" title="Cetak / Simpan PDF langsung dari peramban">
                    <i class="fas fa-print"></i> Cetak Bagan
                </button>

                <!-- EKSPOR PDF -->
                <a href="<?= site_url('tenismeja/pdf?category=putra'); ?>" target="_blank" class="btn-action btn-action-pdf" id="btnEksporPdf">
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

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // API ENDPOINTS
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
            // Check hash in URL (#putra, #putri, #ganda_putra, #ganda_putri)
            const hash = window.location.hash.replace('#', '');
            if (['putra', 'putri', 'ganda_putra', 'ganda_putri'].includes(hash)) {
                currentCategory = hash;
            } else {
                currentCategory = 'putra';
            }

            updateCategoryTabUI(currentCategory);
            renderBracket(allTournaments[currentCategory]);
            initEventHandlers();
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
            const pdfBtn = document.getElementById('btnEksporPdf');

            Object.keys(tabs).forEach(k => {
                if (tabs[k]) {
                    if (k === cat) tabs[k].classList.add('active');
                    else tabs[k].classList.remove('active');
                }
            });

            if (pdfBtn) {
                pdfBtn.href = `${PDF_BASE_URL}?category=${cat}`;
            }

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

            if (data.participant_count === 10) {
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
                roundData.matches.forEach((m) => {
                    if (data.participant_count === 10 && m.group && m.group !== currentGroup) {
                        currentGroup = m.group;
                        const groupTitle = document.createElement('div');
                        if (currentGroup === 'atas') {
                            groupTitle.className = 'bracket-group-section-title group-atas';
                            groupTitle.innerHTML = '<i class="fas fa-arrow-up"></i> Bagan Atas';
                        } else if (currentGroup === 'bawah') {
                            groupTitle.className = 'bracket-group-section-title group-bawah';
                            groupTitle.innerHTML = '<i class="fas fa-arrow-down"></i> Bagan Bawah';
                        } else if (currentGroup === 'grand_final') {
                            groupTitle.className = 'bracket-group-section-title group-grand-final';
                            groupTitle.innerHTML = '<i class="fas fa-trophy"></i> Grand Final (Juara Utama)';
                        }
                        matchesList.appendChild(groupTitle);
                    }
                    const matchCard = createMatchCardElement(m, isFinalRound);
                    matchesList.appendChild(matchCard);
                });
            }

            // Draw visible SVG connector lines between matches & auto-fit
            requestAnimationFrame(() => {
                drawBracketLines(data);
                if (isFitScreenMode) {
                    applyScreenFit();
                }
            });
            setTimeout(() => {
                drawBracketLines(data);
                if (isFitScreenMode) {
                    applyScreenFit();
                }
            }, 60);
        }

        // FUNGSI HITUNG POSISI ELEMEN RELATIF TERHADAP ROOT BRACKET TREE
        function getCardLocalPos(card, root) {
            let left = 0;
            let top = 0;
            let el = card;
            while (el && el !== root) {
                left += el.offsetLeft;
                top += el.offsetTop;
                el = el.offsetParent;
            }
            return {
                left: left,
                top: top,
                right: left + card.offsetWidth,
                bottom: top + card.offsetHeight,
                width: card.offsetWidth,
                height: card.offsetHeight
            };
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

            const scrollW = tree.scrollWidth;
            const scrollH = tree.scrollHeight;

            svg.setAttribute('width', scrollW);
            svg.setAttribute('height', scrollH);
            svg.style.width = scrollW + 'px';
            svg.style.height = scrollH + 'px';

            const rounds = data.bracket_json.rounds;
            const totalRounds = parseInt(data.bracket_json.total_rounds || Object.keys(rounds).length);

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

                    const x1 = posA.right;
                    const y1 = posA.top + (posA.height / 2);

                    const x2 = posB.left;
                    const slotRatio = (m.next_match_slot === 1) ? 0.32 : 0.68;
                    const y2 = posB.top + (posB.height * slotRatio);

                    const midX = x1 + (x2 - x1) * 0.5;

                    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    const d = `M ${x1} ${y1} H ${midX} V ${y2} H ${x2}`;
                    path.setAttribute('d', d);

                    const isWinner = !!(m.winner && m.status === 'completed');
                    path.setAttribute('class', 'bracket-line' + (isWinner ? ' winner' : ''));
                    svg.appendChild(path);

                    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    circle.setAttribute('cx', x2);
                    circle.setAttribute('cy', y2);
                    circle.setAttribute('r', isWinner ? '4.5' : '3.5');
                    circle.setAttribute('class', 'bracket-dot' + (isWinner ? ' winner' : ''));
                    svg.appendChild(circle);
                });
            }
        }

        // FIT LAYAR / PAS LAYAR (OTOMATIS TANPA PERLU MENGGESER-GESER LAYAR)
        let isFitScreenMode = true;

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
                    drawBracketLines(allTournaments[currentCategory]);
                }
                return;
            }

            // Reset sejenak untuk kalkulasi dimensi aktual kontainer
            tree.style.transform = '';
            tree.style.zoom = '';

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

            if (allTournaments[currentCategory]) {
                drawBracketLines(allTournaments[currentCategory]);
            }
        }

        window.addEventListener('resize', () => {
            if (isFitScreenMode) {
                applyScreenFit();
            } else if (allTournaments[currentCategory]) {
                drawBracketLines(allTournaments[currentCategory]);
            }
        });

        // CETAK BAGAN KHUSUS (PAS 1 HALAMAN LANDSCAPE & GARIS ALIR TETAP PRESISI)
        function printBracket() {
            const currentData = allTournaments[currentCategory];
            const tree = document.getElementById('bracketTree');
            const scrollContainer = document.querySelector('.bracket-scroll-container');
            if (!tree) {
                window.print();
                return;
            }

            // Aktifkan stylesheet preview cetak
            document.body.classList.add('print-preview-mode');
            tree.style.transform = '';
            tree.style.zoom = '';
            if (scrollContainer) {
                scrollContainer.style.height = '';
                scrollContainer.style.overflow = 'visible';
            }

            // Gambar ulang garis SVG agar koordinatnya 100% tepat pada dimensi cetak
            drawBracketLines(currentData);

            setTimeout(() => {
                window.print();
                setTimeout(() => {
                    document.body.classList.remove('print-preview-mode');
                    if (isFitScreenMode) {
                        applyScreenFit();
                    } else {
                        drawBracketLines(currentData);
                    }
                }, 300);
            }, 80);
        }

        window.addEventListener('beforeprint', () => {
            const currentData = allTournaments[currentCategory];
            const tree = document.getElementById('bracketTree');
            const scrollContainer = document.querySelector('.bracket-scroll-container');
            document.body.classList.add('print-preview-mode');
            if (tree) {
                tree.style.transform = '';
                tree.style.zoom = '';
            }
            if (scrollContainer) {
                scrollContainer.style.height = '';
                scrollContainer.style.overflow = 'visible';
            }
            if (currentData) {
                drawBracketLines(currentData);
            }
        });

        window.addEventListener('afterprint', () => {
            document.body.classList.remove('print-preview-mode');
            if (isFitScreenMode) {
                applyScreenFit();
            } else if (allTournaments[currentCategory]) {
                drawBracketLines(allTournaments[currentCategory]);
            }
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
                        ` : `
                            <input type="number" min="0" max="99" class="score-input" id="score1_${match.id}" value="${match.score1 !== null ? match.score1 : ''}" placeholder="0" ${!p1 ? 'disabled' : ''}>
                            <button type="button" class="btn-pick-winner" title="Tandai sebagai Pemenang" onclick="setMatchWinner('${match.id}', '${p1 ? escapeHtml(p1.name) : ''}')" ${!p1 ? 'disabled' : ''}>
                                <i class="fas fa-check"></i>
                            </button>
                            <span class="score-print-display ${isP1Winner ? 'is-winner' : ''}">${match.score1 !== null ? match.score1 : '0'}</span>
                            ${isP1Winner ? '<span class="winner-print-check"><i class="fas fa-check"></i></span>' : ''}
                        `}
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
                        ` : `
                            <input type="number" min="0" max="99" class="score-input" id="score2_${match.id}" value="${match.score2 !== null ? match.score2 : ''}" placeholder="0" ${!p2 ? 'disabled' : ''}>
                            <button type="button" class="btn-pick-winner" title="Tandai sebagai Pemenang" onclick="setMatchWinner('${match.id}', '${p2 ? escapeHtml(p2.name) : ''}')" ${!p2 ? 'disabled' : ''}>
                                <i class="fas fa-check"></i>
                            </button>
                            <span class="score-print-display ${isP2Winner ? 'is-winner' : ''}">${match.score2 !== null ? match.score2 : '0'}</span>
                            ${isP2Winner ? '<span class="winner-print-check"><i class="fas fa-check"></i></span>' : ''}
                        `}
                    </div>
                </div>

                ${match.note ? `
                <div class="match-note-banner">
                    <i class="fas fa-info-circle text-primary"></i> ${escapeHtml(match.note)}
                </div>
                ` : ''}

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

        // SET MATCH WINNER DIRECTLY
        async function setMatchWinner(matchId, winnerName) {
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

            // RESET SKOR
            const btnReset = document.getElementById('btnResetSkor');
            btnReset.addEventListener('click', function () {
                const catLabel = (currentCategory === 'putri') ? 'Putri' : 'Putra';

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

            // MODAL SETUP OPEN / CLOSE
            const modal = document.getElementById('setupModal');
            const btnOpenSetup = document.getElementById('btnOpenSetup');
            const btnCloseSetup = document.getElementById('btnCloseSetup');
            const btnCancelSetup = document.getElementById('btnCancelSetup');

            btnOpenSetup.addEventListener('click', () => {
                document.getElementById('formCategory').value = currentCategory;
                populateParticipantInputs(currentCategory);
                modal.classList.add('active');
            });

            btnCloseSetup.addEventListener('click', () => modal.classList.remove('active'));
            btnCancelSetup.addEventListener('click', () => modal.classList.remove('active'));

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
            document.getElementById('btnSubmitSetup').addEventListener('click', async function () {
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

            // REDRAW BRACKET CONNECTOR LINES ON RESIZE
            window.addEventListener('resize', () => {
                if (allTournaments[currentCategory]) {
                    drawBracketLines(allTournaments[currentCategory]);
                }
            });
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
