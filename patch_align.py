import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

# Replace the select tag class
content = content.replace(
    '<select class="form-control form-control-sm" name="transport_jenis[]">${selectOptionsHtml}</select>',
    '<select class="custom-select custom-select-sm" name="transport_jenis[]">${selectOptionsHtml}</select>'
)

# Replace the Checkbox alignment
old_checkbox = """                            <div class="col-md-1 mb-1 mb-md-0 text-center" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted d-block" style="font-size:0.70rem;" for="${rowId}" title="Ceklis jika tarif ini untuk 1 kali bayar (Pulang-Pergi / Lumpsum) dan BUKAN tarif per hari.">Lumpsum/PP</label>
                                <input type="hidden" name="transport_is_lumpsum[]" value="${tIsLumpsumVal}" class="hidden-lumpsum">
                                <input type="checkbox" id="${rowId}" ${tIsLumpsum} style="transform: scale(1.2); margin-top: 5px; cursor:pointer;" title="Ceklis jika tarif ini untuk 1 kali bayar (Pulang-Pergi / Lumpsum) dan BUKAN tarif per hari." onchange="$(this).siblings('.hidden-lumpsum').val(this.checked ? '1' : '0')">
                            </div>"""

new_checkbox = """                            <div class="col-md-1 mb-1 mb-md-0 text-center" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted d-block" style="font-size:0.70rem;" for="${rowId}" title="Ceklis jika tarif ini untuk 1 kali bayar (Pulang-Pergi / Lumpsum) dan BUKAN tarif per hari.">Lumpsum</label>
                                <input type="hidden" name="transport_is_lumpsum[]" value="${tIsLumpsumVal}" class="hidden-lumpsum">
                                <div class="d-flex align-items-center justify-content-center" style="height: calc(1.5em + 0.5rem + 2px);">
                                    <input type="checkbox" id="${rowId}" ${tIsLumpsum} style="transform: scale(1.3); cursor:pointer;" title="Ceklis jika tarif ini untuk 1 kali bayar (Pulang-Pergi / Lumpsum) dan BUKAN tarif per hari." onchange="$(this).siblings('.hidden-lumpsum').val(this.checked ? '1' : '0')">
                                </div>
                            </div>"""
content = content.replace(old_checkbox, new_checkbox)

# Replace the Trash button for Transport
old_trash_trans = """                            <div class="col-md-1 mb-0 text-center pt-3">
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-transport" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>"""

new_trash_trans = """                            <div class="col-md-1 mb-1 mb-md-0 text-center">
                                <label class="d-block mb-0" style="font-size:0.75rem;">&nbsp;</label>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-transport" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>"""
content = content.replace(old_trash_trans, new_trash_trans)


# I also should fix the trash buttons for Uang Harian and Penginapan because they might have the same issue (using pt-3 instead of empty label)
old_trash_uang = """                            <div class="col-md-1 mb-0 text-center pt-3">
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-uang-harian" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>"""
new_trash_uang = """                            <div class="col-md-1 mb-1 mb-md-0 text-center">
                                <label class="d-block mb-0" style="font-size:0.75rem;">&nbsp;</label>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-uang-harian" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>"""
content = content.replace(old_trash_uang, new_trash_uang)

old_trash_peng = """                            <div class="col-md-1 mb-0 text-center pt-3">
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-penginapan" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>"""
new_trash_peng = """                            <div class="col-md-1 mb-1 mb-md-0 text-center">
                                <label class="d-block mb-0" style="font-size:0.75rem;">&nbsp;</label>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-penginapan" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>"""
content = content.replace(old_trash_peng, new_trash_peng)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
