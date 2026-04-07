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

$routes->get('masuk', 'Auth::loginForm');
$routes->post('masuk', 'Auth::login');
$routes->get('keluar', 'Auth::logout');
$routes->get('forbidden', 'Home::forbidden');

$routes->group('admin', ['filter' => 'auth:admin,editor'], static function ($routes): void {
	$routes->get('/', 'Admin\\Dashboard::index');

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
	$routes->get('master/kop-surat', 'Admin\\KopSurat::index');
	$routes->match(['get', 'post'], 'master/kop-surat/tambah', 'Admin\\KopSurat::create');
	$routes->match(['get', 'post'], 'master/kop-surat/(:num)/ubah', 'Admin\\KopSurat::edit/$1');
	$routes->post('master/kop-surat/(:num)/status', 'Admin\\KopSurat::updateStatus/$1');
	$routes->post('master/kop-surat/(:num)/hapus', 'Admin\\KopSurat::delete/$1');
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

	$routes->get('acara', 'Admin\\Event::index');
	$routes->match(['get', 'post'], 'acara/tambah', 'Admin\\Event::create');
	$routes->match(['get', 'post'], 'acara/(:num)/ubah', 'Admin\\Event::edit/$1');
	$routes->post('acara/(:num)/hapus', 'Admin\\Event::delete/$1');

	$routes->get('berita', 'Admin\\Article::index');
	$routes->match(['get', 'post'], 'berita/tambah', 'Admin\\Article::create');
	$routes->match(['get', 'post'], 'berita/(:num)/ubah', 'Admin\\Article::edit/$1');
	$routes->post('berita/(:num)/hapus', 'Admin\\Article::delete/$1');
});
