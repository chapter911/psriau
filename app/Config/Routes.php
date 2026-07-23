<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('favicon.ico', 'Favicon::index');
$routes->get('tentang-kami', 'About::index');

$routes->get('acara', 'Events::index');
$routes->get('acara/(:segment)', 'Events::show/$1');

$routes->get('instagram', 'Articles::index');
$routes->get('instagram/(:segment)', 'Articles::show/$1');
$routes->addRedirect('berita', 'instagram', 301);
$routes->addRedirect('berita/(.*)', 'instagram/$1', 301);

$routes->get('kegiatan-lapangan/share/(:segment)', 'Admin\\Dokumentasi::sharedGallery/$1');
$routes->get('kegiatan-lapangan/share/(:segment)/download-zip', 'Admin\\Dokumentasi::sharedDownloadZip/$1');
$routes->get('kegiatan-lapangan/share/(:segment)/download-photo/(:num)', 'Admin\\Dokumentasi::sharedDownloadPhoto/$1/$2');
$routes->get('simak/share/(:segment)', 'Admin\\Kontrak::sharedSimak/$1');
$routes->post('simak/share/(:segment)/otp/request', 'Admin\\Kontrak::sharedRequestOtp/$1');
$routes->post('simak/share/(:segment)/otp/verify', 'Admin\\Kontrak::sharedVerifyOtp/$1');
$routes->get('simak/share/(:segment)/otp/touch', 'Admin\\Kontrak::sharedTouchOtp/$1');
$routes->post('simak/share/(:segment)/upload', 'Admin\\Kontrak::sharedUploadSimakDokumen/$1');
$routes->get('simak/share/(:segment)/download-dokumen/(:num)', 'Admin\\Kontrak::sharedDownloadDokumen/$1/$2');

$routes->get('privacy-policy', 'Legal::privacy');
$routes->get('terms-of-service', 'Legal::terms');

// OAuth routes for Google Drive
$routes->get('oauth/connect', 'Oauth::connect');
$routes->get('oauth/callback', 'Oauth::callback');
$routes->get('oauth/success', 'Oauth::success');
$routes->get('oauth/status', 'Oauth::status');
$routes->get('oauth/disconnect', 'Oauth::disconnect');
// Media streaming fallback removed — serve assets directly from /public

$routes->get('masuk', 'Auth::loginForm');
$routes->post('masuk', 'Auth::login');
$routes->get('keluar', 'Auth::logout');
$routes->get('forbidden', 'Home::forbidden');

// Public Approval Routes for Disposisi Perjalanan Dinas (Token Secured - No Login Required)
$routes->match(['get', 'post'], 'admin/surat/perjalanan-dinas/disposisi/(:num)/setujui', 'Admin\DisposisiPerjalananDinas::setujui/$1');
$routes->match(['get', 'post'], 'admin/surat/perjalanan-dinas/disposisi/(:num)/tolak', 'Admin\DisposisiPerjalananDinas::tolak/$1');

$routes->group('admin', ['filter' => 'auth:admin,editor'], static function ($routes): void {
	$routes->get('/', 'Admin\\Dashboard::index');
	$routes->get('map', 'Admin\\Dashboard::map');
	$routes->get('dashboard/map', 'Admin\\Dashboard::map');
	$routes->get('dashboard/map-data', 'Admin\\Dashboard::mapData');
	$routes->get('dashboard/map-kecamatan-options', 'Admin\\Dashboard::mapKecamatanOptions');
	$routes->get('dashboard/map-detail', 'Admin\\Dashboard::mapDetail');
	$routes->get('dashboard/map-contour-data', 'Admin\\Dashboard::mapContourData');

	// Update password user
	$routes->get('password', 'Admin\\Password::index');
	$routes->post('password/update', 'Admin\\Password::update');

	$routes->match(['get', 'post'], 'pengaturan-home', 'Admin\\HomeSetting::index');
	$routes->match(['get', 'post'], 'pengaturan/application', 'Admin\\Setting::application');
	$routes->post('pengaturan/application/reset-sidebar', 'Admin\\Setting::resetSidebarDefaults');
	$routes->post('pengaturan/application/test-email', 'Admin\\Setting::testEmail');
	$routes->post('pengaturan/application/git-pull', 'Admin\\Setting::gitPull');
	$routes->post('pengaturan/application/merge-database', 'Admin\\Setting::mergeDatabase');
	$routes->get('pengaturan/application/error-log-dates', 'Admin\\Setting::errorLogDates');
	$routes->get('pengaturan/application/error-logs', 'Admin\\Setting::errorLogsByDate');
	$routes->get('pengaturan/menus', 'Admin\\Setting::menus');
	$routes->post('pengaturan/menus/save', 'Admin\\Setting::menusSave');
	$routes->post('pengaturan/menus/tambah', 'Admin\\Setting::createMenu');
	$routes->post('pengaturan/menus/(:segment)/ubah', 'Admin\\Setting::updateMenu/$1');
	$routes->post('pengaturan/menus/(:segment)/icon', 'Admin\\Setting::updateMenuIcon/$1');
	$routes->post('pengaturan/menus/(:segment)/hapus', 'Admin\\Setting::deleteMenu/$1');
	$routes->get('pengaturan/api', 'ApiDocs::index');
	$routes->get('utility/user', 'Admin\\Utility::user');
	$routes->get('utility/user/list', 'Admin\\Utility::userList');
	$routes->post('utility/user/tambah', 'Admin\\Utility::userCreate');
	$routes->post('utility/user/(:num)/ubah', 'Admin\\Utility::userUpdate/$1');
	$routes->post('utility/user/(:num)/reset-password', 'Admin\\Utility::userResetPassword/$1');
	$routes->post('utility/user/(:num)/hapus', 'Admin\\Utility::userDelete/$1');
	$routes->get('utility/role', 'Admin\\Utility::userRole');
	$routes->post('utility/role/tambah', 'Admin\\Utility::roleCreate');
	$routes->get('utility/role/access/(:num)', 'Admin\\Utility::userRoleAccess/$1');
	$routes->post('utility/role/access/save', 'Admin\\Utility::userRoleAccessSave');
	$routes->get('utility/user-group', 'Admin\\Utility::userGroup');
	$routes->get('utility/user-group/access/(:num)', 'Admin\\Utility::userGroupAccess/$1');
	$routes->post('utility/user-group/access/save', 'Admin\\Utility::userGroupAccessSave');
	$routes->get('history/login', 'Admin\\History::login');
	$routes->get('history/edit', 'Admin\\History::edit');
	$routes->get('history/delete', 'Admin\\History::delete');
	$routes->get('kontrak/paket', 'Admin\\Kontrak::paket');
	$routes->post('kontrak/paket/tambah', 'Admin\\Kontrak::createPaket');
	$routes->post('kontrak/paket/(:num)/kop-surat', 'Admin\\Kontrak::updatePaketKopSurat/$1');
	$routes->post('kontrak/paket/syarat-umum', 'Admin\\Kontrak::updateSyaratUmum');
	$routes->get('kontrak/syarat-umum/jabatan', 'Admin\\Kontrak::getJabaranSyaratUmum');
	$routes->post('kontrak/syarat-umum/get', 'Admin\\Kontrak::getSyaratUmumByPaketId');
	$routes->post('kontrak/syarat-umum/save', 'Admin\\Kontrak::saveSyaratUmumByJabatan');
	$routes->get('kontrak/simak/konstruksi', 'Admin\\Kontrak::simakKonstruksi');
	$routes->get('kontrak/simak/konstruksi/export/html', 'Admin\\Kontrak::exportSimakKonstruksiHtml');
	$routes->get('kontrak/simak/konstruksi/export/excel', 'Admin\\Kontrak::exportSimakKonstruksiExcel');
	$routes->get('kontrak/simak/konsultasi', 'Admin\\Kontrak::simakKonsultasi');
	$routes->get('kontrak/simak/konsultasi/export/html', 'Admin\\Kontrak::exportSimakKonsultasiHtml');
	$routes->get('kontrak/simak/konsultasi/export/excel', 'Admin\\Kontrak::exportSimakKonsultasiExcel');
	$routes->post('kontrak/simak/konstruksi/import', 'Admin\\Kontrak::importSimak');
	$routes->get('kontrak/simak/konstruksi/template', 'Admin\\Kontrak::exportSimakTemplate');
	$routes->post('kontrak/simak/konstruksi/tambah', 'Admin\\Kontrak::createSimak');
	$routes->post('kontrak/simak/konstruksi/(:num)/ubah', 'Admin\\Kontrak::updateSimak/$1');
	$routes->post('kontrak/simak/konstruksi/(:num)/verifikasi', 'Admin\\Kontrak::saveSimakVerifikasi/$1');
	$routes->post('kontrak/simak/konstruksi/(:num)/verifikasi/upload', 'Admin\\Kontrak::uploadSimakVerifikasiDokumen/$1');
	$routes->post('kontrak/simak/konstruksi/(:num)/admin-upload-dokumen', 'Admin\\Kontrak::adminUploadSimakDokumen/$1');
	$routes->post('kontrak/simak/konstruksi/(:num)/share', 'Admin\\Kontrak::createSimakShare/$1');
	$routes->post('kontrak/simak/konstruksi/(:num)/share/deactivate', 'Admin\\Kontrak::deactivateSimakShare/$1');
	$routes->post('kontrak/simak/konstruksi/(:num)/hapus', 'Admin\\Kontrak::deleteSimak/$1');
	$routes->get('kontrak/simak/konstruksi/verifikasi-dokumen/(:num)', 'Admin\\Kontrak::viewSimakVerifikasiDokumen/$1');
	$routes->post('kontrak/simak/konstruksi/salin-dokumen-gdrive/(:num)', 'Admin\\Kontrak::salinDokumenGoogleDrive/$1');
	$routes->post('kontrak/simak/konstruksi/simpan-link-gdrive/(:num)', 'Admin\\Kontrak::simpanLinkBaruGoogleDrive/$1');
	$routes->get('kontrak/simak/konstruksi/(:num)', 'Admin\\Kontrak::detailSimak/$1');
	$routes->get('kontrak/simak/konstruksi/(:num)/export/excel', 'Admin\\Kontrak::exportSimakKonstruksiDetailExcel/$1');
	$routes->get('kontrak/simak/konstruksi/(:num)/export/html', 'Admin\\Kontrak::exportSimakKonstruksiDetailHtml/$1');
	$routes->get('kontrak/simak/konstruksi/(:num)/export/zip', 'Admin\\Kontrak::downloadSimakKonstruksiZip/$1');

	// SIMAK Jasa Konsultansi Routes
	$routes->post('kontrak/simak/konsultasi/import', 'Admin\\Kontrak::importSimakKonsultasi');
	$routes->get('kontrak/simak/konsultasi/template', 'Admin\\Kontrak::exportSimakKonsultasiTemplate');
	$routes->post('kontrak/simak/konsultasi/tambah', 'Admin\\Kontrak::createSimakKonsultasi');
	$routes->post('kontrak/simak/konsultasi/(:num)/ubah', 'Admin\\Kontrak::updateSimakKonsultasi/$1');
	$routes->post('kontrak/simak/konsultasi/(:num)/verifikasi', 'Admin\\Kontrak::saveSimakKonsultasiVerifikasi/$1');
	$routes->post('kontrak/simak/konsultasi/(:num)/verifikasi/upload', 'Admin\\Kontrak::uploadSimakKonsultasiVerifikasiDokumen/$1');
	$routes->post('kontrak/simak/konsultasi/(:num)/admin-upload-dokumen', 'Admin\\Kontrak::adminUploadSimakKonsultasiDokumen/$1');
	$routes->post('kontrak/simak/konsultasi/(:num)/share', 'Admin\\Kontrak::createSimakKonsultasiShare/$1');
	$routes->post('kontrak/simak/konsultasi/(:num)/share/deactivate', 'Admin\\Kontrak::deactivateSimakKonsultasiShare/$1');
	$routes->post('kontrak/simak/konsultasi/(:num)/hapus', 'Admin\\Kontrak::deleteSimakKonsultasi/$1');
	$routes->get('kontrak/simak/konsultasi/verifikasi-dokumen/(:num)', 'Admin\\Kontrak::viewSimakKonsultasiVerifikasiDokumen/$1');
	$routes->post('kontrak/simak/konsultasi/salin-dokumen-gdrive/(:num)', 'Admin\\Kontrak::salinDokumenGoogleDrive/$1');
	$routes->post('kontrak/simak/konsultasi/simpan-link-gdrive/(:num)', 'Admin\\Kontrak::simpanLinkBaruGoogleDrive/$1');
	$routes->get('kontrak/simak/konsultasi/(:num)', 'Admin\\Kontrak::detailSimakKonsultasi/$1');
	$routes->get('kontrak/simak/konsultasi/(:num)/export/excel', 'Admin\\Kontrak::exportSimakKonsultasiDetailExcel/$1');
	$routes->get('kontrak/simak/konsultasi/(:num)/export/html', 'Admin\\Kontrak::exportSimakKonsultasiDetailHtml/$1');
	$routes->get('kontrak/simak/konsultasi/(:num)/export/zip', 'Admin\\Kontrak::downloadSimakKonsultasiZip/$1');
	
	$routes->get('master/kop-surat', 'Admin\\KopSurat::index');
	$routes->match(['get', 'post'], 'master/kop-surat/tambah', 'Admin\\KopSurat::create');
	$routes->match(['get', 'post'], 'master/kop-surat/(:num)/ubah', 'Admin\\KopSurat::edit/$1');
	$routes->post('master/kop-surat/(:num)/status', 'Admin\\KopSurat::updateStatus/$1');
	$routes->post('master/kop-surat/(:num)/hapus', 'Admin\\KopSurat::delete/$1');

	// Master Dasar SPT
	$routes->get('master/dasar-spt', 'Admin\DasarSpt::index');
	$routes->post('master/dasar-spt/tambah', 'Admin\DasarSpt::create');
	$routes->post('master/dasar-spt/(:num)/ubah', 'Admin\DasarSpt::edit/$1');
	$routes->post('master/dasar-spt/(:num)/hapus', 'Admin\DasarSpt::delete/$1');

	// Master Transportasi Routes
	$routes->get('master/transportasi', 'Admin\Transportasi::index');
	$routes->post('master/transportasi/tambah', 'Admin\Transportasi::create');
	$routes->post('master/transportasi/(:num)/ubah', 'Admin\Transportasi::edit/$1');
	$routes->post('master/transportasi/(:num)/hapus', 'Admin\Transportasi::delete/$1');

	// Master Biaya Routes (Transportasi, Penginapan, Harian Personel)
	$routes->get('master/biaya/transportasi', 'Admin\\MasterBiaya::transportasiIndex');
	$routes->post('master/biaya/transportasi/tambah', 'Admin\\MasterBiaya::transportasiCreate');
	$routes->post('master/biaya/transportasi/(:num)/ubah', 'Admin\\MasterBiaya::transportasiEdit/$1');
	$routes->post('master/biaya/transportasi/(:num)/hapus', 'Admin\\MasterBiaya::transportasiDelete/$1');
	$routes->get('master/biaya/penginapan', 'Admin\\MasterBiaya::penginapanIndex');
	$routes->post('master/biaya/penginapan/tambah', 'Admin\\MasterBiaya::penginapanCreate');
	$routes->post('master/biaya/penginapan/(:num)/ubah', 'Admin\\MasterBiaya::penginapanEdit/$1');
	$routes->post('master/biaya/penginapan/(:num)/hapus', 'Admin\\MasterBiaya::penginapanDelete/$1');
	$routes->get('master/biaya/harian', 'Admin\\MasterBiaya::harianIndex');
	$routes->post('master/biaya/harian/tambah', 'Admin\\MasterBiaya::harianCreate');
	$routes->post('master/biaya/harian/(:num)/ubah', 'Admin\\MasterBiaya::harianEdit/$1');
	$routes->post('master/biaya/harian/(:num)/hapus', 'Admin\\MasterBiaya::harianDelete/$1');

	$routes->get('master/sekolah', 'Admin\\MasterSekolah::index');
	$routes->get('master/sekolah/export', 'Admin\\MasterSekolah::export');
	$routes->post('master/sekolah/tambah', 'Admin\\MasterSekolah::create');
	$routes->post('master/sekolah/(:segment)/ubah', 'Admin\\MasterSekolah::edit/$1');
	$routes->get('master/pegawai', 'Admin\\Pegawai::index');
	$routes->get('master/pegawai/template', 'Admin\\Pegawai::downloadTemplate');
	$routes->get('master/pegawai/export', 'Admin\\Pegawai::export');
	$routes->post('master/pegawai/tambah', 'Admin\\Pegawai::create');
	$routes->post('master/pegawai/import', 'Admin\\Pegawai::import');
	$routes->post('master/pegawai/(:num)/ubah', 'Admin\\Pegawai::edit/$1');
	$routes->post('master/pegawai/(:num)/status', 'Admin\\Pegawai::updateStatus/$1');
	$routes->get('master/jabatan', 'Admin\\Jabatan::index');
	$routes->get('master/jabatan/template', 'Admin\\Jabatan::downloadTemplate');
	$routes->post('master/jabatan/tambah', 'Admin\\Jabatan::create');
	$routes->post('master/jabatan/import', 'Admin\\Jabatan::import');
	$routes->post('master/jabatan/(:num)/ubah', 'Admin\\Jabatan::edit/$1');
	$routes->post('master/jabatan/(:num)/status', 'Admin\\Jabatan::updateStatus/$1');
	$routes->get('master/paket', 'Admin\\Paket::index');
	$routes->post('master/paket/tambah', 'Admin\\Paket::create');
	$routes->post('master/paket/(:num)/ubah', 'Admin\\Paket::edit/$1');
	$routes->get('master/wilayah', 'Admin\\MasterWilayah::wilayah');
	$routes->get('master/provinsi', 'Admin\\MasterWilayah::provinsi');
	$routes->post('master/provinsi/tambah', 'Admin\\MasterWilayah::provinsiCreate');
	$routes->post('master/provinsi/(:segment)/ubah', 'Admin\\MasterWilayah::provinsiEdit/$1');
	$routes->get('master/kabupaten', 'Admin\\MasterWilayah::kabupaten');
	$routes->post('master/kabupaten/tambah', 'Admin\\MasterWilayah::kabupatenCreate');
	$routes->post('master/kabupaten/(:segment)/(:segment)/ubah', 'Admin\\MasterWilayah::kabupatenEdit/$1/$2');
	$routes->get('master/kecamatan', 'Admin\\MasterWilayah::kecamatan');
	$routes->post('master/kecamatan/tambah', 'Admin\\MasterWilayah::kecamatanCreate');
	$routes->post('master/kecamatan/(:segment)/(:segment)/(:segment)/ubah', 'Admin\\MasterWilayah::kecamatanEdit/$1/$2/$3');
	$routes->get('master/kelurahan', 'Admin\\MasterWilayah::kelurahan');
	$routes->post('master/kelurahan/tambah', 'Admin\\MasterWilayah::kelurahanCreate');
	$routes->post('master/kelurahan/(:segment)/(:segment)/(:segment)/(:segment)/ubah', 'Admin\\MasterWilayah::kelurahanEdit/$1/$2/$3/$4');
	$routes->get('master/simak/konstruksi', 'Admin\\MasterSimak::konstruksi');
	$routes->get('master/simak/konstruksi/export', 'Admin\\MasterSimak::konstruksiExport');
	$routes->post('master/simak/konstruksi/import', 'Admin\\MasterSimak::konstruksiImport');
	$routes->post('master/simak/konstruksi/import/apply', 'Admin\\MasterSimak::konstruksiImportApply');
	$routes->get('master/simak/konsultasi', 'Admin\\MasterSimak::konsultasi');
	$routes->post('master/simak/konstruksi/tambah', 'Admin\\MasterSimak::konstruksiCreate');
	$routes->post('master/simak/konstruksi/(:num)/ubah', 'Admin\\MasterSimak::konstruksiUpdate/$1');
	$routes->post('master/simak/konstruksi/(:num)/status', 'Admin\\MasterSimak::konstruksiUpdateStatus/$1');
	$routes->post('master/simak/konstruksi/simpan-hirarki', 'Admin\\MasterSimak::konstruksiSaveHierarchy');
	$routes->post('master/simak/konstruksi/(:num)/hapus', 'Admin\\MasterSimak::konstruksiDelete/$1');
	$routes->post('master/simak/konsultasi/tambah', 'Admin\\MasterSimak::konsultasiCreate');
	$routes->post('master/simak/konstruksi/(:num)/share-visibility', 'Admin\MasterSimak::konstruksiUpdateShareVisibility/$1');
	$routes->post('master/simak/konsultasi/(:num)/ubah', 'Admin\\MasterSimak::konsultasiUpdate/$1');
	$routes->post('master/simak/konsultasi/(:num)/status', 'Admin\\MasterSimak::konsultasiUpdateStatus/$1');
	$routes->post('master/simak/konsultasi/simpan-hirarki', 'Admin\\MasterSimak::konsultasiSaveHierarchy');
	$routes->post('master/simak/konsultasi/(:num)/share-visibility', 'Admin\MasterSimak::konsultasiUpdateShareVisibility/$1');
	$routes->post('master/simak/konsultasi/(:num)/hapus', 'Admin\\MasterSimak::konsultasiDelete/$1');
	$routes->get('kontrak/ki/(:num)', 'Admin\\Kontrak::ki/$1');
	$routes->post('kontrak/ki/(:num)/tambah', 'Admin\\Kontrak::createKi/$1');
	$routes->post('kontrak/ki/(:num)/(:num)/ubah', 'Admin\\Kontrak::updateKi/$1/$2');
	$routes->post('kontrak/ki/(:num)/import', 'Admin\\Kontrak::importKi/$1');
	$routes->get('kontrak/ki/(:num)/export', 'Admin\\Kontrak::exportKi/$1');

	$routes->get('laporan', 'Admin\\Laporan::index');
	$routes->get('laporan/harian', 'Admin\\Laporan::harian');
	$routes->get('laporan/harian/(:num)', 'Admin\\Laporan::harianDetail/$1');
	$routes->post('laporan/harian/sekolah/tambah', 'Admin\\Laporan::createHarianTitle');
	$routes->post('laporan/harian/sekolah/(:num)/hapus', 'Admin\\Laporan::deleteHarianTitle/$1');
	$routes->post('laporan/harian/tambah', 'Admin\\Laporan::createHarian');
	$routes->post('laporan/harian/(:num)/hapus', 'Admin\\Laporan::deleteHarian/$1');
	$routes->get('laporan/mingguan', 'Admin\\Laporan::mingguan');
	$routes->post('laporan/mingguan/tambah', 'Admin\\Laporan::createMingguan');
	$routes->get('laporan/perjalanan-dinas', 'Admin\\Laporan::perjalananDinas');
	$routes->match(['get', 'post'], 'laporan/perjalanan-dinas/buat', 'Admin\\Laporan::perjalananDinasBuat');
	$routes->get('laporan/perjalanan-dinas/(:num)/dokumen', 'Admin\\Laporan::perjalananDinasDokumen/$1');
	$routes->post('laporan/perjalanan-dinas/(:num)/upload-verified', 'Admin\\Laporan::perjalananDinasUploadVerified/$1');
	$routes->get('laporan/perjalanan-dinas/(:num)/hapus', 'Admin\\Laporan::perjalananDinasHapus/$1');

	// RAB Per Gedung Routes
	$routes->get('laporan/rab-gedung', 'Admin\RabGedung::index');
	$routes->get('laporan/rab-gedung/detail-semua', 'Admin\RabGedung::detailSemua');
	$routes->get('laporan/rab-gedung/export-excel', 'Admin\RabGedung::exportExcel');
	$routes->get('laporan/rab-gedung/detail/(:num)', 'Admin\RabGedung::detail/$1');
	$routes->get('laporan/rab-gedung/data', 'Admin\RabGedung::data');
	$routes->post('laporan/rab-gedung/tambah', 'Admin\RabGedung::create');
	$routes->post('laporan/rab-gedung/(:num)/ubah', 'Admin\RabGedung::edit/$1');
	$routes->post('laporan/rab-gedung/(:num)/hapus', 'Admin\RabGedung::delete/$1');
	$routes->post('laporan/rab-gedung/import', 'Admin\RabGedung::import');
	$routes->post('laporan/rab-gedung/sekolah/(:num)/ubah-paket', 'Admin\RabGedung::updateSekolahPaket/$1');

	// Rekapitulasi Mingguan Routes
	$routes->get('laporan/rekap-mingguan', 'Admin\RekapMingguan::index');
	$routes->get('laporan/rekap-mingguan/show/(:num)', 'Admin\RekapMingguan::show/$1');
	$routes->get('laporan/rekap-mingguan/export/(:num)', 'Admin\RekapMingguan::export/$1');
	$routes->get('laporan/rekap-mingguan/detail/(:num)', 'Admin\RekapMingguan::detail/$1');
	$routes->get('laporan/rekap-mingguan/data-detail/(:num)', 'Admin\RekapMingguan::dataDetail/$1');
	$routes->post('laporan/rekap-mingguan/tambah', 'Admin\RekapMingguan::create');
	$routes->post('laporan/rekap-mingguan/(:num)/ubah', 'Admin\RekapMingguan::edit/$1');
	$routes->post('laporan/rekap-mingguan/(:num)/hapus', 'Admin\RekapMingguan::delete/$1');
	$routes->post('laporan/rekap-mingguan/import', 'Admin\RekapMingguan::import');

	// Laporan Lapangan (Mobile API submissions)
	$routes->get('laporan/lapangan', 'Admin\LaporanLapangan::index');
	$routes->get('laporan/lapangan/detail/(:segment)/(:num)', 'Admin\LaporanLapangan::detail/$1/$2');
	$routes->get('laporan/lapangan/(:num)', 'Admin\LaporanLapangan::detailLegacy/$1');

	// Surat Routes - Perjalanan Dinas (alias dari Laporan)
	$routes->get('surat/perjalanan-dinas', 'Admin\\Laporan::perjalananDinas');
	$routes->get('surat/perjalanan-dinas/surat-tugas', 'Admin\\Laporan::suratTugas');
	$routes->get('surat/perjalanan-dinas/cetak-periode', 'Admin\\Laporan::perjalananDinasCetakPeriode');
	$routes->match(['get', 'post'], 'surat/perjalanan-dinas/buat', 'Admin\\Laporan::perjalananDinasBuat');
	$routes->get('surat/perjalanan-dinas/(:num)/dokumen', 'Admin\\Laporan::perjalananDinasDokumen/$1');
	$routes->post('surat/perjalanan-dinas/(:num)/upload-verified', 'Admin\\Laporan::perjalananDinasUploadVerified/$1');
	$routes->match(['get', 'post'], 'surat/perjalanan-dinas/(:num)/ubah', 'Admin\\Laporan::perjalananDinasEdit/$1');
	$routes->get('surat/perjalanan-dinas/(:num)/hapus', 'Admin\\Laporan::perjalananDinasHapus/$1');
	$routes->post('surat/perjalanan-dinas/(:num)/verify', 'Admin\Laporan::perjalananDinasVerify/$1');
	$routes->get('surat/perjalanan-dinas/(:num)/cetak-spt', 'Admin\Laporan::perjalananDinasCetakSpt/$1');
	$routes->get('surat/perjalanan-dinas/(:num)/cetak-daftar-nominatif', 'Admin\Laporan::perjalananDinasCetakDaftarNominatif/$1');
	$routes->get('surat/perjalanan-dinas/(:num)/cetak-sppd', 'Admin\Laporan::perjalananDinasCetakSppd/$1');
	$routes->get('surat/perjalanan-dinas/(:num)/cetak-kwitansi', 'Admin\Laporan::perjalananDinasCetakKwitansi/$1');

	// Surat Routes - Lupa Absen
	$routes->get('surat/lupa-absen', 'Admin\\LupaAbsen::index');
	$routes->match(['get', 'post'], 'surat/lupa-absen/buat', 'Admin\\LupaAbsen::buat');
	$routes->match(['get', 'post'], 'surat/lupa-absen/(:num)/ubah', 'Admin\\LupaAbsen::ubah/$1');
	$routes->get('surat/lupa-absen/(:num)/pdf', 'Admin\\LupaAbsen::pdf/$1');
	$routes->get('surat/lupa-absen/(:num)/hapus', 'Admin\\LupaAbsen::hapus/$1');
	$routes->get('surat/lupa-absen/(:num)/approve', 'Admin\\LupaAbsen::approve/$1');
	$routes->get('surat/lupa-absen/(:num)/reject', 'Admin\\LupaAbsen::reject/$1');

	// Surat Routes - Disposisi Perjalanan Dinas
	$routes->get('surat/perjalanan-dinas/disposisi', 'Admin\DisposisiPerjalananDinas::index');
	$routes->match(['get', 'post'], 'surat/perjalanan-dinas/disposisi/buat', 'Admin\DisposisiPerjalananDinas::buat');
	$routes->match(['get', 'post'], 'surat/perjalanan-dinas/disposisi/(:num)/ubah', 'Admin\DisposisiPerjalananDinas::ubah/$1');
	$routes->get('surat/perjalanan-dinas/disposisi/(:num)/pdf', 'Admin\DisposisiPerjalananDinas::pdf/$1');
	$routes->get('surat/perjalanan-dinas/disposisi/(:num)/hapus', 'Admin\DisposisiPerjalananDinas::hapus/$1');
	$routes->match(['get', 'post'], 'surat/perjalanan-dinas/disposisi/(:num)/setujui', 'Admin\DisposisiPerjalananDinas::setujui/$1');
	$routes->match(['get', 'post'], 'surat/perjalanan-dinas/disposisi/(:num)/tolak', 'Admin\DisposisiPerjalananDinas::tolak/$1');
	$routes->get('surat/perjalanan-dinas/disposisi/(:num)/kirim-email', 'Admin\DisposisiPerjalananDinas::kirimEmail/$1');

	$routes->get('kontrak/export/(:any)/(:num)', 'Admin\\Kontrak::exportDocument/$1/$2');
	$routes->post('slide/tambah', 'Admin\\HomeSetting::createSlide');
	$routes->post('slide/(:num)/ubah', 'Admin\\HomeSetting::updateSlide/$1');
	$routes->post('slide/(:num)/hapus', 'Admin\\HomeSetting::deleteSlide/$1');

	$routes->get('dokumentasi/kegiatan-lapangan', 'Admin\\Dokumentasi::index');
	$routes->get('dokumentasi/kegiatan-lapangan/data', 'Admin\\Dokumentasi::dataTable');
	$routes->get('dokumentasi/kegiatan-lapangan/(:num)/download-zip', 'Admin\\Dokumentasi::downloadZip/$1');
	$routes->post('dokumentasi/kegiatan-lapangan/(:num)/share', 'Admin\\Dokumentasi::createShare/$1');
	$routes->post('dokumentasi/kegiatan-lapangan/(:num)/share/deactivate', 'Admin\\Dokumentasi::deactivateShare/$1');
	$routes->match(['get', 'post'], 'dokumentasi/kegiatan-lapangan/tambah', 'Admin\\Dokumentasi::create');
	$routes->match(['get', 'post'], 'dokumentasi/kegiatan-lapangan/(:num)/ubah', 'Admin\\Dokumentasi::edit/$1');
	$routes->post('dokumentasi/kegiatan-lapangan/(:num)/hapus', 'Admin\\Dokumentasi::delete/$1');

	// Watermark Foto
	$routes->get('dokumentasi/watermark-foto', 'Admin\\Dokumentasi::watermarkFoto');
	$routes->post('dokumentasi/watermark-foto/proses', 'Admin\\Dokumentasi::prosesWatermark');

	$routes->get('acara', 'Admin\\Event::index');
	$routes->match(['get', 'post'], 'acara/tambah', 'Admin\\Event::create');
	$routes->match(['get', 'post'], 'acara/(:num)/ubah', 'Admin\\Event::edit/$1');
	$routes->post('acara/(:num)/hapus', 'Admin\\Event::delete/$1');

	$routes->get('berita', 'Admin\\Article::index');
	$routes->match(['get', 'post'], 'berita/tambah', 'Admin\\Article::create');
	$routes->match(['get', 'post'], 'berita/(:num)/ubah', 'Admin\\Article::edit/$1');
	$routes->post('berita/(:num)/hapus', 'Admin\\Article::delete/$1');
});

$routes->group('api', static function ($routes): void {
    // API Documentation (Swagger UI)
    $routes->get('docs', 'ApiDocs::index');

    // Public API Routes
    $routes->post('auth/login', 'Api\Auth::login');

    // Protected API Routes (requires API Auth Filter)
    $routes->group('', ['filter' => 'api-auth'], static function ($routes): void {
        $routes->get('auth/profile', 'Api\Auth::profile');
        $routes->post('auth/logout', 'Api\Auth::logout');

        // RAB Gedung API
        $routes->group('rab-gedung', static function ($routes): void {
            $routes->get('all', 'Api\RabGedung::all');
            $routes->get('paket', 'Api\RabGedung::paket');
            $routes->get('sekolah', 'Api\RabGedung::sekolah');
            $routes->get('pekerjaan', 'Api\RabGedung::pekerjaan');
            $routes->get('kategori', 'Api\RabGedung::kategori');
            $routes->get('subkategori', 'Api\RabGedung::subkategori');
            $routes->get('uraian', 'Api\RabGedung::uraian');
        });

        // Laporan Harian (Mobile) API
        $routes->post('laporan-harian/proyek', 'Api\LaporanHarian::proyek');
        $routes->post('laporan-harian/pekerjaan', 'Api\LaporanHarian::pekerjaan');
    });
});

