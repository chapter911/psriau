<?php
$item = [
    'id' => 1,
    'provinsi_kode' => '14',
    'satuan' => 'Orang',
    'besaran' => '100000',
    'berlaku_mulai' => '2024-01-01',
    'berlaku_hingga' => null,
    'is_active' => 1
];
?>
<button type="button"
    class="btn btn-outline-primary btn-xs px-2 py-1"
    data-toggle="modal"
    data-target="#modal-ubah"
    data-id="<?= htmlspecialchars((string) ($item['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
    data-provinsi="<?= htmlspecialchars((string) ($item['provinsi_kode'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
    data-satuan="<?= htmlspecialchars((string) ($item['satuan'] ?? 'Orang/Kali'), ENT_QUOTES, 'UTF-8'); ?>"
    data-besaran="<?= htmlspecialchars((string) ($item['besaran'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>"
    data-mulai="<?= htmlspecialchars((string) ($item['berlaku_mulai'] ?? '2024-01-01'), ENT_QUOTES, 'UTF-8'); ?>"
    data-hingga="<?= htmlspecialchars((string) ($item['berlaku_hingga'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
    data-status="<?= htmlspecialchars((string) ($item['is_active'] ?? '1'), ENT_QUOTES, 'UTF-8'); ?>"
    style="border-radius: 4px;" title="Edit">
    <i class="fas fa-pen mr-1"></i>Edit
</button>
