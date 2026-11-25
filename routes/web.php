<?php

use App\Http\Controllers\AlternativeController;
use App\Http\Controllers\AhpController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CriterionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HasilController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ProsesController;
use App\Http\Controllers\RiwayatController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

// ---- Autentikasi ----
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---- Aplikasi (wajib login) ----
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('periode', PeriodController::class)
        ->parameters(['periode' => 'period'])
        ->except(['show', 'create']);
    Route::resource('kriteria', CriterionController::class)->except(['show', 'create']);
    Route::resource('kandidat', AlternativeController::class)
        ->parameters(['kandidat' => 'alternative'])
        ->except(['show']);

    Route::get('kandidat/{alternative}/nilai', [AlternativeController::class, 'nilai'])->name('kandidat.nilai');
    Route::put('kandidat/{alternative}/nilai', [AlternativeController::class, 'simpanNilai'])->name('kandidat.nilai.simpan');

    Route::get('/ahp', [AhpController::class, 'edit'])->name('ahp.edit');
    Route::post('/ahp', [AhpController::class, 'store'])->name('ahp.store');

    Route::post('/proses', [ProsesController::class, 'store'])->name('proses.store');
    Route::get('/hasil', [HasilController::class, 'index'])->name('hasil.index');
    Route::get('/hasil/{batch}', [HasilController::class, 'show'])->name('hasil.show');

    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::delete('/riwayat/{batch}', [RiwayatController::class, 'destroy'])->name('riwayat.destroy');

    Route::get('/export/{batch}/pdf', [ExportController::class, 'pdf'])->name('export.pdf');
    Route::get('/export/{batch}/xlsx', [ExportController::class, 'xlsx'])->name('export.xlsx');
});
