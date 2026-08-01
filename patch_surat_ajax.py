import re

with open('app/Views/admin/laporan/surat_tugas.php', 'r') as f:
    content = f.read()

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
                        Swal.fire('Berhasil', response.message, 'success');
                        dt.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal', response.message || 'Terjadi kesalahan', 'error');
                    }
                },
                error: function() {
                    $btn.html(originalText).prop('disabled', false);
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data', 'error');
                }
            });
        });
"""

content = content.replace(
    "// Cetak Berdasarkan Periode handler",
    ajax_logic + "\n        // Cetak Berdasarkan Periode handler"
)

with open('app/Views/admin/laporan/surat_tugas.php', 'w') as f:
    f.write(content)
