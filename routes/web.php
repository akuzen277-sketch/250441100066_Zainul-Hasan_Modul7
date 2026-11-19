<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Models\Buku;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Akses rute khusus role
Route::middleware(['auth', 'permission:akses dashboard admin'])->group(function () {
    Route::get('/admin', function () {
        return redirect()->route('dashboard');
    });
});

Route::middleware(['auth', 'permission:akses dashboard petugas'])->group(function () {
    Route::get('/petugas', function () {
        return redirect()->route('dashboard');
    });
});

Route::middleware(['auth', 'role:siswa|anggota'])->group(function () {
    Route::get('/siswa', function () {
        return redirect()->route('dashboard');
    });
});

Route::middleware(['auth'])->group(function () {

    // Melihat buku (Admin, Petugas, Siswa)
    Route::get('/buku', [BukuController::class, 'index'])
        ->middleware('permission:view buku')
        ->name('buku.index');

    Route::get('/buku/create', [BukuController::class, 'create'])
        ->middleware('can:kelola-buku')
        ->name('buku.create');

    Route::post('/buku', [BukuController::class, 'store'])
        ->middleware('can:kelola-buku')
        ->name('buku.store');

    Route::put('/buku/{buku}', [BukuController::class, 'update'])
        ->middleware('can:kelola-buku')
        ->name('buku.update');

    Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])
        ->middleware('can:kelola-buku')
        ->name('buku.destroy');

    Route::get('/buku-cari', [BukuController::class, 'search'])
        ->name('buku.search');

    Route::get('/buku/{buku}', [BukuController::class, 'show'])
        ->middleware('permission:view buku')
        ->name('buku.show');
});

Route::middleware(['auth'])->group(function () {

    // Melihat daftar peminjaman (Siswa riwayat sendiri, Admin/Petugas kelola semua)
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])
        ->middleware('permission:melihat riwayat peminjaman sendiri|kelola peminjaman')
        ->name('peminjaman.index');

    // Siswa mengajukan peminjaman (Hanya role dengan 'mengajukan peminjaman buku')
    Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])
        ->middleware('permission:mengajukan peminjaman buku')
        ->name('peminjaman.create');

    Route::get('/peminjaman/{id}', [PeminjamanController::class, 'show'])
        ->whereNumber('id')
        ->name('peminjaman.show');

    Route::post('/peminjaman', [PeminjamanController::class, 'store'])
        ->middleware('permission:mengajukan peminjaman buku')
        ->name('peminjaman.store');

    // Admin dan petugas memproses persetujuan (Hanya role dengan 'memproses persetujuan peminjaman')
    Route::patch('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update'])
        ->middleware('permission:memproses persetujuan peminjaman')
        ->name('peminjaman.update');

    // Admin dan petugas mencatat pengembalian (Hanya role dengan 'mencatat pengembalian buku')
    Route::patch('/peminjaman/{peminjaman}/kembali', [PeminjamanController::class, 'returnBook'])
        ->middleware('permission:mencatat pengembalian buku')
        ->name('peminjaman.return');

    // Kelola Data Pengguna & Role Pengguna (Hanya Admin / permission kelola data/role pengguna)
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:kelola data pengguna|kelola role pengguna')
        ->name('users.index');

    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])
        ->middleware('permission:kelola role pengguna')
        ->name('users.updateRole');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:kelola data pengguna')
        ->name('users.destroy');

    // Laporan Perpustakaan (Hanya Admin & Petugas / permission 'lihat laporan perpustakaan')
    Route::get('/laporan', [LaporanController::class, 'index'])
        ->middleware('permission:lihat laporan perpustakaan')
        ->name('laporan.index');

});
require __DIR__.'/auth.php';
