import re

with open('app/Controllers/Admin/Laporan.php', 'r') as f:
    content = f.read()

# Add tglMulaiAttr and tglSelesaiAttr
content = content.replace(
    "$periodeAttr = esc((string) ($row['periode'] ?? ''), 'attr');",
    "$periodeAttr = esc((string) ($row['periode'] ?? ''), 'attr');\n                    $tglMulaiAttr = esc((string) ($row['periode_mulai'] ?? ''), 'attr');\n                    $tglSelesaiAttr = esc((string) ($row['periode_selesai'] ?? ''), 'attr');"
)

# Add to updateButtonHtml
content = content.replace(
    'data-periode="\' . $periodeAttr . \'" data-pelaksana',
    'data-periode="\' . $periodeAttr . \'" data-tgl-mulai="\' . $tglMulaiAttr . \'" data-tgl-selesai="\' . $tglSelesaiAttr . \'" data-pelaksana'
)

with open('app/Controllers/Admin/Laporan.php', 'w') as f:
    f.write(content)
