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

    // Export (Pimpinan)
    Route::get('/export/nilai-pegawai',   [\App\Http\Controllers\ExportController::class, 'pegawai'])->middleware('role.context:Pimpinan')->name('export.pegawai');
    Route::get('/export/nilai-ketua-tim', [\App\Http\Controllers\ExportController::class, 'ketuaTim'])->middleware('role.context:Pimpinan')->name('export.ketuaTim');
    Route::get('/export/nilai-kabkot',    [\App\Http\Controllers\ExportController::class, 'kabkot'])->middleware('role.context:Pimpinan')->name('export.kabkot');

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
