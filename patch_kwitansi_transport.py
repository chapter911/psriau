import re

with open('app/Views/admin/laporan/cetak_kwitansi.php', 'r') as f:
    content = f.read()

old_logic = """                $tKet   = trim((string) ($tItem['keterangan'] ?? ''));

                $tDays = 0;
                if (!empty($tStart) && !empty($tEnd)) {
                    try {
                        $d1 = new \DateTime($tStart);
                        $d2 = new \DateTime($tEnd);
                        $tDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }
                
                $rate = $tNom;
                // If user didn't enter days but entered rate, assume sub = rate
                $sub = ($tDays > 0) ? ($tDays * $rate) : $rate;
                $calcTransport += $sub;

                if ($tDays > 0 || $rate > 0) {
                    $desc = '';
                    if ($tKet !== '') {
                        $desc = esc($tKet);
                    } else {
                        if ($tDays > 0) {
                            $desc = $tDays . ' hari x Rp ' . number_format($rate, 0, ',', '.');
                        } else {
                            $desc = 'Transport';
                        }
                    }"""

new_logic = """                $tKet   = trim((string) ($tItem['keterangan'] ?? ''));
                $tJenis = trim((string) ($tItem['jenis'] ?? ''));
                $tIsLumpsum = !empty($tItem['is_lumpsum']);

                $tDays = 0;
                if (!empty($tStart) && !empty($tEnd)) {
                    try {
                        $d1 = new \DateTime($tStart);
                        $d2 = new \DateTime($tEnd);
                        $tDays = max(0, $d1->diff($d2)->days + 1);
                    } catch (\Throwable $e) {}
                }
                
                $rate = $tNom;
                
                if ($tIsLumpsum) {
                    $sub = $rate;
                } else {
                    $sub = ($tDays > 0) ? ($tDays * $rate) : $rate;
                }
                
                $calcTransport += $sub;

                if ($tDays > 0 || $rate > 0 || $tIsLumpsum) {
                    $descParts = [];
                    if ($tJenis !== '') {
                        $descParts[] = $tJenis;
                    }
                    if ($tKet !== '') {
                        $descParts[] = $tKet;
                    }
                    
                    if (!empty($descParts)) {
                        $desc = esc(implode(' - ', $descParts));
                    } else {
                        if ($tIsLumpsum) {
                            $desc = 'Transport (PP)';
                        } elseif ($tDays > 0) {
                            $desc = $tDays . ' hari x Rp ' . number_format($rate, 0, ',', '.');
                        } else {
                            $desc = 'Transport';
                        }
                    }"""

content = content.replace(old_logic, new_logic)

with open('app/Views/admin/laporan/cetak_kwitansi.php', 'w') as f:
    f.write(content)
