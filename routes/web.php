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
use App\Http\Controllers\SatuanKerjaController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\TimKerjaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserController;


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

    Route::get('detail-monitoring-kegiatan/{id}', [DetailMonitoringKegiatanController::class, 'show'])->name('detail-monitoring-kegiatan');

    Route::get('/monitoring-kegiatan', [MonitoringKegiatanController::class, 'index'])->name('monitoring-kegiatan');
    Route::get('/get-kegiatan-by-tim', [MonitoringKegiatanController::class, 'getKegiatanByTim']);
    Route::post('/monitoring-kegiatan/store', [MonitoringKegiatanController::class, 'store'])->name('monitoring-kegiatan.store');
    Route::delete('/monitoring-kegiatan/update/{id}', [MonitoringKegiatanController::class, 'update'])->name('monitoringkegiatan.update');
    Route::delete('/monitoring-kegiatan/destroy/{id}', [MonitoringKegiatanController::class, 'destroy'])->name('monitoringkegiatan.destroy');

    Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian');
    Route::post('/penilaian/generate', [PenilaianController::class, 'generate'])->name('penilaian.generate');

    Route::get('/sertifikat', [SertifikatController::class, 'index'])->name('sertifikat');
    Route::get('/sertifikat/index', [SertifikatController::class, 'index'])->name('sertifikat')->name('sertifikat.index');
    Route::get('/sertifikat/view', [SertifikatController::class, 'generateCertificate'])->name('viewCertificate');
    Route::get('/sertifikat/download', [SertifikatController::class, 'generateCertificate'])->name('downloadCertificate');
    Route::get('/generate-certificate/{sertifikatId}', [SertifikatController::class, 'generateCertificate'])
        ->name('generateCertificate');

    Route::get('/tim-kerja', [TimKerjaController::class, 'index'])->name('tim-kerja');
    Route::get('/tim-kerja/index', [TimKerjaController::class, 'index'])->name('timkerja.index');
    Route::post('/tim-kerja/store', [TimKerjaController::class, 'store'])->name('timkerja.store');
    Route::put('/tim-kerja/update/{id}', [TimKerjaController::class, 'update'])->name('timkerja.update');
    Route::delete('/tim-kerja/destroy/{id}', [TimKerjaController::class, 'destroy'])->name('timkerja.destroy');

    Route::get('/satuan-kerja', [SatuanKerjaController::class, 'index'])->name('satuan-kerja');
    Route::get('/satuan-kerja/index', [SatuanKerjaController::class, 'index'])->name('satuankerja.index');
    Route::post('/satuan-kerja/store', [SatuanKerjaController::class, 'store'])->name('satuankerja.store');
    Route::put('/satuan-kerja/update/{id}', [SatuanKerjaController::class, 'update'])->name('satuankerja.update');
    Route::delete('/satuan-kerja/destroy/{id}', [SatuanKerjaController::class, 'destroy'])->name('satuankerja.destroy');

    Route::get('/data-kegiatan', [DataKegiatanController::class, 'index'])->name('data-kegiatan');
    Route::get('/data-kegiatan/index', [DataKegiatanController::class, 'index'])->name('datakegiatan.index');
    Route::post('/data-kegiatan/store', [DataKegiatanController::class, 'store'])->name('datakegiatan.store');
    Route::put('/data-kegiatan/update/{id}', [DataKegiatanController::class, 'update'])->name('datakegiatan.update');
    Route::delete('/data-kegiatan/destroy/{id}', [DataKegiatanController::class, 'destroy'])->name('datakegiatan.destroy');

    Route::post('/update-realisasi', [UpdateRealisasiController::class, 'create'])->name('update-realisasi');
    Route::post('/approve-usulan', [UpdateRealisasiController::class, 'approve'])->name('approve-usulan');

    Route::get('logout', [SessionController::class, 'logout'])->name('logout');
});
