import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

# 1. Add the JSON array of transport options at the top of the script
js_options_inject = """    const transportOptionsList = <?= json_encode(array_column($transportasi_list ?? [], 'nama_transportasi')) ?>;"""

if "const transportOptionsList =" not in content:
    content = content.replace(
        "    (function () {",
        "    (function () {\n" + js_options_inject
    )

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
