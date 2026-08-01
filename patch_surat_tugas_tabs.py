import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

# Replace Tab 1 ending
old_tab1_end = """                            <small class="text-muted mt-1 d-block">Masukkan dasar hukum/dasar tugas SPT secara manual.</small>
                        </div>"""

new_tab1_end = """                            <small class="text-muted mt-1 d-block">Masukkan dasar hukum/dasar tugas SPT secara manual.</small>
                            <div class="mt-4 pt-3 border-top text-right">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-success btn-sm font-weight-bold btn-save-tab" data-tab="tab1"><i class="fas fa-save mr-1"></i> Simpan SPT</button>
                            </div>
                        </div>"""
content = content.replace(old_tab1_end, new_tab1_end)

# Replace Tab 2 ending (which also removes the old modal-footer)
old_tab2_end = """                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Tambahkan baris baru jika terdapat perbedaan tanggal/tarif hotel. Kosongkan jika menggunakan tarif master.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold" id="btn-save-verify">Simpan Verifikasi</button>
                </div>"""

new_tab2_end = """                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Tambahkan baris baru jika terdapat perbedaan tanggal/tarif hotel. Kosongkan jika menggunakan tarif master.</small>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top text-right">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary btn-sm font-weight-bold btn-save-tab" data-tab="tab2"><i class="fas fa-save mr-1"></i> Simpan Rincian Biaya</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- modal footer removed since buttons are inside tabs -->"""
content = content.replace(old_tab2_end, new_tab2_end)


# Add AJAX submit logic
ajax_logic = """
        // Handle AJAX submit per tab
        $('.btn-save-tab').on('click', function(e) {
            e.preventDefault();
            const tabAction = $(this).data('tab');
            const $form = $('#form-verify-spt');
            const url = $form.attr('action');
            
            // Create a FormData object
            const formData = new FormData($form[0]);
            formData.append('tab_action', tabAction);

            const $btn = $(this);
            const originalText = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $btn.html(originalText).prop('disabled', false);
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        $table.ajax.reload(null, false);
                    } else {
                        toastr.error(response.message || 'Terjadi kesalahan');
                    }
                },
                error: function() {
                    $btn.html(originalText).prop('disabled', false);
                    toastr.error('Terjadi kesalahan saat menyimpan data');
                }
            });
        });
"""

# Insert ajax_logic after `$('#btn-add-penginapan').on('click', function () { ... });`
content = content.replace(
    '''            $('#btn-add-penginapan').on('click', function () {
                addPenginapanInputRow({});
            });''',
    '''            $('#btn-add-penginapan').on('click', function () {
                addPenginapanInputRow({});
            });''' + ajax_logic
)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
