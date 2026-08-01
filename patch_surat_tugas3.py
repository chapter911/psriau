import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

# 1. Add datalist to the view
datalist_html = """
    <!-- Datalist for Transportasi -->
    <datalist id="transportasi-master-list">
        <?php foreach ($transportasi_list ?? [] as $t): ?>
            <option value="<?= esc($t['nama_transportasi']) ?>"></option>
        <?php endforeach; ?>
    </datalist>
"""
# Insert before the end of a known container, e.g., after the modal form or before JS
content = content.replace(
    '</form>\n            </div>\n        </div>\n    </div>\n</div>',
    '</form>\n            </div>\n        </div>\n    </div>\n</div>\n' + datalist_html
)

# 2. Make tglMulai and tglSelesai accessible to the add functions by passing min/max
# In addUangHarianInputRow:
content = content.replace(
    '''<input type="date" class="form-control form-control-sm" name="uang_harian_start_date[]" value="${$('<div/>').text(uStart).html()}" onfocus="this.showPicker()">''',
    '''<input type="date" class="form-control form-control-sm" name="uang_harian_start_date[]" value="${$('<div/>').text(uStart).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">'''
)
content = content.replace(
    '''<input type="date" class="form-control form-control-sm" name="uang_harian_end_date[]" value="${$('<div/>').text(uEnd).html()}" onfocus="this.showPicker()">''',
    '''<input type="date" class="form-control form-control-sm" name="uang_harian_end_date[]" value="${$('<div/>').text(uEnd).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">'''
)

# In addTransportInputRow:
content = content.replace(
    '''<input type="date" class="form-control form-control-sm" name="transport_start_date[]" value="${$('<div/>').text(tStart).html()}" onfocus="this.showPicker()">''',
    '''<input type="date" class="form-control form-control-sm" name="transport_start_date[]" value="${$('<div/>').text(tStart).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">'''
)
content = content.replace(
    '''<input type="date" class="form-control form-control-sm" name="transport_end_date[]" value="${$('<div/>').text(tEnd).html()}" onfocus="this.showPicker()">''',
    '''<input type="date" class="form-control form-control-sm" name="transport_end_date[]" value="${$('<div/>').text(tEnd).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">'''
)
# Modify Keterangan to use datalist
content = content.replace(
    '''<input type="text" class="form-control form-control-sm" name="transport_ket[]" placeholder="Keterangan..." value="${$('<div/>').text(tKet).html()}">''',
    '''<input type="text" list="transportasi-master-list" class="form-control form-control-sm" name="transport_ket[]" placeholder="Moda transportasi..." value="${$('<div/>').text(tKet).html()}">'''
)


# In addPenginapanInputRow:
content = content.replace(
    '''<input type="date" class="form-control form-control-sm" name="penginapan_start_date[]" value="${$('<div/>').text(pStart).html()}" onfocus="this.showPicker()">''',
    '''<input type="date" class="form-control form-control-sm" name="penginapan_start_date[]" value="${$('<div/>').text(pStart).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">'''
)
content = content.replace(
    '''<input type="date" class="form-control form-control-sm" name="penginapan_end_date[]" value="${$('<div/>').text(pEnd).html()}" onfocus="this.showPicker()">''',
    '''<input type="date" class="form-control form-control-sm" name="penginapan_end_date[]" value="${$('<div/>').text(pEnd).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">'''
)

# 3. Store tglMulai and tglSelesai globally when clicking update
content = content.replace(
    "$(function () {",
    "$(function () {\n        let globalTglMulai = '';\n        let globalTglSelesai = '';"
)
content = content.replace(
    "const tglSelesai = $btn.attr('data-tgl-selesai') || '';",
    "const tglSelesai = $btn.attr('data-tgl-selesai') || '';\n                globalTglMulai = tglMulai;\n                globalTglSelesai = tglSelesai;"
)


with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
