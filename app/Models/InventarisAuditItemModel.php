<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisAuditItemModel extends Model
{
    protected $table            = 'trn_inventaris_audit_item';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'audit_id',
        'inventaris_id',
        'kode_barang',
        'nup',
        'nama_barang',
        'merk_tipe',
        'satuan',
        'peruntukan',
        'ruangan_sistem_id',
        'ruangan_sistem_nama',
        'kondisi_sistem',
        'status_pinjam_sistem',
        'peminjam_nama',
        'no_surat_pinjam',
        'status_audit',
        'kondisi_fisik',
        'ruangan_fisik_id',
        'ruangan_fisik_nama',
        'catatan_pemeriksaan',
        'audited_at',
        'audited_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Mengambil item audit dengan filter status, ruangan, pencarian, dan sorting
     */
    public function getItemsByAudit(int $auditId, array $filters = []): array
    {
        $builder = $this->where('audit_id', $auditId);

        if (! empty($filters['status_audit']) && $filters['status_audit'] !== 'semua') {
            $builder->where('status_audit', $filters['status_audit']);
        }

        if (! empty($filters['status_pinjam']) && $filters['status_pinjam'] !== 'semua') {
            $builder->where('status_pinjam_sistem', $filters['status_pinjam']);
        }

        if (isset($filters['ruangan_id']) && $filters['ruangan_id'] !== 'semua' && $filters['ruangan_id'] !== 'all' && $filters['ruangan_id'] !== '') {
            if ($filters['ruangan_id'] === 'dipinjam') {
                $builder->where('status_pinjam_sistem', 'dipinjam');
            } elseif ($filters['ruangan_id'] === 'non_ruangan') {
                $builder->where('status_pinjam_sistem !=', 'dipinjam')
                    ->groupStart()
                        ->where('ruangan_sistem_id IS NULL', null, false)
                        ->orWhere('ruangan_sistem_id', 0)
                    ->groupEnd();
            } elseif (is_numeric($filters['ruangan_id'])) {
                $builder->where('status_pinjam_sistem !=', 'dipinjam')
                    ->where('ruangan_sistem_id', (int) $filters['ruangan_id']);
            }
        }

        if (! empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $builder->groupStart()
                ->like('nama_barang', $kw)
                ->orLike('kode_barang', $kw)
                ->orLike('nup', $kw)
                ->orLike('peminjam_nama', $kw)
                ->orLike('ruangan_sistem_nama', $kw)
                ->groupEnd();
        }

        return $builder->orderBy('status_audit = "belum_diperiksa"', 'DESC', false)
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->findAll();
    }

    /**
     * Mengambil statistik ringkasan per ruangan dalam suatu sesi audit.
     * Seluruh aset yang berstatus dipinjam disatukan ke dalam grup "Aset yang Dipinjam".
     */
    public function getRoomStatsByAudit(int $auditId): array
    {
        $db = db_connect();

        $ruanganMap = [];
        if ($db->tableExists('mst_ruangan')) {
            $ruangans = $db->table('mst_ruangan')
                ->select('id, kode_ruangan, nama_ruangan, penanggung_jawab_nama, penanggung_jawab_nip, lokasi_lantai')
                ->get()
                ->getResultArray();
            foreach ($ruangans as $r) {
                $ruanganMap[(int) $r['id']] = $r;
            }
        }

        // Ambil semua item audit untuk dikelompokkan
        $items = $db->table($this->table)
            ->select('id, ruangan_sistem_id, ruangan_sistem_nama, status_pinjam_sistem, status_audit')
            ->where('audit_id', $auditId)
            ->get()
            ->getResultArray();

        $groups = [];

        foreach ($items as $item) {
            $isLoaned = ($item['status_pinjam_sistem'] === 'dipinjam');

            if ($isLoaned) {
                $groupKey = 'dipinjam';
            } elseif (! empty($item['ruangan_sistem_id']) && (int) $item['ruangan_sistem_id'] > 0) {
                $groupKey = (string) ((int) $item['ruangan_sistem_id']);
            } else {
                $groupKey = 'non_ruangan';
            }

            if (! isset($groups[$groupKey])) {
                if ($groupKey === 'dipinjam') {
                    $groups[$groupKey] = [
                        'ruangan_id'            => 'dipinjam',
                        'ruangan_key'           => 'dipinjam',
                        'ruangan_nama'          => 'Aset yang Dipinjam',
                        'nama_ruangan'          => 'Aset yang Dipinjam',
                        'kode_ruangan'          => 'PINJAM',
                        'penanggung_jawab_nama' => 'Peminjam Pegawai Satker',
                        'penanggung_jawab_nip'  => null,
                        'lokasi_lantai'         => 'Izin Pinjam Pakai',
                        'is_dipinjam_group'     => true,
                        'total_item'            => 0,
                        'total_sesuai'          => 0,
                        'total_dipinjam'        => 0,
                        'total_berubah'         => 0,
                        'total_selisih'         => 0,
                        'total_belum'           => 0,
                    ];
                } elseif ($groupKey === 'non_ruangan') {
                    $groups[$groupKey] = [
                        'ruangan_id'            => null,
                        'ruangan_key'           => 'non_ruangan',
                        'ruangan_nama'          => 'Gudang / Belum Berlokasi',
                        'nama_ruangan'          => 'Gudang / Belum Berlokasi',
                        'kode_ruangan'          => '',
                        'penanggung_jawab_nama' => null,
                        'penanggung_jawab_nip'  => null,
                        'lokasi_lantai'         => 'Penyimpanan Satker',
                        'is_dipinjam_group'     => false,
                        'total_item'            => 0,
                        'total_sesuai'          => 0,
                        'total_dipinjam'        => 0,
                        'total_berubah'         => 0,
                        'total_selisih'         => 0,
                        'total_belum'           => 0,
                    ];
                } else {
                    $rId = (int) $groupKey;
                    $rInfo = $ruanganMap[$rId] ?? null;
                    $rName = $rInfo ? $rInfo['nama_ruangan'] : ($item['ruangan_sistem_nama'] ?: 'Ruangan #' . $rId);

                    $groups[$groupKey] = [
                        'ruangan_id'            => $rId,
                        'ruangan_key'           => $groupKey,
                        'ruangan_nama'          => $rName,
                        'nama_ruangan'          => $rName,
                        'kode_ruangan'          => $rInfo ? $rInfo['kode_ruangan'] : '',
                        'penanggung_jawab_nama' => $rInfo ? $rInfo['penanggung_jawab_nama'] : null,
                        'penanggung_jawab_nip'  => $rInfo ? $rInfo['penanggung_jawab_nip'] : null,
                        'lokasi_lantai'         => $rInfo ? $rInfo['lokasi_lantai'] : null,
                        'is_dipinjam_group'     => false,
                        'total_item'            => 0,
                        'total_sesuai'          => 0,
                        'total_dipinjam'        => 0,
                        'total_berubah'         => 0,
                        'total_selisih'         => 0,
                        'total_belum'           => 0,
                    ];
                }
            }

            $groups[$groupKey]['total_item']++;
            $st = $item['status_audit'];
            if ($st === 'sesuai') {
                $groups[$groupKey]['total_sesuai']++;
            } elseif ($st === 'terkonfirmasi_dipinjam' || $isLoaned) {
                $groups[$groupKey]['total_dipinjam']++;
            } elseif (in_array($st, ['kondisi_berubah', 'salah_lokasi'], true)) {
                $groups[$groupKey]['total_berubah']++;
            } elseif ($st === 'tidak_ditemukan') {
                $groups[$groupKey]['total_selisih']++;
            } elseif ($st === 'belum_diperiksa') {
                $groups[$groupKey]['total_belum']++;
            }
        }

        $roomList = [];
        $loanGroup = null;
        $nonRoomGroup = null;

        foreach ($groups as $k => $g) {
            $tot = $g['total_item'];
            $blm = $g['total_belum'];
            $selesai = $tot - $blm;
            $g['total_selesai'] = $selesai;
            $g['persen'] = $tot > 0 ? round(($selesai / $tot) * 100) : 0;

            if ($k === 'dipinjam') {
                $loanGroup = $g;
            } elseif ($k === 'non_ruangan') {
                $nonRoomGroup = $g;
            } else {
                $roomList[] = $g;
            }
        }

        usort($roomList, static function ($a, $b) {
            return strcmp((string) $a['ruangan_nama'], (string) $b['ruangan_nama']);
        });

        $result = $roomList;
        if ($loanGroup !== null) {
            $result[] = $loanGroup;
        }
        if ($nonRoomGroup !== null) {
            $result[] = $nonRoomGroup;
        }

        return $result;
    }

    /**
     * Menandai sisa barang pada ruangan tertentu menjadi Sesuai
     */
    public function markRuanganRemainingSesuai(int $auditId, string $ruanganKey, int $auditorId, string $auditorNama): int
    {
        $db = db_connect();
        $builder = $db->table($this->table)
            ->where('audit_id', $auditId)
            ->where('status_audit', 'belum_diperiksa');

        if ($ruanganKey === 'dipinjam') {
            $builder->where('status_pinjam_sistem', 'dipinjam');
        } elseif ($ruanganKey === 'non_ruangan') {
            $builder->where('status_pinjam_sistem !=', 'dipinjam')
                ->groupStart()
                    ->where('ruangan_sistem_id IS NULL', null, false)
                    ->orWhere('ruangan_sistem_id', 0)
                ->groupEnd();
        } elseif (is_numeric($ruanganKey)) {
            $builder->where('status_pinjam_sistem !=', 'dipinjam')
                ->where('ruangan_sistem_id', (int) $ruanganKey);
        }

        $items = $builder->get()->getResultArray();
        $updatedCount = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($items as $item) {
            $isLoaned = ($item['status_pinjam_sistem'] === 'dipinjam');
            $newStatus = $isLoaned ? 'terkonfirmasi_dipinjam' : 'sesuai';
            $catatan = $isLoaned 
                ? 'Terkonfirmasi sedang dipinjam pakai oleh ' . ($item['peminjam_nama'] ?? 'pegawai') 
                : 'Pemeriksaan fisik per ruangan: Sesuai & ada di lokasi';

            $db->table($this->table)->where('id', $item['id'])->update([
                'status_audit'        => $newStatus,
                'kondisi_fisik'       => $item['kondisi_sistem'] ?: 'Baik',
                'ruangan_fisik_id'    => $item['ruangan_sistem_id'],
                'ruangan_fisik_nama'  => $item['ruangan_sistem_nama'],
                'catatan_pemeriksaan' => $catatan,
                'audited_at'          => $now,
                'audited_by'          => $auditorId,
                'updated_at'          => $now,
            ]);
            $updatedCount++;
        }

        return $updatedCount;
    }

    /**
     * Cari item berdasarkan scan QR / barcode / NUP
     */
    public function lookupScan(int $auditId, string $keyword): ?array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return null;
        }

        // Format umum QR DBR: "KODE|NUP" atau "KODE" atau "NUP"
        $kodeBarang = null;
        $nup = null;

        if (str_contains($keyword, '|')) {
            $parts = explode('|', $keyword);
            $kodeBarang = trim($parts[0] ?? '');
            $nup = trim($parts[1] ?? '');
        }

        $builder = $this->where('audit_id', $auditId);

        if ($kodeBarang && $nup) {
            $builder->groupStart()
                ->where('kode_barang', $kodeBarang)
                ->where('nup', $nup)
                ->groupEnd();
        } else {
            $builder->groupStart()
                ->where('nup', $keyword)
                ->orWhere('kode_barang', $keyword)
                ->orLike('nama_barang', $keyword)
                ->groupEnd();
        }

        return $builder->first();
    }
}
