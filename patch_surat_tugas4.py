import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

datalist_html = """
    <!-- Datalist for Transportasi -->
    <datalist id="transportasi-master-list">
        <?php foreach ($transportasi_list ?? [] as $t): ?>
            <option value="<?= esc($t['nama_transportasi']) ?>"></option>
        <?php endforeach; ?>
    </datalist>
"""

content = content.replace(
    '<?= $this->section(\'js\'); ?>',
    datalist_html + '\n<?= $this->section(\'js\'); ?>'
)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
