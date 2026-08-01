import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

# Add extraction of tgl_mulai and tgl_selesai
content = content.replace(
    "const pelaksana = $btn.attr('data-pelaksana') || '-';",
    "const pelaksana = $btn.attr('data-pelaksana') || '-';\n                const tglMulai = $btn.attr('data-tgl-mulai') || '';\n                const tglSelesai = $btn.attr('data-tgl-selesai') || '';"
)

# Modify default array generation for uang_harian
content = content.replace(
    "if (uangHarianList.length === 0) {\n                    addUangHarianInputRow({});\n                } else {",
    "if (uangHarianList.length === 0) {\n                    addUangHarianInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai });\n                } else {"
)

# Modify default array generation for transport
content = content.replace(
    "if (transportList.length === 0) {\n                    addTransportInputRow({});\n                } else {",
    "if (transportList.length === 0) {\n                    addTransportInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai });\n                } else {"
)

# Modify default array generation for penginapan
content = content.replace(
    "if (penginapanList.length === 0) {\n                    addPenginapanInputRow({});\n                } else {",
    "if (penginapanList.length === 0) {\n                    addPenginapanInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai });\n                } else {"
)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
