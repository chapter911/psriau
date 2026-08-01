import re

with open('app/Controllers/Admin/Laporan.php', 'r') as f:
    content = f.read()

def repl_error(m):
    return f"""if ($this->request->isAJAX()) {{
            return $this->response->setJSON(['status' => 'error', 'message' => '{m.group(1)}']);
        }}
        return redirect()->back()->with('error', '{m.group(1)}');"""

content = re.sub(r"return redirect\(\)->back\(\)->with\('error',\s*'([^']+)'\);", repl_error, content)

def repl_success(m):
    return f"""if ($this->request->isAJAX()) {{
            return $this->response->setJSON(['status' => 'success', 'message' => '{m.group(1)}']);
        }}
        return redirect()->back()->with('success', '{m.group(1)}');"""

content = re.sub(r"return redirect\(\)->back\(\)->with\('success',\s*'([^']+)'\);", repl_success, content)

old_update = """
        $updateData = [
            'nomor_surat_tugas' => $nomorSurat,
            'dasar_spt_ids_json' => json_encode($dasarInputs, JSON_UNESCAPED_UNICODE),
            'tanggal_tanda_tangan' => $tglTtd,
            'is_verified' => 1,
            'rincian_biaya_json' => json_encode($rincianBiaya, JSON_UNESCAPED_UNICODE),
        ];
"""
new_update = """
        $updateData = ['is_verified' => 1];
        $targetTab = $this->request->getPost('tab_action') ?: 'all';
        
        if ($targetTab === 'all' || $targetTab === 'tab1') {
            $updateData['nomor_surat_tugas'] = $nomorSurat;
            $updateData['dasar_spt_ids_json'] = json_encode($dasarInputs, JSON_UNESCAPED_UNICODE);
            $updateData['tanggal_tanda_tangan'] = $tglTtd;
            if ($kopSuratId > 0) {
                $updateData['kop_surat_id'] = $kopSuratId;
            }
            if ($kodeNomorInput !== '') {
                $updateData['kode_nomor'] = $kodeNomorInput;
            } else {
                $this->ensureKodeNomorAssigned($row);
                $updateData['kode_nomor'] = $row['kode_nomor'];
            }
        }
        
        if ($targetTab === 'all' || $targetTab === 'tab2') {
            $updateData['rincian_biaya_json'] = json_encode($rincianBiaya, JSON_UNESCAPED_UNICODE);
            if ($mataAnggaranId > 0) {
                $updateData['mata_anggaran_id'] = $mataAnggaranId;
            }
        }
"""
if old_update in content:
    content = content.replace(old_update, new_update)

content = content.replace(
    "if ($nomorSurat === '') {",
    "if ($nomorSurat === '' && ($this->request->getPost('tab_action') === 'all' || $this->request->getPost('tab_action') === 'tab1')) {"
)
content = content.replace(
    "if ($tglTtd === '') {",
    "if ($tglTtd === '' && ($this->request->getPost('tab_action') === 'all' || $this->request->getPost('tab_action') === 'tab1')) {"
)

content = re.sub(r"if \(\$kopSuratId > 0\) \{[\s\S]*?\}", "", content, count=1)
content = re.sub(r"if \(\$mataAnggaranId > 0\) \{[\s\S]*?\}", "", content, count=1)
content = re.sub(r"if \(\$kodeNomorInput !== ''\) \{[\s\S]*?\$updateData\['kode_nomor'\] = \$row\['kode_nomor'\];\n\s*\}", "", content, count=1)

with open('app/Controllers/Admin/Laporan.php', 'w') as f:
    f.write(content)
