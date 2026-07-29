<?php helper('custom'); helper('pdf_helper');
$data = $data ?? [];
$periodeMulai = tanggal_indonesia((string) ($data['periode_mulai'] ?? ''));
$periodeSelesai = tanggal_indonesia((string) ($data['periode_selesai'] ?? ''));
$laporan = (string) ($data['laporan_hasil'] ?? '');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { size: A4; margin: 1.3cm 1.5cm 1.5cm 1.5cm; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; }
  .center { text-align: center; }
  .bold { font-weight: 700; }
  .main-table { width:100%; border-collapse: collapse; border:1px solid #000; }
  .main-table td { border:1px solid #000; padding:6px; vertical-align: top; }
  .label { width:40%; }
  .colon { width:3%; text-align:center; }
  .value { width:57%; }
  .report-wrapper { border:1px solid #000; margin-top:6px; }
  .report-row { border-top:1px solid #000; padding:8px; min-height:26px; }
  .report-title { padding:8px; font-weight:700; }
  .page-break { page-break-before: always; }
</style>
</head>
<body>
  <div class="center">
    <?= kop_surat_img_tag('', 'width:100%; max-height:120px; object-fit:contain;', 'Kop Surat'); ?>
    <h2 class="bold">LAPORAN PELAKSANAAN PERJALANAN DINAS</h2>
  </div>

  <table class="main-table">
    <tr>
      <td class="label">Nomor Surat Tugas</td>
      <td class="colon">:</td>
      <td class="value"><?= esc((string) ($data['nomor_surat_tugas'] ?? '-')); ?></td>
    </tr>
    <tr>
      <td class="label">Periode Perjalanan Dinas</td>
      <td class="colon">:</td>
      <td class="value"><?= esc($periodeMulai . ' s.d ' . $periodeSelesai); ?></td>
    </tr>
    <tr>
      <td class="label">Kota/Kab. Tujuan Perjalanan Dinas</td>
      <td class="colon">:</td>
      <td class="value"><?= esc((string) ($data['kota_tujuan'] ?? '-')); ?></td>
    </tr>
    <tr>
      <td class="label">Tujuan Perjalanan Dinas</td>
      <td class="colon">:</td>
      <td class="value"><?= nl2br(esc((string) ($data['tujuan'] ?? '-'))); ?></td>
    </tr>
    <tr>
      <td class="label">Sasaran Perjalanan Dinas</td>
      <td class="colon">:</td>
      <td class="value"><?= nl2br(esc((string) ($data['sasaran'] ?? '-'))); ?></td>
    </tr>
  </table>

  <?php
    // Split laporan into blocks and render as rows inside a bordered wrapper
    $blocks = pdf_split_blocks($laporan);
    $minRows = 16; $maxRows = 40;
    $used = count($blocks);
    $total = max($minRows, min($maxRows, $used + 6));
  ?>
  <div class="report-wrapper">
    <div class="report-row report-title">Laporan Hasil Perjalanan Dinas</div>
    <?php foreach ($blocks as $b): ?>
      <div class="report-row"><?= $b; ?></div>
    <?php endforeach; ?>
    <?php for ($i = 0; $i < ($total - $used); $i++): ?>
      <div class="report-row">&nbsp;</div>
    <?php endfor; ?>
  </div>

  <div class="page-break"></div>

  <div style="margin-top:12px">
    <div style="float:left; width:50%; text-align:center">
      <div style="height:80px"></div>
      <?php $pel = $data['pelaksana'][0] ?? ($data['creator_pegawai'] ?? []); ?>
      <div class="bold"><?= esc($pel['nama'] ?? '-'); ?></div>
      <?php if (should_show_nip($pel) && !empty($pel['nip'])): ?>
        <div>NIP. <?= esc($pel['nip']); ?></div>
      <?php endif; ?>
    </div>
    <div style="float:right; width:50%; text-align:center">
      <div style="height:80px"></div>
      <?php $dik = $data['diketahui_oleh'] ?? []; ?>
      <div class="bold"><?= esc($dik['nama'] ?? '-'); ?></div>
      <?php if (should_show_nip($dik) && !empty($dik['nip'])): ?>
        <div>NIP. <?= esc($dik['nip']); ?></div>
      <?php endif; ?>
    </div>
    <div style="clear:both"></div>
  </div>

</body>
</html>
