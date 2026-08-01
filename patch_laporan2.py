import re

with open('app/Controllers/Admin/Laporan.php', 'r') as f:
    content = f.read()

# Add getBiayaMasterForKota call
content = content.replace(
    "$tglSelesaiAttr = esc((string) ($row['periode_selesai'] ?? ''), 'attr');",
    "$tglSelesaiAttr = esc((string) ($row['periode_selesai'] ?? ''), 'attr');\n                    $biayaMasterRow = $this->getBiayaMasterForKota($row['kota_tujuan'] ?? '');\n                    $defHarian = $biayaMasterRow['harian'] ?? 0;\n                    $defPenginapan = (int)(($biayaMasterRow['penginapan_e4'] ?? 0) * 0.3);"
)

# Add to updateButtonHtml
content = content.replace(
    'data-tgl-selesai="\' . $tglSelesaiAttr . \'" data-pelaksana',
    'data-tgl-selesai="\' . $tglSelesaiAttr . \'" data-def-harian="\' . $defHarian . \'" data-def-penginapan="\' . $defPenginapan . \'" data-pelaksana'
)

with open('app/Controllers/Admin/Laporan.php', 'w') as f:
    f.write(content)
