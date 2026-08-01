import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

# 1. Add `modal-dialog-scrollable` to the modal
content = content.replace(
    '<div class="modal-dialog modal-dialog-centered modal-xl" role="document">',
    '<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">'
)

# 2. Remove the buttons from Tab 1
tab1_buttons = """                            <div class="mt-4 pt-3 border-top text-right">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-success btn-sm font-weight-bold btn-save-tab" data-tab="tab1"><i class="fas fa-save mr-1"></i> Simpan SPT</button>
                            </div>"""
content = content.replace(tab1_buttons, "")

# 3. Remove the buttons from Tab 2 and add the unified modal-footer instead
tab2_buttons_old = """                            <div class="mt-4 pt-3 border-top text-right">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary btn-sm font-weight-bold btn-save-tab" data-tab="tab2"><i class="fas fa-save mr-1"></i> Simpan Rincian Biaya</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- modal footer removed since buttons are inside tabs -->"""

tab2_buttons_new = """                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2" style="border-top: 1px solid #e9eef5;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-sm font-weight-bold btn-save-tab" data-tab="tab1" id="btn-save-tab1"><i class="fas fa-save mr-1"></i> Simpan SPT</button>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold btn-save-tab" data-tab="tab2" id="btn-save-tab2" style="display:none;"><i class="fas fa-save mr-1"></i> Simpan Rincian Biaya</button>
                </div>"""
content = content.replace(tab2_buttons_old, tab2_buttons_new)

# 4. Add the tab switch logic to JS
js_logic = """
        // Toggle save buttons based on active tab
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).attr("href");
            if (target === '#tab-verifikasi') {
                $('#btn-save-tab1').show();
                $('#btn-save-tab2').hide();
            } else if (target === '#tab-biaya') {
                $('#btn-save-tab1').hide();
                $('#btn-save-tab2').show();
            }
        });

        // Handle AJAX submit per tab"""
content = content.replace("        // Handle AJAX submit per tab", js_logic)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
