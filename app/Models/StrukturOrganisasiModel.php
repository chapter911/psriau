<?php

namespace App\Models;

use CodeIgniter\Model;

class StrukturOrganisasiModel extends Model
{
    protected $table            = 'tb_struktur_organisasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent_id',
        'pegawai_id',
        'nama_manual',
        'nip_manual',
        'foto_manual',
        'jabatan_bagian',
        'kategori_kelompok',
        'urutan',
        'level',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get tree nodes joined with master pegawai, falling back to manual inputs if not from master
     */
    public function getTreeNodes(): array
    {
        $hasNamaManual = $this->db->fieldExists('nama_manual', $this->table);

        $builder = $this->db->table($this->table . ' s');

        if ($hasNamaManual) {
            $builder->select('
                s.*,
                COALESCE(NULLIF(p.nama, ""), s.nama_manual) AS nama_pegawai,
                COALESCE(NULLIF(p.nip, ""), s.nip_manual) AS nip_pegawai,
                COALESCE(NULLIF(p.foto, ""), s.foto_manual) AS foto_pegawai,
                p.jenis_pegawai,
                p.eselon,
                p.golongan,
                j.jabatan AS nama_jabatan_master
            ');
        } else {
            $builder->select('
                s.*,
                p.nama AS nama_pegawai,
                p.nip AS nip_pegawai,
                p.foto AS foto_pegawai,
                p.jenis_pegawai,
                p.eselon,
                p.golongan,
                j.jabatan AS nama_jabatan_master
            ');
        }

        $builder->join('mst_pegawai p', 'p.id = s.pegawai_id', 'left');
        $builder->join('mst_jabatan j', 'j.id = p.jabatan_utama_id', 'left');
        $builder->where('s.is_active', 1);
        $builder->orderBy('s.level', 'ASC');
        $builder->orderBy('s.urutan', 'ASC');
        $builder->orderBy('s.id', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get rank order mapping for pegawai based on Struktur Organisasi hierarchy
     * Returns array containing id_map, nip_map, and nama_map
     */
    public function getPegawaiOrderMap(): array
    {
        if (! $this->db->tableExists($this->table)) {
            return ['id_map' => [], 'nip_map' => [], 'nama_map' => []];
        }

        $nodes = $this->getTreeNodes();
        $idMap = [];
        $nipMap = [];
        $namaMap = [];
        $rank = 0;

        foreach ($nodes as $node) {
            $rank++;
            $pegawaiId = (int) ($node['pegawai_id'] ?? 0);
            if ($pegawaiId > 0 && ! isset($idMap[$pegawaiId])) {
                $idMap[$pegawaiId] = $rank;
            }

            $nip = trim((string) ($node['nip_pegawai'] ?? $node['nip_manual'] ?? ''));
            if ($nip !== '' && ! isset($nipMap[$nip])) {
                $nipMap[$nip] = $rank;
            }

            $nama = strtolower(trim((string) ($node['nama_pegawai'] ?? $node['nama_manual'] ?? '')));
            if ($nama !== '' && ! isset($namaMap[$nama])) {
                $namaMap[$nama] = $rank;
            }
        }

        return [
            'id_map'   => $idMap,
            'nip_map'  => $nipMap,
            'nama_map' => $namaMap,
        ];
    }
}

