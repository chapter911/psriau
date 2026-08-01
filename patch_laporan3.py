import re

with open('app/Controllers/Admin/Laporan.php', 'r') as f:
    content = f.read()

# Add transportasi list
content = content.replace(
    "$mataAnggaranList = [];",
    "$transportasiList = [];\n        if ($db->tableExists('mst_transportasi')) {\n            $transportasiList = $db->table('mst_transportasi')->orderBy('nama_transportasi', 'ASC')->get()->getResultArray();\n        }\n\n        $mataAnggaranList = [];"
)

content = content.replace(
    "'mata_anggaran_list' => $mataAnggaranList,",
    "'mata_anggaran_list' => $mataAnggaranList,\n            'transportasi_list' => $transportasiList,"
)

with open('app/Controllers/Admin/Laporan.php', 'w') as f:
    f.write(content)
