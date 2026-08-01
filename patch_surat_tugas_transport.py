import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

old_logic = """            function addTransportInputRow(data) {
                data = data || {};
                const tStart = data.tgl_mulai || '';
                const tEnd = data.tgl_selesai || '';
                const tNom = data.nominal !== undefined && data.nominal !== null ? data.nominal : '';
                const tKet = data.keterangan || '';

                const rowHtml = `
                    <div class="transport-row p-2 mb-2 bg-light border rounded">
                        <div class="form-row align-items-center">
                            <div class="col-md-3 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Mulai Tgl</label>
                                <input type="date" class="form-control form-control-sm" name="transport_start_date[]" value="${$('<div/>').text(tStart).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-3 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Selesai Tgl</label>
                                <input type="date" class="form-control form-control-sm" name="transport_end_date[]" value="${$('<div/>').text(tEnd).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Tarif (Rp)</label>
                                <input type="text" class="form-control form-control-sm input-currency" name="transport_nominal[]" placeholder="Rp" value="${$('<div/>').text(formatRibuan(tNom)).html()}">
                            </div>
                            <div class="col-md-3 mb-1 mb-md-0">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Keterangan</label>
                                <input type="text" list="transportasi-master-list" class="form-control form-control-sm" name="transport_ket[]" placeholder="Moda transportasi..." value="${$('<div/>').text(tKet).html()}">
                            </div>
                            <div class="col-md-1 mb-0 text-center pt-3">
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-transport" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                `;
                $('#transport-container').append(rowHtml);
            }"""

new_logic = """            function addTransportInputRow(data) {
                data = data || {};
                const tStart = data.tgl_mulai || '';
                const tEnd = data.tgl_selesai || '';
                const tNom = data.nominal !== undefined && data.nominal !== null ? data.nominal : '';
                const tJenis = data.jenis || '';
                // Handle legacy data where transport_ket held the type, but if it has a '-', it's likely a route
                let tKet = data.keterangan || '';
                let defaultJenis = tJenis;
                if (!tJenis && tKet && tKet.indexOf('-') === -1 && tKet.toLowerCase().indexOf('pp') === -1) {
                    // It might be a legacy transport type in keterangan
                    defaultJenis = tKet;
                    tKet = '';
                }

                const tIsLumpsum = data.is_lumpsum ? 'checked' : '';
                const tIsLumpsumVal = data.is_lumpsum ? '1' : '0';
                const rowId = 'lumpsum_' + Math.random().toString(36).substr(2, 9);

                const rowHtml = `
                    <div class="transport-row p-2 mb-2 bg-light border rounded">
                        <div class="form-row align-items-center">
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Mulai</label>
                                <input type="date" class="form-control form-control-sm" name="transport_start_date[]" value="${$('<div/>').text(tStart).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Selesai</label>
                                <input type="date" class="form-control form-control-sm" name="transport_end_date[]" value="${$('<div/>').text(tEnd).html()}" min="${globalTglMulai}" max="${globalTglSelesai}" onfocus="this.showPicker()">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Jenis Transp.</label>
                                <input type="text" list="transportasi-master-list" class="form-control form-control-sm" name="transport_jenis[]" placeholder="Cth: Pesawat" value="${$('<div/>').text(defaultJenis).html()}">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Rute</label>
                                <input type="text" class="form-control form-control-sm" name="transport_ket[]" placeholder="Cth: Jkt-Pku (PP)" value="${$('<div/>').text(tKet).html()}">
                            </div>
                            <div class="col-md-2 mb-1 mb-md-0" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted" style="font-size:0.75rem;">Tarif (Rp)</label>
                                <input type="text" class="form-control form-control-sm input-currency" name="transport_nominal[]" placeholder="Rp" value="${$('<div/>').text(formatRibuan(tNom)).html()}">
                            </div>
                            <div class="col-md-1 mb-1 mb-md-0 text-center" style="padding-right: 5px; padding-left: 5px;">
                                <label class="font-weight-bold mb-0 text-muted d-block" style="font-size:0.70rem;" for="${rowId}" title="Ceklis jika tarif ini untuk 1 kali bayar (Pulang-Pergi / Lumpsum) dan BUKAN tarif per hari.">Lumpsum/PP</label>
                                <input type="hidden" name="transport_is_lumpsum[]" value="${tIsLumpsumVal}" class="hidden-lumpsum">
                                <input type="checkbox" id="${rowId}" ${tIsLumpsum} style="transform: scale(1.2); margin-top: 5px; cursor:pointer;" title="Ceklis jika tarif ini untuk 1 kali bayar (Pulang-Pergi / Lumpsum) dan BUKAN tarif per hari." onchange="$(this).siblings('.hidden-lumpsum').val(this.checked ? '1' : '0')">
                            </div>
                            <div class="col-md-1 mb-0 text-center pt-3">
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove-transport" title="Hapus Baris"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                `;
                $('#transport-container').append(rowHtml);
            }"""

content = content.replace(old_logic, new_logic)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
