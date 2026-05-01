<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/offline', fn() => view('offline'))->name('offline');

Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/login');
});

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::middleware(['auth', 'role.context'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/switcher', \App\Livewire\Auth\RoleSwitcher::class)->name('role.switcher');
    
    // Assessment (Ketua Tim)
    Route::get('/penilaian', \App\Livewire\Penilaian\Index::class)->middleware('role.context:Ketua Tim')->name('penilaian');
    
    // Management (Admin)
    Route::get('/teams', \App\Livewire\Teams\Index::class)->middleware('role.context:Admin')->name('teams');
    Route::get('/users', \App\Livewire\Users\Index::class)->middleware('role.context:Admin')->name('users');
    Route::get('/users/template-excel', [\App\Http\Controllers\ExcelTemplateController::class, 'download'])->middleware('role.context:Admin')->name('users.template');
    Route::get('/konfigurasi', \App\Livewire\Konfigurasi\Index::class)->middleware('role.context:Admin')->name('konfigurasi');

    // Super Admin
    Route::get('/super-admin', \App\Livewire\SuperAdmin\Dashboard::class)->middleware('role.context:Super Admin')->name('super-admin.dashboard');
    Route::get('/super-admin/export/nilai-pegawai',   [\App\Http\Controllers\ExportController::class, 'superPegawai'])->middleware('role.context:Super Admin')->name('export.super.pegawai');
    Route::get('/super-admin/export/nilai-ketua-tim', [\App\Http\Controllers\ExportController::class, 'superKetuaTim'])->middleware('role.context:Super Admin')->name('export.super.ketuaTim');
    Route::get('/super-admin/export/nilai-kabkot',    [\App\Http\Controllers\ExportController::class, 'superKabkot'])->middleware('role.context:Super Admin')->name('export.super.kabkot');
    Route::get('/super-admin/export/laporan',         [\App\Http\Controllers\ExportController::class, 'superLaporan'])->middleware('role.context:Super Admin')->name('export.super.laporan');

    // Export (Pimpinan)
    Route::get('/export/nilai-pegawai',   [\App\Http\Controllers\ExportController::class, 'pegawai'])->middleware('role.context:Pimpinan')->name('export.pegawai');
    Route::get('/export/nilai-ketua-tim', [\App\Http\Controllers\ExportController::class, 'ketuaTim'])->middleware('role.context:Pimpinan')->name('export.ketuaTim');
    Route::get('/export/nilai-kabkot',    [\App\Http\Controllers\ExportController::class, 'kabkot'])->middleware('role.context:Pimpinan')->name('export.kabkot');

    // Export (Kepala Kabkot)
    Route::get('/kepala/export/nilai-pegawai',   [\App\Http\Controllers\ExportController::class, 'kepalaPegawai'])->middleware('role.context:Kepala Kabkot')->name('export.kepala.pegawai');
    Route::get('/kepala/export/nilai-ketua-tim', [\App\Http\Controllers\ExportController::class, 'kepalaKetuaTim'])->middleware('role.context:Kepala Kabkot')->name('export.kepala.ketuaTim');

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
