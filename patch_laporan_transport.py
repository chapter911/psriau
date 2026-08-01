import re

with open('app/Controllers/Admin/Laporan.php', 'r') as f:
    content = f.read()

old_logic = """        $transportStarts   = $this->request->getPost('transport_start_date') ?: [];
        $transportEnds     = $this->request->getPost('transport_end_date') ?: [];
        $transportNominals = $this->request->getPost('transport_nominal') ?: [];
        $transportKets     = $this->request->getPost('transport_ket') ?: [];

        $transportList = [];
        if (is_array($transportStarts)) {
            foreach ($transportStarts as $idx => $tStart) {
                $tStart = trim((string) $tStart);
                $tEnd   = trim((string) ($transportEnds[$idx] ?? ''));
                $tNomRaw = preg_replace('/\\D/', '', (string) ($transportNominals[$idx] ?? ''));
                $tNom   = $tNomRaw !== '' ? (int) $tNomRaw : 0;
                $tKet   = trim((string) ($transportKets[$idx] ?? ''));
                if ($tStart !== '' || $tEnd !== '' || $tNom > 0 || $tKet !== '') {
                    $transportList[] = [
                        'tgl_mulai'   => $tStart,
                        'tgl_selesai' => $tEnd,
                        'nominal'     => $tNom,
                        'keterangan'  => $tKet,
                    ];
                }
            }
        }"""

new_logic = """        $transportStarts   = $this->request->getPost('transport_start_date') ?: [];
        $transportEnds     = $this->request->getPost('transport_end_date') ?: [];
        $transportNominals = $this->request->getPost('transport_nominal') ?: [];
        $transportKets     = $this->request->getPost('transport_ket') ?: [];
        $transportJenis    = $this->request->getPost('transport_jenis') ?: [];
        $transportLumpsum  = $this->request->getPost('transport_is_lumpsum') ?: [];

        $transportList = [];
        if (is_array($transportStarts)) {
            foreach ($transportStarts as $idx => $tStart) {
                $tStart = trim((string) $tStart);
                $tEnd   = trim((string) ($transportEnds[$idx] ?? ''));
                $tNomRaw = preg_replace('/\\D/', '', (string) ($transportNominals[$idx] ?? ''));
                $tNom   = $tNomRaw !== '' ? (int) $tNomRaw : 0;
                $tKet   = trim((string) ($transportKets[$idx] ?? ''));
                $tJenis = trim((string) ($transportJenis[$idx] ?? ''));
                $tIsLumpsum = !empty($transportLumpsum[$idx]) ? true : false;
                
                if ($tStart !== '' || $tEnd !== '' || $tNom > 0 || $tKet !== '' || $tJenis !== '') {
                    $transportList[] = [
                        'tgl_mulai'   => $tStart,
                        'tgl_selesai' => $tEnd,
                        'nominal'     => $tNom,
                        'jenis'       => $tJenis,
                        'keterangan'  => $tKet,
                        'is_lumpsum'  => $tIsLumpsum,
                    ];
                }
            }
        }"""

content = content.replace(old_logic, new_logic)

with open('app/Controllers/Admin/Laporan.php', 'w') as f:
    f.write(content)
