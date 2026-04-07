<?php

namespace App\Models;

use CodeIgniter\Model;

class KontrakKiModel extends Model
{
    protected $table = 'trn_kontrak_ki';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'nomor_kontrak',
        'tanggal_kontrak',
        'paket',
        'kode_personil',
        'nama',
        'alamat',
        'nik',
        'npwp',
        'jabatan',
        'durasi_pelaksanaan',
        'nomor_dipa',
        'tanggal_dipa',
        'mata_anggaran',
        'nomor_surat_undangan_pengadaan',
        'tanggal_surat_undangan_pengadaan',
        'nomor_surat_berita_acara_pengadaan',
        'tanggal_surat_berita_acara_pengadaan',
        'nomor_surat_penawaran',
        'tanggal_surat_penawaran',
        'nomor_undangan',
        'total_penawaran',
        'tanggal_awal',
        'tanggal_akhir',
        'created_date',
        'created_by',
        'tahun_anggaran',
        'no_sppbj',
        'tanggal_sppbj',
        'pejabat_ppk',
        'nip_pejabat_ppk',
        'kedudukan_pejabat_ppk',
        'nomor_surat_keputusan_menteri',
        'tanggal_surat_keputusan_menteri',
        'nomor_perubahan_keputusan_menteri',
        'bank_nomor_rekening',
        'bank_nama',
        'bank_atas_nama',
        'bank_pembayaran',
        'kategori',
        'nomor_telefon_ki',
        'email_ki',
        'nominal_kontrak',
        'nominal_hps',
        'nomor_spmk',
        'nomor_baphp',
        'nomor_surat_permohonan',
        'tanggal_surat_permohonan',
        'nama_pekerjaan',
        'jenis_pembayaran',
        'nomor_bast',
        'created_at',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by',
    ];
}
