<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'PublicController::index');
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::prosesLogin');
$routes->post('/logout', 'AuthController::logout', ['filter' => 'auth']);

$routes->get('/file/dokumen/(:num)/download', 'FileController::dokumen/$1');
$routes->get('/file/dokumen/(:num)/preview', 'FileController::previewDokumen/$1');

// Public Portal Routes (no authentication required)
$routes->group('portal', static function ($routes) {
    $routes->get('profil', 'PublicController::profil');
    $routes->get('kriteria', 'PublicController::kriteria');
    $routes->get('kriteria/(:num)', 'PublicController::kriteriaDetail/$1');
    $routes->get('dokumen-penting', 'PublicController::dokumenPenting');
    $routes->get('pencarian', 'PublicController::pencarian');
});

// Alias tambahan bila sewaktu-waktu tetap ingin memakai prefix /public di level route.
$routes->group('public', static function ($routes) {
    $routes->get('/', 'PublicController::index');
    $routes->get('profil', 'PublicController::profil');
    $routes->get('kriteria', 'PublicController::kriteria');
    $routes->get('kriteria/(:num)', 'PublicController::kriteriaDetail/$1');
    $routes->get('dokumen-penting', 'PublicController::dokumenPenting');
    $routes->get('pencarian', 'PublicController::pencarian');
});

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/dashboard', 'DashboardController::index');

    $routes->get('/kriteria', 'KriteriaController::index', ['filter' => 'role:admin,lpm,dekan,kaprodi,dosen']);
    $routes->get('/kriteria/(:num)', 'KriteriaController::show/$1', ['filter' => 'role:admin,lpm,dekan,kaprodi,dosen']);

    $routes->get('/peraturan', 'PeraturanController::index', ['filter' => 'role:admin,lpm,dekan,kaprodi,dosen']);
    $routes->get('/peraturan/create', 'PeraturanController::create', ['filter' => 'role:admin,lpm']);
    $routes->post('/peraturan/store', 'PeraturanController::store', ['filter' => 'role:admin,lpm']);
    $routes->get('/peraturan/(:num)/edit', 'PeraturanController::edit/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/peraturan/(:num)/update', 'PeraturanController::update/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/peraturan/(:num)/delete', 'PeraturanController::delete/$1', ['filter' => 'role:admin,lpm']);

    $routes->get('/instrumen', 'InstrumenController::index', ['filter' => 'role:admin,lpm,dekan,kaprodi,dosen']);
    $routes->get('/instrumen/create', 'InstrumenController::create', ['filter' => 'role:admin,lpm']);
    $routes->post('/instrumen/store', 'InstrumenController::store', ['filter' => 'role:admin,lpm']);
    $routes->get('/instrumen/(:num)/edit', 'InstrumenController::edit/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/instrumen/(:num)/update', 'InstrumenController::update/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/instrumen/(:num)/delete', 'InstrumenController::delete/$1', ['filter' => 'role:admin,lpm']);

    $routes->get('/kriteria/(:num)/sub-bagian/create', 'SubBagianController::create/$1', ['filter' => 'role:admin,lpm,kaprodi']);
    $routes->post('/kriteria/(:num)/sub-bagian/store', 'SubBagianController::store/$1', ['filter' => 'role:admin,lpm,kaprodi']);
        $routes->get('/kriteria/(:num)/dokumen/create', 'DokumenController::createByKriteria/$1', ['filter' => 'role:admin,lpm,kaprodi,dosen']);

    $routes->get('/sub-bagian/(:num)/edit', 'SubBagianController::edit/$1', ['filter' => 'role:admin,lpm,kaprodi']);
    $routes->post('/sub-bagian/(:num)/update', 'SubBagianController::update/$1', ['filter' => 'role:admin,lpm,kaprodi']);
    $routes->post('/sub-bagian/(:num)/delete', 'SubBagianController::delete/$1', ['filter' => 'role:admin,lpm,kaprodi']);

        $routes->get('/sub-bagian/(:num)/dokumen/create', 'DokumenController::create/$1', ['filter' => 'role:admin,lpm,kaprodi,dosen']);
        $routes->post('/sub-bagian/(:num)/dokumen/store', 'DokumenController::store/$1', ['filter' => 'role:admin,lpm,kaprodi,dosen']);

    $routes->get('/dokumen/(:num)', 'DokumenController::show/$1');
        $routes->get('/dokumen/(:num)/edit', 'DokumenController::edit/$1', ['filter' => 'role:admin,lpm,kaprodi,dosen']);
        $routes->post('/dokumen/(:num)/update', 'DokumenController::update/$1', ['filter' => 'role:admin,lpm,kaprodi,dosen']);
        $routes->get('/dokumen/(:num)/review', 'DokumenController::review/$1', ['filter' => 'role:admin,lpm']);
        $routes->post('/dokumen/(:num)/finalisasi', 'DokumenController::finalisasi/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/dokumen/(:num)/delete', 'DokumenController::delete/$1', ['filter' => 'role:admin,lpm,kaprodi,dosen']);
    $routes->post('/dokumen/bulk-delete', 'DokumenController::bulkDelete', ['filter' => 'role:admin,lpm,kaprodi,dosen']);

    $routes->get('/file/peraturan/(:num)/download', 'FileController::peraturan/$1', ['filter' => 'role:admin,lpm,dekan,kaprodi,dosen']);
    $routes->get('/file/peraturan/(:num)/preview', 'FileController::previewPeraturan/$1', ['filter' => 'role:admin,lpm,dekan,kaprodi,dosen']);

    $routes->get('/file/instrumen/(:num)/download', 'FileController::instrumen/$1', ['filter' => 'role:admin,lpm,dekan,kaprodi,dosen']);
    $routes->get('/file/instrumen/(:num)/preview', 'FileController::previewInstrumen/$1', ['filter' => 'role:admin,lpm,dekan,kaprodi,dosen']);
    $routes->get('/file/profil-pt/sk/download', 'FileController::profilPtSk', ['filter' => 'role:admin,lpm']);
    $routes->get('/file/profil-pt/sertifikat/download', 'FileController::profilPtSertifikat', ['filter' => 'role:admin,lpm']);
    $routes->get('/file/documents/(:num)/download', 'FileController::legacyDocument/$1', ['filter' => 'role:admin,lpm']);

    $routes->get('/users', 'UserController::index', ['filter' => 'role:admin']);
    $routes->get('/users/template-excel', 'UserController::templateExcel', ['filter' => 'role:admin']);
    $routes->post('/users/impor', 'UserController::impor', ['filter' => 'role:admin']);
    $routes->get('/users/create', 'UserController::create', ['filter' => 'role:admin']);
    $routes->post('/users/store', 'UserController::store', ['filter' => 'role:admin']);
    $routes->get('/users/(:num)/edit', 'UserController::edit/$1', ['filter' => 'role:admin']);
    $routes->post('/users/(:num)/update', 'UserController::update/$1', ['filter' => 'role:admin']);
    $routes->post('/users/(:num)/delete', 'UserController::delete/$1', ['filter' => 'role:admin']);
    $routes->post('/users/(:num)/impersonate', 'UserController::impersonate/$1', ['filter' => 'role:admin']);
    $routes->post('/users/impersonation/stop', 'UserController::stopImpersonation');
    $routes->get('/profil-pt', 'ProfilPtController::index', ['filter' => 'role:admin,lpm']);
    $routes->post('/profil-pt/update', 'ProfilPtController::update', ['filter' => 'role:admin,lpm']);
    $routes->get('/upps', 'UppsController::index', ['filter' => 'role:admin,lpm']);
    $routes->get('/upps/create', 'UppsController::create', ['filter' => 'role:admin,lpm']);
    $routes->post('/upps/store', 'UppsController::store', ['filter' => 'role:admin,lpm']);
    $routes->get('/upps/(:num)/edit', 'UppsController::edit/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/upps/(:num)/update', 'UppsController::update/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/upps/(:num)/delete', 'UppsController::delete/$1', ['filter' => 'role:admin,lpm']);
    $routes->get('/program-studi', 'ProgramStudiController::index', ['filter' => 'role:admin,lpm']);
    $routes->get('/program-studi/create', 'ProgramStudiController::create', ['filter' => 'role:admin,lpm']);
    $routes->post('/program-studi/store', 'ProgramStudiController::store', ['filter' => 'role:admin,lpm']);
    $routes->get('/program-studi/(:num)/edit', 'ProgramStudiController::edit/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/program-studi/(:num)/update', 'ProgramStudiController::update/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/program-studi/(:num)/delete', 'ProgramStudiController::delete/$1', ['filter' => 'role:admin,lpm']);
    $routes->get('/lembaga-akreditasi', 'LembagaAkreditasiController::index', ['filter' => 'role:admin,lpm']);
    $routes->get('/lembaga-akreditasi/create', 'LembagaAkreditasiController::create', ['filter' => 'role:admin,lpm']);
    $routes->post('/lembaga-akreditasi/store', 'LembagaAkreditasiController::store', ['filter' => 'role:admin,lpm']);
    $routes->get('/lembaga-akreditasi/(:num)/edit', 'LembagaAkreditasiController::edit/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/lembaga-akreditasi/(:num)/update', 'LembagaAkreditasiController::update/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/lembaga-akreditasi/(:num)/delete', 'LembagaAkreditasiController::delete/$1', ['filter' => 'role:admin,lpm']);
    $routes->get('/jenis-dokumen', 'JenisDokumenController::index', ['filter' => 'role:admin,lpm']);
    $routes->get('/jenis-dokumen/create', 'JenisDokumenController::create', ['filter' => 'role:admin,lpm']);
    $routes->post('/jenis-dokumen/store', 'JenisDokumenController::store', ['filter' => 'role:admin,lpm']);
    $routes->get('/jenis-dokumen/(:num)/edit', 'JenisDokumenController::edit/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/jenis-dokumen/(:num)/update', 'JenisDokumenController::update/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/jenis-dokumen/(:num)/delete', 'JenisDokumenController::delete/$1', ['filter' => 'role:admin,lpm']);
    $routes->get('/master-dokumen-kriteria', 'MasterDokumenKriteriaController::index', ['filter' => 'role:admin,lpm']);
    $routes->get('/master-dokumen-kriteria/template-excel', 'MasterDokumenKriteriaController::templateExcel', ['filter' => 'role:admin,lpm']);
    $routes->post('/master-dokumen-kriteria/impor', 'MasterDokumenKriteriaController::impor', ['filter' => 'role:admin,lpm']);
    $routes->get('/master-dokumen-kriteria/create', 'MasterDokumenKriteriaController::create', ['filter' => 'role:admin,lpm']);
    $routes->post('/master-dokumen-kriteria/store', 'MasterDokumenKriteriaController::store', ['filter' => 'role:admin,lpm']);
    $routes->get('/master-dokumen-kriteria/(:num)/edit', 'MasterDokumenKriteriaController::edit/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/master-dokumen-kriteria/(:num)/update', 'MasterDokumenKriteriaController::update/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/master-dokumen-kriteria/(:num)/delete', 'MasterDokumenKriteriaController::delete/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/master-dokumen-kriteria/bulk-delete', 'MasterDokumenKriteriaController::bulkDelete', ['filter' => 'role:admin,lpm']);
    $routes->post('/master-dokumen-kriteria/generate', 'MasterDokumenKriteriaController::generate', ['filter' => 'role:admin,lpm']);
    $routes->get('/audit-trail', 'AuditTrailController::index', ['filter' => 'role:admin,lpm']);
    $routes->post('/audit-trail/bulk-delete', 'AuditTrailController::bulkDelete', ['filter' => 'role:admin,lpm']);
    $routes->get('/pengaturan/aplikasi', 'PengaturanAplikasiController::index', ['filter' => 'role:admin,lpm']);
    $routes->post('/pengaturan/aplikasi/update', 'PengaturanAplikasiController::update', ['filter' => 'role:admin,lpm']);
    $routes->get('/pengaturan/manajemen-prodi-akreditasi', 'ManajemenProdiAkreditasiController::index', ['filter' => 'role:admin,lpm']);
    $routes->post('/pengaturan/manajemen-prodi-akreditasi/(:num)/toggle', 'ManajemenProdiAkreditasiController::toggle/$1', ['filter' => 'role:admin']);

    $routes->get('/laporan', 'LaporanController::index', ['filter' => 'role:admin,lpm,dekan,kaprodi']);
    $routes->get('/laporan/export/excel', 'LaporanExportController::excel', ['filter' => 'role:admin,lpm,dekan,kaprodi']);
    $routes->get('/laporan/export/pdf', 'LaporanExportController::pdf', ['filter' => 'role:admin,lpm,dekan,kaprodi']);
    $routes->get('/profil', 'ProfileController::index');
    $routes->get('/profil/edit', 'ProfileController::edit');
    $routes->post('/profil/update', 'ProfileController::update');

    // Document Workflow Routes
    $routes->get('/documents', 'DocumentController::index', ['filter' => 'role:admin,lpm']);
    $routes->get('/documents/create', 'DocumentController::create', ['filter' => 'role:admin,lpm']);
    $routes->post('/documents/store', 'DocumentController::store', ['filter' => 'role:admin,lpm']);
    $routes->get('/documents/edit/(:num)', 'DocumentController::edit/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/documents/update/(:num)', 'DocumentController::update/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/documents/submit/(:num)', 'DocumentController::submit/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/documents/resubmit/(:num)', 'DocumentController::resubmit/$1', ['filter' => 'role:admin,lpm']);

    // Review Routes (LPM)
    $routes->get('/reviews', 'ReviewController::index', ['filter' => 'role:admin,lpm']);
    $routes->get('/reviews/(:num)', 'ReviewController::show/$1', ['filter' => 'role:admin,lpm']);
    $routes->post('/reviews/(:num)/review', 'ReviewController::review/$1', ['filter' => 'role:admin,lpm']);
});
