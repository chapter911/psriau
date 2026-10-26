<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisPemeliharaanModel extends Model
{
    protected $table            = 'trn_inventaris_pemeliharaan';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'no_surat',
        'judul_surat',
        'jenis_pemeliharaan',
        'tanggal_surat',
        'tempat_surat',
        'nama_kasatker',
        'nip_kasatker',
        'jabatan_kasatker',
        'unit_kerja',
        'kop_surat_id',
        'alasan_poin_a',
        'alasan_poin_b',
        'keterangan',
        'total_item',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Mengambil daftar surat pemeliharaan dengan filter dan paginasi.
     */
    public function getList(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        $builder = $this->db->table($this->table . ' p')
            ->select('p.*,
                      COUNT(f.id) AS total_kelompok,
                      COALESCE(SUM(
                          (CASE WHEN f.foto_depan IS NOT NULL AND f.foto_depan != "" THEN 1 ELSE 0 END) +
                          (CASE WHEN f.foto_atas IS NOT NULL AND f.foto_atas != "" THEN 1 ELSE 0 END) +
                          (CASE WHEN f.foto_bawah IS NOT NULL AND f.foto_bawah != "" THEN 1 ELSE 0 END) +
                          (CASE WHEN f.foto_belakang IS NOT NULL AND f.foto_belakang != "" THEN 1 ELSE 0 END)
                      ), 0) AS total_foto_terisi')
            ->join('trn_inventaris_pemeliharaan_foto f', 'f.pemeliharaan_id = p.id', 'left')
            ->groupBy('p.id');

        if (! empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $builder->groupStart()
                ->like('p.no_surat', $q)
                ->orLike('p.judul_surat', $q)
                ->orLike('p.nama_kasatker', $q)
            ->groupEnd();
        }

        if (! empty($filters['tahun'])) {
            $builder->where('YEAR(p.tanggal_surat)', (int) $filters['tahun']);
        }

        if (! empty($filters['jenis'])) {
            $builder->where('p.jenis_pemeliharaan', $filters['jenis']);
        }

        $rows = $builder->orderBy('p.tanggal_surat', 'DESC')
            ->orderBy('p.id', 'DESC')
            ->get($limit, $offset)
            ->getResultArray();

        foreach ($rows as &$r) {
            $kelompok = (int) ($r['total_kelompok'] ?? 0);
            $terisi = (int) ($r['total_foto_terisi'] ?? 0);
            $target = $kelompok * 4;

            if ($kelompok === 0 || $terisi === 0) {
                $r['status_foto'] = 'kosong';
                $r['foto_color']  = 'danger'; // Merah
                $r['foto_label']  = 'Belum Ada Foto';
            } elseif ($terisi >= $target && $target > 0) {
                $r['status_foto'] = 'lengkap';
                $r['foto_color']  = 'success'; // Hijau
                $r['foto_label']  = 'Lengkap (' . $terisi . '/' . $target . ')';
            } else {
                $r['status_foto'] = 'sebagian';
                $r['foto_color']  = 'warning'; // Kuning
                $r['foto_label']  = 'Belum Lengkap (' . $terisi . '/' . $target . ')';
            }
        }
        unset($r);

        return $rows;
    }

    /**
     * Hitung total surat pemeliharaan berdasarkan filter.
     */
    public function countFiltered(array $filters = []): int
    {
        $builder = $this->builder();

        if (! empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $builder->groupStart()
                ->like('no_surat', $q)
                ->orLike('judul_surat', $q)
                ->orLike('nama_kasatker', $q)
            ->groupEnd();
        }

        if (! empty($filters['tahun'])) {
            $builder->where('YEAR(tanggal_surat)', (int) $filters['tahun']);
        }

        if (! empty($filters['jenis'])) {
            $builder->where('jenis_pemeliharaan', $filters['jenis']);
        }

        return $builder->countAllResults();
    }

    /**
     * Mengambil detail lengkap surat pemeliharaan beserta item dan lampiran foto.
     */
    public function getDetail(int $id): ?array
    {
        $header = $this->find($id);
        if (! $header) {
            return null;
        }

        $itemModel = new InventarisPemeliharaanItemModel();
        $fotoModel = new InventarisPemeliharaanFotoModel();

        $header['items'] = $itemModel->where('pemeliharaan_id', $id)
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $header['fotos'] = $fotoModel->where('pemeliharaan_id', $id)
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return $header;
    }

    /**
     * Menghapus surat beserta seluruh item, foto, dan berkas fisik foto di disk.
     */
    public function deleteWithChildren(int $id): bool
    {
        $fotoModel = new InventarisPemeliharaanFotoModel();
        $fotos = $fotoModel->where('pemeliharaan_id', $id)->findAll();

        foreach ($fotos as $f) {
            foreach (['foto_depan', 'foto_atas', 'foto_bawah', 'foto_belakang'] as $col) {
                if (! empty($f[$col])) {
                    $cleanPath = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, (string) $f[$col]), DIRECTORY_SEPARATOR);
                    $fullPath = FCPATH . $cleanPath;
                    if (is_file($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }
        }

        $fotoModel->where('pemeliharaan_id', $id)->delete();
        (new InventarisPemeliharaanItemModel())->where('pemeliharaan_id', $id)->delete();

        return (bool) $this->delete($id);
    }
}
