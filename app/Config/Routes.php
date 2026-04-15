<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
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

$routes->get('masuk', 'Auth::loginForm');
$routes->post('masuk', 'Auth::login');
$routes->get('keluar', 'Auth::logout');
$routes->get('forbidden', 'Home::forbidden');

$routes->group('admin', ['filter' => 'auth:admin,editor'], static function ($routes): void {
	$routes->get('/', 'Admin\\Dashboard::index');
	$routes->get('map', 'Admin\\Dashboard::map');
	$routes->get('dashboard/map', 'Admin\\Dashboard::map');
	$routes->get('dashboard/map-data', 'Admin\\Dashboard::mapData');
	$routes->get('dashboard/map-kecamatan-options', 'Admin\\Dashboard::mapKecamatanOptions');
	$routes->get('dashboard/map-detail', 'Admin\\Dashboard::mapDetail');

	// Update password user
	$routes->get('password', 'Admin\\Password::index');
	$routes->post('password/update', 'Admin\\Password::update');

	$routes->match(['get', 'post'], 'pengaturan-home', 'Admin\\HomeSetting::index');
	$routes->match(['get', 'post'], 'pengaturan/application', 'Admin\\Setting::application');
	$routes->post('pengaturan/application/reset-sidebar', 'Admin\\Setting::resetSidebarDefaults');
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
	$routes->get('utility/user', 'Admin\\Utility::user');
	$routes->get('utility/user/list', 'Admin\\Utility::userList');
	$routes->post('utility/user/tambah', 'Admin\\Utility::userCreate');
	$routes->post('utility/user/(:num)/ubah', 'Admin\\Utility::userUpdate/$1');
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
	$routes->get('kontrak/simak', 'Admin\\Kontrak::simak');
	$routes->post('kontrak/simak/import', 'Admin\\Kontrak::importSimak');
	$routes->get('kontrak/simak/template', 'Admin\\Kontrak::exportSimakTemplate');
	$routes->post('kontrak/simak/tambah', 'Admin\\Kontrak::createSimak');
	$routes->post('kontrak/simak/(:num)/ubah', 'Admin\\Kontrak::updateSimak/$1');
	$routes->post('kontrak/simak/(:num)/verifikasi', 'Admin\\Kontrak::saveSimakVerifikasi/$1');
	$routes->post('kontrak/simak/(:num)/verifikasi/upload', 'Admin\\Kontrak::uploadSimakVerifikasiDokumen/$1');
	$routes->get('kontrak/simak/verifikasi-dokumen/(:num)', 'Admin\\Kontrak::viewSimakVerifikasiDokumen/$1');
	$routes->get('kontrak/simak/(:num)', 'Admin\\Kontrak::detailSimak/$1');
	$routes->get('master/kop-surat', 'Admin\\KopSurat::index');
	$routes->match(['get', 'post'], 'master/kop-surat/tambah', 'Admin\\KopSurat::create');
	$routes->match(['get', 'post'], 'master/kop-surat/(:num)/ubah', 'Admin\\KopSurat::edit/$1');
	$routes->post('master/kop-surat/(:num)/status', 'Admin\\KopSurat::updateStatus/$1');
	$routes->post('master/kop-surat/(:num)/hapus', 'Admin\\KopSurat::delete/$1');
	$routes->get('master/sekolah', 'Admin\\MasterSekolah::index');
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

	$routes->get('acara', 'Admin\\Event::index');
	$routes->match(['get', 'post'], 'acara/tambah', 'Admin\\Event::create');
	$routes->match(['get', 'post'], 'acara/(:num)/ubah', 'Admin\\Event::edit/$1');
	$routes->post('acara/(:num)/hapus', 'Admin\\Event::delete/$1');

	$routes->get('berita', 'Admin\\Article::index');
	$routes->match(['get', 'post'], 'berita/tambah', 'Admin\\Article::create');
	$routes->match(['get', 'post'], 'berita/(:num)/ubah', 'Admin\\Article::edit/$1');
	$routes->post('berita/(:num)/hapus', 'Admin\\Article::delete/$1');
});
