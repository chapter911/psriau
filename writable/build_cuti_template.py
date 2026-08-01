import zipfile
import xml.etree.ElementTree as ET
import os

ns = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
ET.register_namespace('w', ns['w'])

src_docx = 'do_not_upload/form Surat Cuti.docx'
target_dir = 'app/Views/admin/surat'
os.makedirs(target_dir, exist_ok=True)
target_docx = os.path.join(target_dir, 'form_surat_cuti_template.docx')

with zipfile.ZipFile(src_docx, 'r') as z_in:
    root = ET.fromstring(z_in.read('word/document.xml'))

def set_cell_text(cell, text, align=None):
    for child in list(cell):
        if child.tag.endswith('p'):
            cell.remove(child)
            
    p = ET.SubElement(cell, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p')
    pPr = ET.SubElement(p, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}pPr')
    if align:
        jc = ET.SubElement(pPr, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}jc')
        jc.set('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}val', align)
        
    new_r = ET.SubElement(p, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}r')
    new_t = ET.SubElement(new_r, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t')
    new_t.text = text

def set_sig_cell(cell, title, name, nip):
    for child in list(cell):
        if child.tag.endswith('p'):
            cell.remove(child)

    p1 = ET.SubElement(cell, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p')
    pPr1 = ET.SubElement(p1, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}pPr')
    ET.SubElement(pPr1, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}jc').set('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}val', 'center')
    r1 = ET.SubElement(p1, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}r')
    t1 = ET.SubElement(r1, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t')
    t1.text = title

    for _ in range(3):
        p_sp = ET.SubElement(cell, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p')
        pPr_sp = ET.SubElement(p_sp, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}pPr')
        ET.SubElement(pPr_sp, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}jc').set('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}val', 'center')

    p2 = ET.SubElement(cell, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p')
    pPr2 = ET.SubElement(p2, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}pPr')
    ET.SubElement(pPr2, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}jc').set('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}val', 'center')
    r2 = ET.SubElement(p2, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}r')
    rPr2 = ET.SubElement(r2, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}rPr')
    ET.SubElement(rPr2, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}b')
    ET.SubElement(rPr2, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}u').set('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}val', 'single')
    t2 = ET.SubElement(r2, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t')
    t2.text = name

    p3 = ET.SubElement(cell, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p')
    pPr3 = ET.SubElement(p3, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}pPr')
    ET.SubElement(pPr3, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}jc').set('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}val', 'center')
    r3 = ET.SubElement(p3, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}r')
    t3 = ET.SubElement(r3, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t')
    t3.text = nip

tables = root.findall('.//w:tbl', ns)

# --- Table 0: DATA PEGAWAI ---
t0_rows = tables[0].findall('.//w:tr', ns)
set_cell_text(t0_rows[1].findall('w:tc', ns)[1], '${nama}')
set_cell_text(t0_rows[1].findall('w:tc', ns)[3], '${nip}')
set_cell_text(t0_rows[2].findall('w:tc', ns)[1], '${jabatan}')
set_cell_text(t0_rows[2].findall('w:tc', ns)[3], '${masa_kerja}')
set_cell_text(t0_rows[3].findall('w:tc', ns)[1], '${unit_kerja}')

# --- Table 1: JENIS CUTI ---
t1_rows = tables[1].findall('.//w:tr', ns)
set_cell_text(t1_rows[1].findall('w:tc', ns)[1], '${v_ct}', 'center')
set_cell_text(t1_rows[1].findall('w:tc', ns)[3], '${v_cb}', 'center')
set_cell_text(t1_rows[2].findall('w:tc', ns)[1], '${v_cs}', 'center')
set_cell_text(t1_rows[2].findall('w:tc', ns)[3], '${v_cm}', 'center')
set_cell_text(t1_rows[3].findall('w:tc', ns)[1], '${v_cap}', 'center')
set_cell_text(t1_rows[3].findall('w:tc', ns)[3], '${v_cltn}', 'center')

# --- Table 2: ALASAN CUTI ---
t2_rows = tables[2].findall('.//w:tr', ns)
set_cell_text(t2_rows[1].findall('w:tc', ns)[0], '${alasan_cuti}')

# --- Table 3: LAMANYA CUTI ---
t3_rows = tables[3].findall('.//w:tr', ns)
set_cell_text(t3_rows[1].findall('w:tc', ns)[1], '${lama_cuti}', 'center')
set_cell_text(t3_rows[1].findall('w:tc', ns)[3], '${tanggal_mulai}', 'center')
set_cell_text(t3_rows[1].findall('w:tc', ns)[5], '${tanggal_selesai}', 'center')

# --- Table 4: CATATAN CUTI ---
t4_rows = tables[4].findall('.//w:tr', ns)
set_cell_text(t4_rows[3].findall('w:tc', ns)[0], '${catatan_tahun}', 'center')
set_cell_text(t4_rows[3].findall('w:tc', ns)[1], '${catatan_cuti_n}', 'center')
set_cell_text(t4_rows[3].findall('w:tc', ns)[2], '${catatan_cuti_keterangan}')

# --- Table 5: ALAMAT SELAMA CUTI ---
t5_rows = tables[5].findall('.//w:tr', ns)
set_cell_text(t5_rows[1].findall('w:tc', ns)[0], '${alamat_selama_cuti}')
set_cell_text(t5_rows[1].findall('w:tc', ns)[2], '${telepon}')
sig_c = t5_rows[2].findall('w:tc', ns)[1]
set_sig_cell(sig_c, 'Hormat saya,', '${nama}', 'NIP. ${nip}')

# --- Table 6: PERTIMBANGAN ATASAN ---
t6_rows = tables[6].findall('.//w:tr', ns)
set_cell_text(t6_rows[2].findall('w:tc', ns)[0], '${v_atasan_setuju}', 'center')
set_cell_text(t6_rows[2].findall('w:tc', ns)[1], '${v_atasan_ubah}', 'center')
set_cell_text(t6_rows[2].findall('w:tc', ns)[2], '${v_atasan_tangguh}', 'center')
set_cell_text(t6_rows[2].findall('w:tc', ns)[3], '${v_atasan_tolak}', 'center')
set_sig_cell(t6_rows[3].findall('w:tc', ns)[3], '${atasan_jabatan},', '${atasan_nama}', 'NIP. ${atasan_nip}')

# --- Table 7: KEPUTUSAN PEJABAT ---
t7_rows = tables[7].findall('.//w:tr', ns)
set_cell_text(t7_rows[2].findall('w:tc', ns)[0], '${v_pejabat_setuju}', 'center')
set_cell_text(t7_rows[2].findall('w:tc', ns)[1], '${v_pejabat_ubah}', 'center')
set_cell_text(t7_rows[2].findall('w:tc', ns)[2], '${v_pejabat_tangguh}', 'center')
set_cell_text(t7_rows[2].findall('w:tc', ns)[3], '${v_pejabat_tolak}', 'center')
set_sig_cell(t7_rows[3].findall('w:tc', ns)[3], '${pejabat_jabatan},', '${pejabat_nama}', 'NIP. ${pejabat_nip}')

# Header Paragraphs
body = root.find('w:body', ns)
paragraphs = body.findall('w:p', ns)

p0 = paragraphs[0]
for child in list(p0):
    if child.tag.endswith('r'):
        p0.remove(child)
r0 = ET.SubElement(p0, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}r')
t0 = ET.SubElement(r0, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t')
t0.text = 'Pekanbaru, ${tgl_pengajuan}'

p2 = paragraphs[2]
for child in list(p2):
    if child.tag.endswith('r'):
        p2.remove(child)
r2 = ET.SubElement(p2, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}r')
t2 = ET.SubElement(r2, '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t')
t2.text = '${pejabat_jabatan_tujuan}'

new_doc_xml = ET.tostring(root, encoding='utf-8', xml_declaration=True)

with zipfile.ZipFile(src_docx, 'r') as z_in:
    with zipfile.ZipFile(target_docx, 'w', zipfile.ZIP_DEFLATED) as z_out:
        for item in z_in.infolist():
            if item.filename == 'word/document.xml':
                z_out.writestr(item.filename, new_doc_xml)
            else:
                z_out.writestr(item.filename, z_in.read(item.filename))

print('Master template app/Views/admin/surat/form_surat_cuti_template.docx saved successfully!')
