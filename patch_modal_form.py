import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

# Current structure:
old_structure = """        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" id="modalVerifyTitle">
                    <i class="fas fa-check-double text-success mr-2"></i>Verifikasi Laporan Perjadin & SPT
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-verify-spt" method="post" action="">"""

new_structure = """        <form id="form-verify-spt" method="post" action="" class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header bg-light py-3" style="border-bottom: 1px solid #e9eef5;">
                <h5 class="modal-title font-weight-bold text-dark" id="modalVerifyTitle">
                    <i class="fas fa-check-double text-success mr-2"></i>Verifikasi Laporan Perjadin & SPT
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>"""

content = content.replace(old_structure, new_structure)

# Remove the old </form> closing tag and replace it properly at the end of the modal-content
old_footer = """                    <button type="button" class="btn btn-success btn-sm font-weight-bold btn-save-tab" data-tab="tab1" id="btn-save-tab1"><i class="fas fa-save mr-1"></i> Simpan SPT</button>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold btn-save-tab" data-tab="tab2" id="btn-save-tab2" style="display:none;"><i class="fas fa-save mr-1"></i> Simpan Rincian Biaya</button>
                </div>
            </form>
        </div>
    </div>
</div>"""

new_footer = """                    <button type="button" class="btn btn-success btn-sm font-weight-bold btn-save-tab" data-tab="tab1" id="btn-save-tab1"><i class="fas fa-save mr-1"></i> Simpan SPT</button>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold btn-save-tab" data-tab="tab2" id="btn-save-tab2" style="display:none;"><i class="fas fa-save mr-1"></i> Simpan Rincian Biaya</button>
                </div>
        </form>
    </div>
</div>"""

content = content.replace(old_footer, new_footer)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
