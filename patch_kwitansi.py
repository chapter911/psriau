import re

with open('app/Views/admin/laporan/cetak_kwitansi.php', 'r') as f:
    content = f.read()

# 1. Update CSS
content = content.replace('font-size: 12px;', 'font-size: 11px;')
content = content.replace('border: 1px solid #000;', 'border: 1.5px solid #000;')
# But only for rinci-table and kwitansi-header-table
# Actually, the original CSS has `border: 1px solid #000;` in a few places.
# Let's do it manually via regex
content = re.sub(r'(\.rinci-table th, \.rinci-table td {\s+)border: 1px solid #000;(\s+)padding: 5px;', r'\1border: 1.5px solid #000;\2padding: 3px 5px;', content)
content = re.sub(r'(\.kwitansi-header-table td {\s+)border: 1px solid #000;(\s+)padding: 4px;', r'\1border: 1.5px solid #000;\2padding: 2px 4px;', content)
content = content.replace('.kwitansi-header-table {\n            width: 50%;', '.kwitansi-header-table {\n            width: 45%;')

# 2. JUMLAH column formatting
# Biaya Transport Total
content = content.replace('''<td class="text-right" style="vertical-align: middle;">
                        Rp. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($calcTransport, 0, ',', '.'); ?>
                    </td>''', '''<td style="vertical-align: top;">
                        <div style="float: left;">Rp.</div>
                        <div style="float: right;"><?= number_format($calcTransport, 0, ',', '.'); ?></div>
                        <div style="clear: both;"></div>
                    </td>''')

# Uang Harian Total
content = content.replace('''<td class="text-right" style="vertical-align: middle;">
                        Rp. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($calcHarian, 0, ',', '.'); ?>
                    </td>''', '''<td style="vertical-align: top;">
                        <div style="float: left;">Rp.</div>
                        <div style="float: right;"><?= number_format($calcHarian, 0, ',', '.'); ?></div>
                        <div style="clear: both;"></div>
                    </td>''')

# Uang Penginapan Total
content = content.replace('''<td class="text-right" style="vertical-align: middle;">
                        Rp. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($calcPenginapan, 0, ',', '.'); ?>
                    </td>''', '''<td style="vertical-align: top;">
                        <div style="float: left;">Rp.</div>
                        <div style="float: right;"><?= number_format($calcPenginapan, 0, ',', '.'); ?></div>
                        <div style="clear: both;"></div>
                    </td>''')

# JUMLAH Total Row
content = content.replace('''<td class="font-weight-bold" style="border-bottom: 2px solid #000;">
                        Rp. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($calcTotal, 0, ',', '.'); ?>
                    </td>''', '''<td class="font-weight-bold" style="border-bottom: 1.5px solid #000;">
                        <div style="float: left;">Rp.</div>
                        <div style="float: right;"><?= number_format($calcTotal, 0, ',', '.'); ?></div>
                        <div style="clear: both;"></div>
                    </td>''')

# TERBILANG Row
content = content.replace('''<tr>
                    <td colspan="4">
                        <strong>TERBILANG :</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $terbilangText; ?>
                    </td>
                </tr>''', '''<tr>
                    <td colspan="3">
                        <strong>TERBILANG :</strong> &nbsp;&nbsp;&nbsp; <?= $terbilangText; ?>
                    </td>
                    <td style="background-color: #d3d3d3; -webkit-print-color-adjust: exact;"></td>
                </tr>''')

# 3. Footer RINCIAN BIAYA
content = content.replace('''Telah dibayar uang sebesar<br><br>
                    Rp. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($calcTotal, 0, ',', '.'); ?><br><br><br>
                    Bendahara Pengeluaran,<br>''', '''Telah dibayar uang sebesar<br><br>
                    <table style="width: 100%; border: none; margin: 0; padding: 0;">
                        <tr><td style="width: 10%; padding: 0;">Rp.</td><td style="width: 90%; text-align: left; padding: 0;"><?= number_format($calcTotal, 0, ',', '.'); ?></td></tr>
                    </table><br><br>
                    Pekanbaru, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $tanggalTtd; ?><br>
                    Bendahara Pengeluaran,<br>''')

content = content.replace('''Telah terima sejumlah uang sebesar:<br><br>
                    Rp. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($calcTotal, 0, ',', '.'); ?><br><br><br>''', '''Telah terima sejumlah uang sebesar:<br><br>
                    <table style="width: 100%; border: none; margin: 0; padding: 0;">
                        <tr><td style="width: 25%; text-align: right; padding: 0;">Rp.</td><td style="width: 75%; text-align: left; padding: 0; padding-left: 20px;"><?= number_format($calcTotal, 0, ',', '.'); ?></td></tr>
                    </table><br><br>''')

# 4. RAMPUNG Table - alignment of Rp
content = content.replace('''<table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 15%;">Rp</td>
                            <td style="width: 40%; text-align: right;"><?= number_format($calcTotal, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Rp</td>
                            <td style="text-align: right; border-bottom: 1px solid #000;">-</td>
                        </tr>
                        <tr>
                            <td>Rp</td>
                            <td style="text-align: right;">-</td>
                        </tr>
                    </table>''', '''<table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 20%; padding: 0;">Rp</td>
                            <td style="width: 80%; text-align: right; padding: 0;"><?= number_format($calcTotal, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 0;">Rp</td>
                            <td style="text-align: right; border-bottom: 1px solid #000; padding: 0;">-</td>
                        </tr>
                        <tr>
                            <td style="padding: 0;">Rp</td>
                            <td style="text-align: right; padding: 0;">-</td>
                        </tr>
                    </table>''')

# 5. KWITANSI PAGE formatting
# Wrap in border
content = content.replace('<div style="border-top: 1px solid #000; border-bottom: 2px solid #000; height: 1px; margin-bottom: 10px;"></div>', 
'''<div style="border-top: 1px solid #000; border-bottom: 2px solid #000; height: 1px;"></div>
<div style="border-left: 1.5px solid #000; border-right: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 15px; margin-top: 0; min-height: 800px; position: relative;">''')

content = content.replace('''        <?php if ($index < $totalPelaksana - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>''', '''        </div> <!-- End of kwitansi-wrapper -->
        
        <?php if ($index < $totalPelaksana - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>''')

# KWITANSI title
content = content.replace('''<div class="kwitansi-title">K U I T A N S I</div>''', '''<div class="kwitansi-title" style="margin-top: 40px; margin-bottom: 25px;">K U I T A N S I</div>''')

# KWITANSI Body Table spacing
content = content.replace('''            <tr>
                <td style="width: 25%;">Sudah di terima dari</td>
                <td style="width: 3%;">:</td>
                <td style="width: 72%;">PEJABAT PEMBUAT KOMITMEN PELAKSANAAN PRASARANA STRATEGIS</td>
            </tr>
            <tr>
                <td>Jumlah Uang</td>
                <td>:</td>
                <td class="font-weight-bold">Rp. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($calcTotal, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td>Terbilang</td>
                <td>:</td>
                <td><span style="font-style: italic; font-weight: bold;"><?= $terbilangText; ?></span></td>
            </tr>''', '''            <tr>
                <td style="width: 25%; padding-bottom: 8px;">Sudah di terima dari</td>
                <td style="width: 3%; padding-bottom: 8px;">:</td>
                <td style="width: 72%; padding-bottom: 8px;">PEJABAT PEMBUAT KOMITMEN PELAKSANAAN PRASARANA STRATEGIS</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px;">Jumlah Uang</td>
                <td style="padding-bottom: 8px;">:</td>
                <td class="font-weight-bold" style="padding-bottom: 8px;">
                    <span style="display: inline-block; width: 30px;">Rp.</span> 
                    <?= number_format($calcTotal, 0, ',', '.'); ?>
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px;">Terbilang</td>
                <td style="padding-bottom: 8px;">:</td>
                <td style="padding-bottom: 8px;"><span style="font-style: italic; font-weight: bold;"><?= $terbilangText; ?></span></td>
            </tr>''')

content = content.replace('''            <tr>
                <td colspan="3"><br><br>Berdasarkan SPD</td>
            </tr>''', '''            <tr>
                <td colspan="3"><br>Berdasarkan SPD</td>
            </tr>''')

# 6. Nested table inside RINCI table for items
# Wait, for items inside "UANG HARIAN" the nested table is fine, but maybe spacing can be fixed.
content = content.replace('''                                 <tr>
                                     <td style="width: 15%;">&nbsp; <?= $hd['days']; ?> hari</td>
                                     <td style="width: 5%;">x</td>
                                     <td style="width: 10%;">Rp</td>
                                     <td style="width: 25%; text-align: right;"><?= number_format($hd['rate'], 0, ',', '.'); ?></td>
                                     <td style="width: 10%; text-align: center;">Rp</td>
                                     <td style="width: 35%; text-align: right;"><?= number_format($hd['sub'], 0, ',', '.'); ?></td>
                                 </tr>''', '''                                 <tr>
                                     <td style="width: 15%;">&nbsp; <?= $hd['days']; ?> hari</td>
                                     <td style="width: 5%;">x</td>
                                     <td style="width: 10%;">Rp</td>
                                     <td style="width: 25%; text-align: right;"><?= number_format($hd['rate'], 0, ',', '.'); ?></td>
                                     <td style="width: 15%; text-align: right; padding-right: 10px !important;">Rp</td>
                                     <td style="width: 30%; text-align: right;"><?= number_format($hd['sub'], 0, ',', '.'); ?></td>
                                 </tr>''')

content = content.replace('''                                 <tr>
                                     <td style="width: 15%;">&nbsp; <?= $pd['nights']; ?> malam</td>
                                     <td style="width: 5%;">x</td>
                                     <td style="width: 10%;">Rp</td>
                                     <td style="width: 25%; text-align: right;"><?= number_format($pd['rate'], 0, ',', '.'); ?></td>
                                     <td style="width: 10%; text-align: center;">Rp</td>
                                     <td style="width: 35%; text-align: right;"><?= number_format($pd['sub'], 0, ',', '.'); ?></td>
                                 </tr>''', '''                                 <tr>
                                     <td style="width: 15%;">&nbsp; <?= $pd['nights']; ?> malam</td>
                                     <td style="width: 5%;">x</td>
                                     <td style="width: 10%;">Rp</td>
                                     <td style="width: 25%; text-align: right;"><?= number_format($pd['rate'], 0, ',', '.'); ?></td>
                                     <td style="width: 15%; text-align: right; padding-right: 10px !important;">Rp</td>
                                     <td style="width: 30%; text-align: right;"><?= number_format($pd['sub'], 0, ',', '.'); ?></td>
                                 </tr>''')

# Transport nested table
content = content.replace('''                                 <tr>
                                     <td style="width: 50%;"><?= $td['desc']; ?></td>
                                     <td style="width: 15%;">Rp.</td>
                                     <td style="width: 35%; text-align: right;"><?= number_format($td['sub'], 0, ',', '.'); ?></td>
                                 </tr>''', '''                                 <tr>
                                     <td style="width: 55%;"><?= $td['desc']; ?></td>
                                     <td style="width: 15%; text-align: right; padding-right: 10px !important;">Rp.</td>
                                     <td style="width: 30%; text-align: right;"><?= number_format($td['sub'], 0, ',', '.'); ?></td>
                                 </tr>''')

with open('app/Views/admin/laporan/cetak_kwitansi.php', 'w') as f:
    f.write(content)
