<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title); ?> - Export PDF Poster</title>
    
    <!-- FontAwesome & Bootstrap CSS for Crisp Typography -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <?php
    $appLogoRaw = $globalSetting['logo_url'] ?? $appSetting['app_logo_url'] ?? '';
    $appLogoUrl = ! empty($appLogoRaw) ? media_url((string) $appLogoRaw) : site_url('assets/img/logo.png');
    $isPortrait = $orientation === 'portrait';
    ?>

    <style>
        /* Edge-to-Edge Page Reset */
        @page {
            size: A4 <?= $orientation; ?>;
            margin: 0 !important;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html, body {
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: radial-gradient(circle at 50% 15%, #1e3a8a 0%, #0f172a 55%, #020617 100%) !important;
            color: #ffffff;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        .poster-frame-outer {
            width: 100vw;
            height: 100vh;
            padding: 8px;
            box-sizing: border-box;
        }

        .poster-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 10px 14px;
            position: relative;
            box-sizing: border-box;
            border: 2px solid #d4af37;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.4);
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.6);
        }

        /* Printable Official Header Banner */
        .poster-header {
            text-align: center;
            padding-bottom: 6px;
            border-bottom: 1.5px solid rgba(212, 175, 55, 0.6);
            flex-shrink: 0;
            width: 100%;
        }

        .poster-dept-title {
            color: #cbd5e1;
            font-size: 0.76rem;
            letter-spacing: 1.2px;
            font-weight: 700;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .poster-satker-title {
            color: #38bdf8;
            font-size: 1.18rem;
            letter-spacing: 1.8px;
            font-weight: 800;
            line-height: 1.15;
            text-transform: uppercase;
            text-shadow: 0 2px 6px rgba(0,0,0,0.8);
        }

        .poster-year-badge {
            background: linear-gradient(135deg, #d4af37, #f59e0b);
            color: #0f172a;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 2px 14px;
            border-radius: 16px;
            letter-spacing: 1px;
            display: inline-block;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
        }

        /* Org Tree Canvas Wrapper (Aligned Top-Center) */
        .poster-canvas {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            position: relative;
            overflow: hidden;
            padding-top: 6px;
            width: 100%;
        }

        .org-tree-wrapper {
            display: inline-flex;
            justify-content: center;
            transform-origin: top center;
            transition: transform 0.1s ease-out;
        }

        .org-node-tree {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Tree Connections (Compact Glowing Stems) */
        .org-node-children-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            padding-top: 14px;
        }

        .org-node-children-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 2px solid #38bdf8;
            width: 0;
            height: 14px;
            filter: drop-shadow(0 0 2px #0284c7);
        }

        .org-node-children {
            display: flex;
            justify-content: center;
            position: relative;
            padding-top: 14px;
        }

        .org-node-children::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 2px solid #38bdf8;
            width: 0;
            height: 14px;
            filter: drop-shadow(0 0 2px #0284c7);
        }

        .org-node-children-row + .org-node-children-row {
            margin-top: 8px;
        }

        .org-node-children-row + .org-node-children-row::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            border-left: 2px solid #38bdf8;
            width: 0;
            height: 22px;
            filter: drop-shadow(0 0 2px #0284c7);
        }

        .org-node-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            padding: 14px 8px 0 8px;
        }

        .org-node-tree > .org-node-item {
            padding-top: 0;
        }

        .org-node-item::before, .org-node-item::after {
            content: '';
            position: absolute;
            top: 0;
            height: 14px;
        }

        .org-node-item::before {
            right: 50%;
            width: 50%;
            border-top: 2px solid #38bdf8;
            filter: drop-shadow(0 0 2px #0284c7);
        }

        .org-node-item::after {
            left: 50%;
            width: 50%;
            border-top: 2px solid #38bdf8;
            border-left: 2px solid #38bdf8;
            filter: drop-shadow(0 0 2px #0284c7);
        }

        .org-node-item:first-child::before { border-top: none; }
        .org-node-item:last-child::after { border-top: none; }
        .org-node-item:only-child::before { border-top: none; }
        .org-node-item:only-child::after {
            border-top: none;
            border-left: 2px solid #38bdf8;
        }

        /* Compact Card Styling for 1-Page Poster Guarantee */
        .org-card {
            width: 200px;
            height: 190px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.45);
            border: 2px solid #38bdf8;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #0f172a;
        }

        .org-card.level-1 {
            width: 220px;
            height: 200px;
            border: 2.5px solid #f59e0b !important;
            background: linear-gradient(180deg, #ffffff 0%, #fffdf5 100%);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.35) !important;
        }

        .org-card.level-2, .org-card.level-3 {
            width: 205px;
            height: 190px;
            border-color: #38bdf8;
            box-shadow: 0 8px 20px rgba(56, 189, 248, 0.25) !important;
        }

        .org-card-header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #ffffff;
            padding: 4px 6px;
            text-align: center;
            font-size: 0.72rem;
            font-weight: 700;
            text-uppercase: uppercase;
            line-height: 1.15;
            letter-spacing: 0.4px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .level-1 .org-card-header {
            background: linear-gradient(135deg, #78350f, #b45309);
            color: #fef08a;
            border-bottom: 1.5px solid #f59e0b;
        }

        .org-card-body {
            padding: 5px 6px;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .org-avatar-frame {
            width: 54px;
            height: 54px;
            margin: 0 auto 3px auto;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #0284c7;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .level-1 .org-avatar-frame {
            width: 64px;
            height: 64px;
            border: 2.5px solid #f59e0b;
            box-shadow: 0 3px 10px rgba(245, 158, 11, 0.4);
        }

        .org-avatar-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .org-pegawai-nama {
            font-size: 0.76rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1px;
            line-height: 1.15;
        }

        .org-pegawai-nip {
            font-size: 0.65rem;
            color: #475569;
            margin-bottom: 1px;
        }

        /* Group Block Cards */
        .org-card.org-group-block {
            width: 520px;
            max-width: 100%;
            height: auto !important;
            min-height: 110px;
            border: 2px solid #38bdf8;
            background: #f8fafc;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
            overflow: hidden !important;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .org-card.org-group-block-pendukung {
            border-color: #374151 !important;
            background: #f9fafb !important;
        }

        .org-group-header {
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            color: #ffffff;
            padding: 4px 10px;
            font-weight: 700;
            font-size: 0.72rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            letter-spacing: 0.4px;
            text-uppercase: uppercase;
            height: 34px;
            flex-shrink: 0;
        }

        .org-group-body {
            padding: 8px;
            flex-grow: 1;
        }

        .org-group-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            width: 100%;
        }

        .org-member-card {
            background: #ffffff;
            border: 1.2px solid #cbd5e1;
            border-radius: 6px;
            padding: 4px 8px;
            height: 48px;
            display: flex;
            align-items: center;
            position: relative;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            width: 100%;
            overflow: hidden;
            color: #0f172a;
        }

        .org-member-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            border: 1.5px solid #d4af37;
            margin-right: 6px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
        }

        .org-member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .org-member-details {
            flex: 1 1 auto;
            min-width: 0;
        }

        .org-member-nama {
            font-weight: 700;
            font-size: 0.72rem;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.15;
        }

        .org-member-title {
            font-size: 0.64rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .poster-footer {
            text-align: center;
            font-size: 0.65rem;
            color: #94a3b8;
            padding-top: 2px;
            letter-spacing: 0.4px;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

    <div class="poster-frame-outer">
        <div class="poster-container">
            
            <!-- Official Executive Poster Header Banner -->
            <div class="poster-header">
                <div class="d-flex align-items-center justify-content-center">
                    <?php if (! empty($appLogoUrl)): ?>
                        <img src="<?= esc($appLogoUrl); ?>" alt="Logo Aplikasi" height="48" class="mr-3" style="object-fit: contain; filter: drop-shadow(0 3px 8px rgba(0,0,0,0.6));" onerror="this.style.display='none';">
                    <?php endif; ?>
                    <div class="text-left">
                        <h6 class="poster-dept-title mb-0">
                            KEMENTERIAN PEKERJAAN UMUM — DIREKTORAT JENDERAL PRASARANA STRATEGIS
                        </h6>
                        <h4 class="poster-satker-title mb-1">
                            SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU
                        </h4>
                        <div>
                            <span class="poster-year-badge">
                                BAGAN STRUKTUR ORGANISASI RESMI TA <?= date('Y'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Poster Org Tree Canvas -->
            <div class="poster-canvas" id="poster-canvas">
                <div class="org-tree-wrapper" id="org-tree-wrapper">
                    <!-- Tree rendered via JS -->
                </div>
            </div>

            <!-- Footer Tagline -->
            <div class="poster-footer">
                <i class="fas fa-shield-alt text-warning mr-1"></i> Dokumen Resmi Bagan Struktur Organisasi • Satuan Kerja Pelaksanaan Prasarana Strategis Riau
            </div>

        </div>
    </div>

<script>
    const rawNodesData = <?= json_encode($treeNodes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const isPortrait = <?= json_encode($isPortrait); ?>;

    window.onload = function () {
        renderOrgChart();
        autoFitScale();
        setTimeout(function () {
            window.print();
        }, 350);
    };

    function autoFitScale() {
        const wrapper = document.getElementById('org-tree-wrapper');
        const canvas = document.getElementById('poster-canvas');
        if (!wrapper || !canvas) return;

        // Reset to 1 to measure exact unscaled dimensions
        wrapper.style.transform = 'scale(1)';

        const treeWidth = wrapper.offsetWidth || wrapper.scrollWidth || 1100;
        const treeHeight = wrapper.offsetHeight || wrapper.scrollHeight || 750;

        const availWidth = canvas.clientWidth - 20;
        const availHeight = canvas.clientHeight - 10;

        const scaleX = availWidth / treeWidth;
        const scaleY = availHeight / treeHeight;
        
        // Exact mathematical scale to guarantee 0% overflow cutoff
        let autoScale = Math.min(scaleX, scaleY);

        // Safe bounds: allows shrinking smoothly down to 0.15 without minimum clamping cutoff!
        autoScale = Math.min(Math.max(autoScale, 0.15), 0.95);

        wrapper.style.transform = `scale(${autoScale.toFixed(3)})`;
    }

    function renderOrgChart() {
        const wrapper = document.getElementById('org-tree-wrapper');
        if (!wrapper) return;

        if (!rawNodesData || rawNodesData.length === 0) {
            wrapper.innerHTML = `<h5 class="text-white">Bagan Struktur Organisasi Masih Kosong</h5>`;
            return;
        }

        const nodeMap = {};
        rawNodesData.forEach(node => {
            nodeMap[node.id] = { ...node, children: [] };
        });

        const rootNodes = [];
        rawNodesData.forEach(node => {
            if (node.parent_id && nodeMap[node.parent_id]) {
                nodeMap[node.parent_id].children.push(nodeMap[node.id]);
            } else {
                rootNodes.push(nodeMap[node.id]);
            }
        });

        let html = '<div class="org-node-tree">';
        rootNodes.forEach(root => {
            html += buildNodeHtml(root);
        });
        html += '</div>';

        wrapper.innerHTML = html;
    }

    function buildNodeHtml(node) {
        const hasChildren = node.children && node.children.length > 0;
        const levelClass = 'level-' + (node.level || 1);

        let avatarHtml = '';
        if (node.foto_pegawai && String(node.foto_pegawai).trim() !== '') {
            const fotoClean = String(node.foto_pegawai).replace(/^\/+/, '');
            const fotoUrl = '<?= site_url('/'); ?>' + fotoClean;
            avatarHtml = `
                <img src="${escapeHtml(fotoUrl)}" alt="" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.classList.remove('d-none');">
                <i class="fas fa-user-tie fa-2x text-secondary d-none"></i>
            `;
        } else {
            avatarHtml = `<i class="fas fa-user-tie fa-2x text-secondary"></i>`;
        }

        let nodeHtml = `
            <div class="org-node-item">
                <div class="org-card ${levelClass}">
                    <div class="org-card-header">
                        ${escapeHtml(node.jabatan_bagian)}
                    </div>
                    <div class="org-card-body">
                        <div class="org-avatar-frame">
                            ${avatarHtml}
                        </div>
                        <div class="org-pegawai-nama">
                            ${escapeHtml(node.nama_pegawai || '(Belum Ditentukan)')}
                        </div>
                        <div class="org-pegawai-nip">
                            ${node.nip_pegawai ? (node.nip_pegawai.toLowerCase().includes('menteri') || node.nip_pegawai.toLowerCase().includes('direktur') || isNaN(node.nip_pegawai) ? escapeHtml(node.nip_pegawai) : 'NIP. ' + escapeHtml(node.nip_pegawai)) : (node.nama_jabatan_master ? escapeHtml(node.nama_jabatan_master) : 'Satker PPS Riau')}
                        </div>
                    </div>
                </div>
        `;

        if (hasChildren) {
            node.children.sort((a, b) => (parseInt(a.urutan) || 1) - (parseInt(b.urutan) || 1));

            const mainSubNodes = [];
            const leafMembers = [];

            node.children.forEach(child => {
                const titleLower = (child.jabatan_bagian || '').toLowerCase();
                const katLower = (child.kategori_kelompok || '').toLowerCase();
                const childHasChildren = child.children && child.children.length > 0;

                const isTeamCategory = katLower === 'staf' || katLower === 'pendukung';
                const isTeamTitle = titleLower.startsWith('anggota') ||
                                    titleLower.includes('security') ||
                                    titleLower.includes('satpam') ||
                                    titleLower.includes('cleaning') ||
                                    titleLower.includes('driver') ||
                                    titleLower.includes('sopir') ||
                                    titleLower.includes('ob ');

                const isStructuralKeyword = titleLower.includes('ppk') ||
                                            titleLower.includes('bendahara') ||
                                            titleLower.includes('spm') ||
                                            titleLower.includes('penguji') ||
                                            titleLower.includes('kasatker') ||
                                            titleLower.includes('kasubbag') ||
                                            titleLower.includes('direktur') ||
                                            titleLower.includes('dirjen') ||
                                            titleLower.includes('menteri') ||
                                            titleLower.includes('kabag') ||
                                            titleLower.includes('kepala');

                const isStructuralHead = childHasChildren || isStructuralKeyword || (!isTeamCategory && !isTeamTitle);

                if (isStructuralHead) {
                    mainSubNodes.push(child);
                } else {
                    leafMembers.push(child);
                }
            });

            nodeHtml += '<div class="org-node-children-wrapper">';
            nodeHtml += '<div class="org-node-children org-node-children-row">';

            mainSubNodes.forEach(c => {
                nodeHtml += buildNodeHtml(c);
            });

            if (leafMembers.length > 0) {
                nodeHtml += buildGroupBlockHtml(node, leafMembers);
            }

            nodeHtml += '</div>';
            nodeHtml += '</div>';
        }

        nodeHtml += '</div>';
        return nodeHtml;
    }

    function buildGroupBlockHtml(parentNode, leafMembers) {
        const teknisMembers = [];
        const pendukungMembers = [];

        leafMembers.forEach(m => {
            const titleLower = (m.jabatan_bagian || '').toLowerCase();
            const katLower = (m.kategori_kelompok || '').toLowerCase();

            const isPendukung = katLower === 'pendukung' ||
                titleLower.includes('security') ||
                titleLower.includes('satpam') ||
                titleLower.includes('keamanan') ||
                titleLower.includes('cleaning') ||
                titleLower.includes('kebersihan') ||
                titleLower.includes('cs') ||
                titleLower.includes('driver') ||
                titleLower.includes('sopir') ||
                titleLower.includes('pengemudi') ||
                titleLower.includes('ob') ||
                titleLower.includes('office boy') ||
                titleLower.includes('penjaga') ||
                titleLower.includes('pramusaji') ||
                titleLower.includes('pendukung') ||
                titleLower.includes('teknisi');

            if (isPendukung) {
                pendukungMembers.push(m);
            } else {
                teknisMembers.push(m);
            }
        });

        if (teknisMembers.length > 0 && pendukungMembers.length > 0) {
            return `
                <div class="org-node-item">
                    <div class="org-card org-group-block">
                        <div class="org-group-header">
                            <span><i class="fas fa-user-gear mr-2"></i> TIM TEKNIS & STAF PELAKSANA (${teknisMembers.length})</span>
                        </div>
                        <div class="org-group-body">
                            <div class="org-group-grid">
                                ${teknisMembers.map(m => renderMemberCardItemHtml(m)).join('')}
                            </div>
                        </div>
                    </div>

                    <div class="org-node-children-wrapper">
                        <div class="org-node-children org-node-children-row">
                            <div class="org-node-item">
                                <div class="org-card org-group-block org-group-block-pendukung">
                                    <div class="org-group-header bg-dark text-warning border-bottom border-warning">
                                        <span><i class="fas fa-shield-alt text-warning mr-2"></i> TIM PENDUKUNG OPERASIONAL (${pendukungMembers.length})</span>
                                    </div>
                                    <div class="org-group-body">
                                        <div class="org-group-grid">
                                            ${pendukungMembers.map(m => renderMemberCardItemHtml(m)).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            `;
        }

        if (pendukungMembers.length > 0) {
            return `
                <div class="org-node-item">
                    <div class="org-card org-group-block org-group-block-pendukung">
                        <div class="org-group-header bg-dark text-warning border-bottom border-warning">
                            <span><i class="fas fa-shield-alt text-warning mr-2"></i> TIM PENDUKUNG OPERASIONAL (${pendukungMembers.length})</span>
                        </div>
                        <div class="org-group-body">
                            <div class="org-group-grid">
                                ${pendukungMembers.map(m => renderMemberCardItemHtml(m)).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        return `
            <div class="org-node-item">
                <div class="org-card org-group-block">
                    <div class="org-group-header">
                        <span><i class="fas fa-users mr-2"></i> ANGGOTA / TIM PELAKSANA (${teknisMembers.length})</span>
                    </div>
                    <div class="org-group-body">
                        <div class="org-group-grid">
                            ${teknisMembers.map(m => renderMemberCardItemHtml(m)).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderMemberCardItemHtml(m) {
        let avatarHtml = '';
        if (m.foto_pegawai && String(m.foto_pegawai).trim() !== '') {
            const fotoClean = String(m.foto_pegawai).replace(/^\/+/, '');
            const fotoUrl = '<?= site_url('/'); ?>' + fotoClean;
            avatarHtml = `
                <img src="${escapeHtml(fotoUrl)}" alt="" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.classList.remove('d-none');">
                <i class="fas fa-user-tie text-secondary d-none"></i>
            `;
        } else {
            avatarHtml = `<i class="fas fa-user-tie text-secondary"></i>`;
        }

        return `
            <div class="org-member-card">
                <div class="org-member-avatar">
                    ${avatarHtml}
                </div>
                <div class="org-member-details">
                    <div class="org-member-nama" title="${escapeHtml(m.nama_pegawai)}">${escapeHtml(m.nama_pegawai || '(Belum Ditentukan)')}</div>
                    <div class="org-member-title">${escapeHtml(m.jabatan_bagian)}</div>
                </div>
            </div>
        `;
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

</body>
</html>
