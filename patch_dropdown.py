import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

# 1. Add the JSON array of transport options at the top of the script
js_options_inject = """    const transportOptionsList = <?= json_encode(array_column($transportasi_list ?? [], 'nama_transportasi')) ?>;"""

if "const transportOptionsList =" not in content:
    content = content.replace(
        "    let globalTglMulai = '';",
        js_options_inject + "\n    let globalTglMulai = '';"
    )

# 2. Update addTransportInputRow to use the dropdown
old_input = """<input type="text" list="transportasi-master-list" class="form-control form-control-sm" name="transport_jenis[]" placeholder="Cth: Pesawat" value="${$('<div/>').text(defaultJenis).html()}">"""

# Replace it inside addTransportInputRow logic
old_logic = """                let defaultJenis = tJenis;
                if (!tJenis && tKet && tKet.indexOf('-') === -1 && tKet.toLowerCase().indexOf('pp') === -1) {
                    // It might be a legacy transport type in keterangan
                    defaultJenis = tKet;
                    tKet = '';
                }

                const tIsLumpsum = data.is_lumpsum ? 'checked' : '';
                const tIsLumpsumVal = data.is_lumpsum ? '1' : '0';
                const rowId = 'lumpsum_' + Math.random().toString(36).substr(2, 9);"""

new_logic = """                let defaultJenis = tJenis;
                if (!tJenis && tKet && tKet.indexOf('-') === -1 && tKet.toLowerCase().indexOf('pp') === -1) {
                    // It might be a legacy transport type in keterangan
                    defaultJenis = tKet;
                    tKet = '';
                }

                let selectOptionsHtml = '<option value="">-- Pilih --</option>';
                transportOptionsList.forEach(opt => {
                    const isSelected = (opt === defaultJenis) ? 'selected' : '';
                    selectOptionsHtml += `<option value="${$('<div/>').text(opt).html()}" ${isSelected}>${$('<div/>').text(opt).html()}</option>`;
                });
                
                // If the defaultJenis is not in the list (e.g. legacy), add it as an option
                if (defaultJenis && !transportOptionsList.includes(defaultJenis)) {
                    selectOptionsHtml += `<option value="${$('<div/>').text(defaultJenis).html()}" selected>${$('<div/>').text(defaultJenis).html()}</option>`;
                }

                const tIsLumpsum = data.is_lumpsum ? 'checked' : '';
                const tIsLumpsumVal = data.is_lumpsum ? '1' : '0';
                const rowId = 'lumpsum_' + Math.random().toString(36).substr(2, 9);"""

new_input = """<select class="form-control form-control-sm" name="transport_jenis[]">${selectOptionsHtml}</select>"""

content = content.replace(old_logic, new_logic)
content = content.replace(old_input, new_input)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
