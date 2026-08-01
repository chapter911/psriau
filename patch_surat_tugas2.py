import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

# Add extraction of defHarian and defPenginapan
content = content.replace(
    "const tglSelesai = $btn.attr('data-tgl-selesai') || '';",
    "const tglSelesai = $btn.attr('data-tgl-selesai') || '';\n                const defHarian = $btn.attr('data-def-harian') || '';\n                const defPenginapan = $btn.attr('data-def-penginapan') || '';"
)

# Modify addUangHarianInputRow default
content = content.replace(
    "addUangHarianInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai });",
    "addUangHarianInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai, nominal: defHarian });"
)

# Modify addPenginapanInputRow default
content = content.replace(
    "addPenginapanInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai });",
    "addPenginapanInputRow({ tgl_mulai: tglMulai, tgl_selesai: tglSelesai, nominal: defPenginapan });"
)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
