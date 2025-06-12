<?php

// Gunakan satu namespace saja untuk controller
namespace App\Http\Controllers;

// Impor controller yang diperlukan
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataKegiatanController;
use App\Http\Controllers\DetailMonitoringKegiatanController;
use App\Http\Controllers\MonitoringKegiatanController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\KelolaUserController;
use App\Http\Controllers\SatuanKerjaController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\TimKerjaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'login'])->name('login');
    Route::post('/authenticate', [SessionController::class, 'authentication'])->name('authenticate');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [BerandaController::class, 'index'])->name('index');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/get-objek', [DashboardController::class, 'getObjek'])->name('get.objek');
    Route::get('/get-periode', [DashboardController::class, 'getPeriode'])->name('get.periode');
    Route::get('/get-periode-kegiatan', [DashboardController::class, 'getPeriodeKegiatan'])->name('get.periodeKegiatan');
    Route::get('/get-nama-kegiatan', [DashboardController::class, 'getNamaKegiatan'])->name('get.namaKegiatan');
    Route::get('/fetch-data', [DashboardController::class, 'fetchData'])->name('fetch.data');
    Route::get('/fetch-data', [DashboardController::class, 'fetchData']);
    Route::get('/get-filtered-data', [DashboardController::class, 'getFilteredData'])->name('get.filteredData');

    Route::get('monitoring-kegiatan/detail-monitoring-kegiatan/{id}', [DetailMonitoringKegiatanController::class, 'show'])->name('detail-monitoring-kegiatan');
    Route::get('/riwayat-update-realisasi/{satuanKerjaId}/{monitoringKegiatanId}', [DetailMonitoringKegiatanController::class, 'getRiwayatRealisasi']);
    Route::get('export-riwayat-realisasi/{monitoringKegiatanId}/{satuanKerjaId}', [DetailMonitoringKegiatanController::class, 'exportRiwayatRealisasi'])->name('export-riwayat-realisasi');

    Route::get('/monitoring-kegiatan', [MonitoringKegiatanController::class, 'index'])->name('monitoring-kegiatan');
    Route::get('/get-kegiatan-by-tim', [MonitoringKegiatanController::class, 'getKegiatanByTim']);
    Route::post('/monitoring-kegiatan/store', [MonitoringKegiatanController::class, 'store'])->name('monitoring-kegiatan.store');
    Route::put('/monitoring-kegiatan/update/{id}', [MonitoringKegiatanController::class, 'update'])->name('monitoringkegiatan.update');
    Route::delete('/monitoring-kegiatan/destroy/{id}', [MonitoringKegiatanController::class, 'destroy'])->name('monitoringkegiatan.destroy');
    Route::post('/monitoring-kegiatan/import', [MonitoringKegiatanController::class, 'import'])->name('monitoring-kegiatan.import');
    Route::get('/monitoring-kegiatan/download-format', [MonitoringKegiatanController::class, 'downloadFormat'])->name('monitoring-kegiatan.download-format');

    Route::get('/notifications/pending-verifikasi', [DetailMonitoringKegiatanController::class, 'getPendingVerifikasiNotifications'])
        ->name('notifications.pending-verifikasi');

    Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian');
    Route::post('/penilaian/generate', [PenilaianController::class, 'generate'])->name('penilaian.generate');

    Route::get('/sertifikat', [SertifikatController::class, 'index'])->name('sertifikat');
    Route::get('/sertifikat/index', [SertifikatController::class, 'index'])->name('sertifikat')->name('sertifikat.index');
    Route::get('/sertifikat/view', [SertifikatController::class, 'generateCertificate'])->name('viewCertificate');
    Route::get('/sertifikat/download', [SertifikatController::class, 'generateCertificate'])->name('downloadCertificate');
    Route::get('/generate-certificate/{sertifikatId}', [SertifikatController::class, 'generateCertificate'])
        ->name('generateCertificate');

    Route::get('/edit-sertifikat', [EditSertifikatController::class, 'edit'])->name('edit-sertifikat.edit');
    Route::put('/edit-sertifikat', [EditSertifikatController::class, 'update'])->name('edit-sertifikat.update');

    Route::get('/tim-kerja', [TimKerjaController::class, 'index'])->name('tim-kerja');
    Route::get('/tim-kerja/index', [TimKerjaController::class, 'index'])->name('timkerja.index');
    Route::post('/tim-kerja/store', [TimKerjaController::class, 'store'])->name('timkerja.store');
    Route::put('/tim-kerja/update/{id}', [TimKerjaController::class, 'update'])->name('timkerja.update');
    Route::delete('/tim-kerja/destroy/{id}', [TimKerjaController::class, 'destroy'])->name('timkerja.destroy');
    Route::post('/tim-kerja/import', [TimKerjaController::class, 'import'])->name('timkerja.import');
    Route::get('/tim-kerja/download-format', [TimKerjaController::class, 'downloadFormat'])->name('timkerja.download-format');

    Route::get('/satuan-kerja', [SatuanKerjaController::class, 'index'])->name('satuan-kerja');
    Route::get('/satuan-kerja/index', [SatuanKerjaController::class, 'index'])->name('satuankerja.index');
    Route::post('/satuan-kerja/store', [SatuanKerjaController::class, 'store'])->name('satuankerja.store');
    Route::put('/satuan-kerja/update/{id}', [SatuanKerjaController::class, 'update'])->name('satuankerja.update');
    Route::delete('/satuan-kerja/destroy/{id}', [SatuanKerjaController::class, 'destroy'])->name('satuankerja.destroy');
    Route::post('/satuan-kerja/import', [SatuanKerjaController::class, 'import'])->name('satuankerja.import');
    Route::get('/satuan-kerja/download-format', [SatuanKerjaController::class, 'downloadFormat'])->name('satuankerja.download-format');

    Route::get('/data-kegiatan', [DataKegiatanController::class, 'index'])->name('data-kegiatan');
    Route::get('/data-kegiatan/index', [DataKegiatanController::class, 'index'])->name('datakegiatan.index');
    Route::post('/data-kegiatan/store', [DataKegiatanController::class, 'store'])->name('datakegiatan.store');
    Route::put('/data-kegiatan/update/{id}', [DataKegiatanController::class, 'update'])->name('datakegiatan.update');
    Route::delete('/data-kegiatan/destroy/{id}', [DataKegiatanController::class, 'destroy'])->name('datakegiatan.destroy');
    Route::post('/data-kegiatan/import', [DataKegiatanController::class, 'import'])->name('datakegiatan.import');
    Route::get('/data-kegiatan/download-format', [DataKegiatanController::class, 'downloadFormat'])->name('datakegiatan.download-format');

    Route::get('/kelola-user', [KelolaUserController::class, 'index'])->name('kelola-user');
    Route::get('/kelola-user/index', [KelolaUserController::class, 'index'])->name('kelolauser.index');
    Route::post('/kelola-user/store', [KelolaUserController::class, 'store'])->name('kelolauser.store');
    Route::put('/kelola-user/update/{id}', [KelolaUserController::class, 'update'])->name('kelolauser.update');
    Route::delete('/kelola-user/destroy/{id}', [KelolaUserController::class, 'destroy'])->name('kelolauser.destroy');
    Route::post('/kelola-user/import', [KelolaUserController::class, 'import'])->name('kelolauser.import');

    Route::get('/kelola-user/download-format', [KelolaUserController::class, 'downloadFormat'])->name('kelolauser.download-format');

    Route::post('/update-realisasi', [UpdateRealisasiController::class, 'create'])->name('update-realisasi');
    Route::post('/approve-usulan', [UpdateRealisasiController::class, 'approve'])->name('approve-usulan');
    Route::get('/export-realisasi/{id_kegiatan}', [UpdateRealisasiController::class, 'export'])->name('export-realisasi');

    Route::get('logout', [SessionController::class, 'logout'])->name('logout');
});
