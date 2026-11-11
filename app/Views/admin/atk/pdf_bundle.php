<?php
use App\Libraries\AtkExcelExporter;

$p           = $pengadaan;
$bulanNama   = AtkExcelExporter::bulanNama((int) ($p['bulan'] ?? 1));
$tahun       = (int) ($p['tahun_anggaran'] ?? 2026);
$kategori    = (string) ($p['kategori'] ?? 'ATK');
$jenisLabel  = $kategori === 'Supplies Komputer' ? 'Supplies Komputer' : 'Alat Tulis Kantor (ATK)';
$kegiatan    = $p['kegiatan_label'] ?: 'Dukungan Kegiatan Prasarana Strategis';
$showAll     = ($jenis === 'all');

$tglUsulan   = AtkExcelExporter::tanggalIndo($p['tgl_usulan'] ?? null) ?: ($bulanNama . ' ' . $tahun);
$tglPes      = AtkExcelExporter::tanggalIndo($p['tgl_pesanan'] ?? null) ?: ($bulanNama . ' ' . $tahun);
$tglBatas    = AtkExcelExporter::tanggalIndo($p['tgl_batas_terima'] ?? null) ?: '-';
$tglBastp    = AtkExcelExporter::tanggalIndo($p['tgl_bastp'] ?? null) ?: ($bulanNama . ' ' . $tahun);
$tglFaktur   = AtkExcelExporter::tanggalIndo($p['tgl_faktur'] ?? null) ?: ($bulanNama . ' ' . $tahun);
$tglKwi      = AtkExcelExporter::tanggalIndo($p['tgl_kuitansi'] ?? null) ?: ($bulanNama . ' ' . $tahun);
$tglSlash    = AtkExcelExporter::tanggalSlash($p['tgl_bastp'] ?? null) ?: '';
$tglLunasRaw = AtkExcelExporter::tanggalSlash($p['tgl_lunas_bayar'] ?? null);
$tglLunas    = $tglLunasRaw !== '' ? esc($tglLunasRaw) : '...... / ...... / ' . $tahun;
$kalimatBast = AtkExcelExporter::tanggalKalimatBast($p['tgl_bastp'] ?? null);

$ppkNama  = $p['ppk_nama'] ?: 'NURHIDAYAT NUGROHO, S.Ars.';
$ppkNip   = $p['ppk_nip'] ?: '199012212018021001';
$benNama  = $p['bendahara_nama'] ?: 'KH. SRI HANDAYANI, S.Si., M.T.';
$benNip   = $p['bendahara_nip'] ?: '198204022014122002';
$dirNama  = $penyedia['nama_direktur'] ?? 'ILHAM RAMAPALA';
$dirJab   = $penyedia['jabatan_pimpinan'] ?? 'Direktur';
$compNama = $penyedia['nama_perusahaan'] ?? 'CV. SINAR CAHAYA LESTARI';
$compAddr = ($penyedia['alamat'] ?? 'Jalan Bukit Barisan Ujung No. 97 Tangkerang Timur') . ' - ' . ($penyedia['kota'] ?? 'Pekanbaru');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dokumen SPJ ATK &amp; Supplies</title>
<style>
    @page { margin: 10mm 14mm; }
    body {
        font-family: "DejaVu Sans", Arial, sans-serif;
        font-size: 8.8pt;
        color: #111;
        line-height: 1.28;
    }
    p { margin: 0 0 4px 0; }
    .doc-section { page-break-inside: avoid; }
    .page-break { page-break-after: always; }
    .kop {
        width: 100%;
        margin-bottom: 6px;
        border-bottom: 2px solid #000;
        padding-bottom: 4px;
    }
    .kop img {
        width: 100%;
        max-height: 68px;
        object-fit: contain;
    }
    .kop-fallback {
        text-align: center;
        font-weight: bold;
        font-size: 10pt;
    }
    .doc-title {
        text-align: center;
        font-weight: bold;
        font-size: 10.5pt;
        text-decoration: underline;
        margin-top: 3px;
    }
    .doc-sub {
        text-align: center;
        font-size: 8.8pt;
        margin-bottom: 8px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table.tbl-border th,
    table.tbl-border td {
        border: 1px solid #000;
        padding: 2.5px 5px;
        font-size: 8.4pt;
        vertical-align: middle;
    }
    table.tbl-border th {
        background: #efefef;
        text-align: center;
        font-weight: bold;
    }
    table.tbl-lamp th,
    table.tbl-lamp td {
        padding: 2.5px 4px;
        font-size: 8pt;
    }
    table.tbl-plain td {
        padding: 1.5px 3px;
        vertical-align: top;
    }
    .sig-block {
        page-break-inside: avoid;
        margin-top: 8px;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .nowrap { white-space: nowrap; }
    .bold { font-weight: bold; }
    .underline { text-decoration: underline; }
    .italic { font-style: italic; }
    .ttd-space { height: 40px; }
    .kwi-box {
        border: 2px solid #000;
        padding: 12px 14px;
        page-break-inside: avoid;
    }
</style>
</head>
<body>

<?php
$renderKop = static function () use ($kopDataUri): void {
    echo '<div class="kop">';
    if ($kopDataUri !== '') {
        echo '<img src="' . $kopDataUri . '" alt="Kop Surat">';
    } else {
        echo '<div class="kop-fallback">KEMENTERIAN PEKERJAAN UMUM<br>SATUAN KERJA PELAKSANAAN PRASARANA STRATEGIS RIAU<br><small>Jalan Datuk Setia Maharaja No. 15, Tangkerang Labuai, Bukit Raya, Kota Pekanbaru</small></div>';
    }
    echo '</div>';
};

$sectionsRendered = 0;
$breakIfNeeded = static function () use (&$sectionsRendered): void {
    if ($sectionsRendered > 0) {
        echo '<div class="page-break"></div>';
    }
    $sectionsRendered++;
};
?>

<?php if ($showAll || $jenis === 'usulan_ppk'): ?>
    <?php $breakIfNeeded(); ?>
    <?php $renderKop(); ?>
    <table class="tbl-plain" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 12%;">Nomor</td>
            <td style="width: 52%;">: <?= esc($p['no_usulan'] ?: '-'); ?></td>
            <td class="text-right nowrap">Pekanbaru, <?= esc($tglUsulan); ?></td>
        </tr>
        <tr><td>Sifat</td><td colspan="2">: Biasa</td></tr>
        <tr><td>Lampiran</td><td colspan="2">: -</td></tr>
        <tr><td>Hal</td><td colspan="2" class="bold">: Usulan Kebutuhan <?= esc($jenisLabel); ?> Bulan <?= esc($bulanNama); ?> <?= $tahun; ?></td></tr>
    </table>

    <p style="margin-bottom: 6px;">
        Yth.<br>
        <strong>Bendahara Pengeluaran</strong><br>
        Satuan Kerja Pelaksanaan Prasarana Strategis Riau<br>
        di Tempat
    </p>

    <p>Dengan hormat,</p>
    <p style="text-align: justify;">
        Sehubungan dengan kebutuhan operasional dan kelancaran pelaksanaan kegiatan <?= esc($kegiatan); ?> pada Satuan Kerja Pelaksanaan Prasarana Strategis Riau bulan <?= esc($bulanNama); ?> <?= $tahun; ?>, bersama ini kami selaku Pejabat Pembuat Komitmen (PPK) mengusulkan pengadaan <?= esc($jenisLabel); ?> untuk menunjang pelaksanaan tugas dan kegiatan administrasi.
    </p>
    <p>Adapun rincian kebutuhan <?= esc($jenisLabel); ?> yang diusulkan adalah sebagai berikut:</p>

    <table class="tbl-border" style="margin-bottom: 8px;">
        <thead>
            <tr>
                <th style="width: 32px;">No.</th>
                <th>Nama Barang</th>
                <th style="width: 70px;">Volume</th>
                <th style="width: 95px;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $idx => $it): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1; ?></td>
                    <td><?= esc($it['nama_barang']); ?></td>
                    <td class="text-center"><?= number_format((float) $it['volume'], 0, ',', '.'); ?></td>
                    <td class="text-center"><?= esc($it['satuan']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="text-align: justify;">
        Demikian usulan kebutuhan <?= esc($jenisLabel); ?> ini disampaikan untuk dapat dipertimbangkan dan ditindaklanjuti sesuai dengan ketersediaan anggaran serta ketentuan yang berlaku. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
    </p>

    <table class="tbl-plain sig-block">
        <tr>
            <td style="width: 56%;"></td>
            <td>
                Pejabat Pembuat Komitmen<br>
                Pelaksanaan Prasarana Strategis Riau
                <div class="ttd-space"></div>
                <span class="bold underline"><?= esc($ppkNama); ?></span><br>
                NIP <?= esc($ppkNip); ?>
            </td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($showAll || $jenis === 'spb'): ?>
    <?php $breakIfNeeded(); ?>
    <?php $renderKop(); ?>
    <div class="doc-title">SURAT PESANAN BARANG</div>
    <div class="doc-sub">No. <?= esc($p['no_pesanan'] ?: '      /PES/SATKER/PPS-RIAU/' . $tahun); ?></div>

    <p style="margin-bottom: 8px;">
        Kepada Yth :<br>
        <strong><?= esc($compNama); ?></strong><br>
        <?= esc($penyedia['alamat'] ?? ''); ?><br>
        di-<br>
        <?= esc($penyedia['kota'] ?? 'Pekanbaru'); ?>
    </p>

    <p class="bold" style="margin-bottom: 4px;">Barang Yang dipesan :</p>
    <table class="tbl-border" style="margin-bottom: 8px;">
        <thead>
            <tr>
                <th style="width: 32px;">No.</th>
                <th>Nama Barang</th>
                <th style="width: 70px;">Banyaknya</th>
                <th style="width: 90px;">Satuan</th>
                <th style="width: 95px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $idx => $it): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1; ?></td>
                    <td><?= esc($it['nama_barang']); ?></td>
                    <td class="text-center"><?= number_format((float) $it['volume'], 0, ',', '.'); ?></td>
                    <td class="text-center"><?= esc($it['satuan']); ?></td>
                    <td><?= esc($it['keterangan'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-bottom: 8px;">
        - Barang tersebut sudah kami terima selambat-lambatnya tanggal <?= esc($tglBatas); ?><br>
        - Keterlambatan dikenakan denda <?= number_format((float) ($p['persen_denda_harian'] ?? 2), 0); ?>% dari harga pesanan/ hari
    </p>

    <table class="tbl-plain sig-block">
        <tr>
            <td style="width: 56%;"></td>
            <td>
                Pekanbaru, <?= esc($tglPes); ?><br>
                PEMESAN :<br>
                Pejabat Pembuat Komitmen<br>
                Pelaksanaan Prasarana Strategis
                <div class="ttd-space"></div>
                <span class="bold underline"><?= esc($ppkNama); ?></span><br>
                NIP <?= esc($ppkNip); ?>
            </td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($showAll || $jenis === 'bast'): ?>
    <?php $breakIfNeeded(); ?>
    <?php $renderKop(); ?>
    <div class="doc-title">BERITA ACARA SERAH TERIMA DAN PEMERIKSAAN BARANG</div>
    <div class="doc-sub">Nomor : <?= esc($p['no_bastp'] ?: '        /BASTP/SATKER/PPS-RIAU/' . $tahun); ?></div>

    <p style="text-align: justify;">
        <?= esc($kalimatBast); ?>, bertempat di Pekanbaru yang bertanda tangan dibawah ini :
    </p>

    <table class="tbl-plain" style="margin-bottom: 8px;">
        <tr><td style="width: 85px;">Nama</td><td style="width: 10px;">:</td><td class="bold"><?= esc($ppkNama); ?></td></tr>
        <tr><td>Jabatan</td><td>:</td><td>Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis</td></tr>
        <tr><td>NIP</td><td>:</td><td><?= esc($ppkNip); ?></td></tr>
        <tr><td>Alamat</td><td>:</td><td>Jalan Datuk Setia Maharaja No. 15, Tangkerang Labuai Bukit Raya Kota Pekanbaru</td></tr>
        <tr><td colspan="3" class="bold" style="padding-top: 3px;">Yang selanjutnya disebut sebagai PIHAK PERTAMA</td></tr>
    </table>

    <table class="tbl-plain" style="margin-bottom: 8px;">
        <tr><td style="width: 85px;">Nama</td><td style="width: 10px;">:</td><td class="bold"><?= esc($dirNama); ?></td></tr>
        <tr><td>Jabatan</td><td>:</td><td><?= esc($dirJab . ' ' . $compNama); ?></td></tr>
        <tr><td>Alamat</td><td>:</td><td><?= esc($compAddr); ?></td></tr>
        <tr><td colspan="3" class="bold" style="padding-top: 3px;">Yang selanjutnya disebut sebagai PIHAK KEDUA</td></tr>
    </table>

    <p style="text-align: justify;">
        PIHAK KEDUA telah menyerahkan kepada PIHAK PERTAMA dan PIHAK PERTAMA telah menerima dan memeriksa penyerahan dari PIHAK KEDUA barang-barang persediaan dengan Surat Pesanan Barang Nomor : <?= esc($p['no_pesanan'] ?: '        /PES/SATKER/PPS-RIAU/' . $tahun); ?> dengan rincian sebagaimana terlampir pada Berita Acara ini.
    </p>
    <p style="text-align: justify;">
        Dengan ditanda tanganinya Berita Acara ini, maka segala tanggungjawab terhadap penyimpangan, pengamanan, dan penatausahaan barang-barang persediaan tersebut berada pada PIHAK PERTAMA.
    </p>
    <p>Demikian berita acara ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

    <table class="tbl-plain sig-block">
        <tr>
            <td style="width: 50%;"><strong>PIHAK KEDUA</strong></td>
            <td style="width: 50%;"><strong>PIHAK PERTAMA</strong></td>
        </tr>
        <tr>
            <td><?= esc($compNama); ?></td>
            <td>PEJABAT PEMBUAT KOMITMEN<br>PELAKSANAAN PRASARANA STRATEGIS</td>
        </tr>
        <tr>
            <td><div class="ttd-space"></div></td>
            <td><div class="ttd-space"></div></td>
        </tr>
        <tr>
            <td><span class="bold underline"><?= esc($dirNama); ?></span><br><?= esc($dirJab); ?></td>
            <td><span class="bold underline"><?= esc($ppkNama); ?></span><br>NIP <?= esc($ppkNip); ?></td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($showAll || $jenis === 'lamp_bastp'): ?>
    <?php $breakIfNeeded(); ?>
    <table class="tbl-plain" style="margin-bottom: 8px;">
        <tr>
            <td style="width: 46%;"></td>
            <td>
                <strong>Lampiran Berita Acara Serah Terima Barang Persediaan</strong><br>
                Nomor : <?= esc($p['no_bastp'] ?: '        /BASTP/SATKER/PPS-RIAU/' . $tahun); ?><br>
                Tanggal : <?= esc($tglBastp); ?>
            </td>
        </tr>
    </table>

    <table class="tbl-border tbl-lamp" style="margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="width: 24px;">No</th>
                <th style="width: 50px;">No. Bukti</th>
                <th style="width: 58px;">Tgl. Bukti</th>
                <th>Uraian Barang</th>
                <th style="width: 40px;">Kondisi</th>
                <th style="width: 62px;">Harga</th>
                <th style="width: 30px;">Jlh</th>
                <th style="width: 72px;">Satuan</th>
                <th style="width: 30px;">Ket</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $idx => $it): ?>
                <tr>
                    <td class="text-center"><?= $idx === 0 ? '1.' : ''; ?></td>
                    <td class="text-center"><?= $idx === 0 ? esc($p['no_bukti_kuitansi'] ?: '') : ''; ?></td>
                    <td class="text-center nowrap"><?= $idx === 0 ? esc($tglSlash) : ''; ?></td>
                    <td><?= esc($it['nama_barang']); ?></td>
                    <td class="text-center"><?= esc($it['kondisi_barang'] ?? 'Baik'); ?></td>
                    <td class="text-right nowrap"><?= number_format((float) $it['harga_satuan'], 0, ',', '.'); ?></td>
                    <td class="text-center"><?= number_format((float) $it['volume'], 0, ',', '.'); ?></td>
                    <td class="text-center"><?= esc($it['satuan']); ?></td>
                    <td><?= esc($it['keterangan'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="tbl-plain sig-block">
        <tr>
            <td style="width: 50%;"><strong>PIHAK KEDUA</strong></td>
            <td style="width: 50%;"><strong>PIHAK PERTAMA</strong></td>
        </tr>
        <tr>
            <td><?= esc($compNama); ?></td>
            <td>PEJABAT PEMBUAT KOMITMEN<br>PELAKSANAAN PRASARANA STRATEGIS</td>
        </tr>
        <tr>
            <td><div class="ttd-space"></div></td>
            <td><div class="ttd-space"></div></td>
        </tr>
        <tr>
            <td><span class="bold underline"><?= esc($dirNama); ?></span><br><?= esc($dirJab); ?></td>
            <td><span class="bold underline"><?= esc($ppkNama); ?></span><br>NIP <?= esc($ppkNip); ?></td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($showAll || $jenis === 'faktur'): ?>
    <?php $breakIfNeeded(); ?>
    <div style="border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 10px;">
        <div style="font-size: 12pt; font-weight: bold;"><?= esc($compNama); ?></div>
        <div style="font-size: 8.5pt;"><?= esc($compAddr); ?> | HP/WA: <?= esc($penyedia['telepon'] ?? '-'); ?></div>
    </div>

    <table class="tbl-plain" style="margin-bottom: 8px;">
        <tr>
            <td><span class="bold underline" style="font-size: 10.5pt;">FAKTUR PENAGIHAN</span><?= ! empty($p['no_faktur']) ? ' — No: ' . esc($p['no_faktur']) : ''; ?></td>
            <td class="text-right nowrap">Pekanbaru, <?= esc($tglFaktur); ?></td>
        </tr>
        <tr>
            <td colspan="2">Kepada Yth: <strong>Pejabat Pembuat Komitmen Satker Pelaksanaan Prasarana Strategis Riau</strong></td>
        </tr>
    </table>

    <table class="tbl-border" style="margin-bottom: 8px;">
        <thead>
            <tr>
                <th style="width: 58px;">Banyaknya</th>
                <th style="width: 80px;">Satuan</th>
                <th>Nama Barang</th>
                <th style="width: 90px;">Harga Satuan</th>
                <th style="width: 98px;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td class="text-center"><?= number_format((float) $it['volume'], 0, ',', '.'); ?></td>
                    <td class="text-center"><?= esc($it['satuan']); ?></td>
                    <td><?= esc($it['nama_barang']); ?></td>
                    <td class="text-right nowrap">Rp <?= number_format((float) $it['harga_satuan'], 0, ',', '.'); ?></td>
                    <td class="text-right nowrap">Rp <?= number_format((float) $it['subtotal'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">JUMLAH TOTAL</th>
                <th class="text-right nowrap">Rp <?= number_format((float) $p['total_nilai'], 0, ',', '.'); ?></th>
            </tr>
        </tfoot>
    </table>

    <p class="italic bold">Terbilang : <?= esc($p['terbilang']); ?></p>

    <table class="tbl-plain sig-block">
        <tr>
            <td style="width: 60%;"></td>
            <td class="text-center">
                Hormat Kami,<br>
                <?= esc($compNama); ?>
                <div class="ttd-space"></div>
                <span class="bold underline"><?= esc($dirNama); ?></span><br>
                <?= esc($dirJab); ?>
            </td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($showAll || $jenis === 'kuitansi_ls'): ?>
    <?php $breakIfNeeded(); ?>
    <div class="kwi-box">
        <div class="doc-title" style="margin-bottom: 10px;">KUITANSI / BUKTI PEMBAYARAN</div>

        <table class="tbl-plain" style="margin-bottom: 10px;">
            <tr>
                <td style="width: 44%;"></td>
                <td class="nowrap" style="width: 19%;">Tahun Anggaran</td>
                <td class="nowrap" style="width: 37%;">: <?= $tahun; ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="nowrap">Nomor Bukti</td>
                <td class="nowrap">: <?= esc($p['no_bukti_kuitansi'] ?: '........................'); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="nowrap">Mata Anggaran</td>
                <td class="nowrap">: <?= esc($p['kode_mata_anggaran'] ?: '7987.FAD.002.101.A.521211'); ?></td>
            </tr>
        </table>

        <table class="tbl-plain" style="margin-bottom: 12px;">
            <tr>
                <td class="nowrap" style="width: 138px;">SUDAH TERIMA DARI</td>
                <td style="width: 10px;">:</td>
                <td>Pejabat Pembuat Komitmen Pelaksanaan Prasarana Strategis</td>
            </tr>
            <tr>
                <td class="nowrap">JUMLAH UANG</td>
                <td>:</td>
                <td class="bold" style="font-size: 10.5pt;">Rp. <?= number_format((float) $p['total_nilai'], 0, ',', '.'); ?> ,-</td>
            </tr>
            <tr>
                <td class="nowrap">TERBILANG</td>
                <td>:</td>
                <td class="italic bold"><?= esc($p['terbilang']); ?></td>
            </tr>
            <tr>
                <td class="nowrap">UNTUK PEMBAYARAN</td>
                <td>:</td>
                <td style="text-align: justify;">
                    Biaya Pembelian <?= esc($jenisLabel); ?> untuk keperluan Administrasi <?= esc($kegiatan); ?> pada Satuan Kerja Pelaksanaan Prasarana Strategis Riau bulan <?= esc($bulanNama); ?> <?= $tahun; ?>, sebagaimana faktur terlampir.
                </td>
            </tr>
        </table>

        <table class="tbl-plain" style="margin-bottom: 14px;">
            <tr>
                <td style="width: 55%;"></td>
                <td>
                    Pekanbaru, <?= esc($tglKwi); ?><br>
                    Yang menerima uang,<br>
                    <?= esc($compNama); ?>
                    <div class="ttd-space"></div>
                    <span class="bold underline"><?= esc($dirNama); ?></span><br>
                    <?= esc($dirJab); ?>
                </td>
            </tr>
        </table>

        <table class="tbl-plain sig-block" style="border-top: 1px solid #000; padding-top: 8px;">
            <tr>
                <td style="width: 52%;">
                    Yang Memerintahkan Pembayaran :<br>
                    Pejabat Pembuat Komitmen<br>
                    Pelaksanaan Prasarana Strategis
                </td>
                <td style="width: 48%;">
                    Setuju dan Lunas dibayar Tgl. <?= $tglLunas; ?><br>
                    Bendahara Pengeluaran,
                </td>
            </tr>
            <tr>
                <td><div class="ttd-space"></div></td>
                <td><div class="ttd-space"></div></td>
            </tr>
            <tr>
                <td>
                    <span class="bold underline"><?= esc($ppkNama); ?></span><br>
                    NIP <?= esc($ppkNip); ?>
                </td>
                <td>
                    <span class="bold underline"><?= esc($benNama); ?></span><br>
                    NIP <?= esc($benNip); ?>
                </td>
            </tr>
        </table>
    </div>
<?php endif; ?>

</body>
</html>
