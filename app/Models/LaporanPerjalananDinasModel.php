<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanPerjalananDinasModel extends Model
{
    protected $table = 'laporan_perjalanan_dinas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'disposisi_id', 'nomor_surat_tugas', 'periode_mulai', 'periode_selesai', 'kota_tujuan',
        'tujuan', 'sasaran', 'laporan_hasil', 'pelaksana_json', 'foto_dokumentasi_json',
        'dokumen_pendukung_json', 'creator_name', 'creator_pegawai_json', 'diketahui_oleh_json',
        'is_final', 'verified_spt_path', 'created_at', 'updated_at',
        'dasar_spt_ids_json', 'tanggal_tanda_tangan', 'is_verified', 'kop_surat_id', 'mata_anggaran_id',
    ];
    protected $useTimestamps = false;
}
