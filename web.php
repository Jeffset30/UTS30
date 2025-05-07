<?php

use App\Http\Controllers\ProdukController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

// Route Publik
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Route Dashboard tanpa auth
Route::view('dashboard', 'dashboard')->name('dashboard');

// Route Produk tanpa auth
Route::prefix('produk')->group(function () {
    Route::get('/', [ProdukController::class, 'index'])->name('produk.index');
    Route::get('/tambah', [ProdukController::class, 'create'])->name('produk.create');
    Route::post('/', [ProdukController::class, 'store'])->name('produk.store');
    Route::get('/{produk}', [ProdukController::class, 'show'])->name('produk.show');
    Route::get('/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
    Route::put('/{produk}', [ProdukController::class, 'update'])->name('produk.update');
    Route::delete('/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');
    
    // API Endpoint
    Route::get('/api/list', [ProdukController::class, 'apiIndex'])->name('produk.api');
});

// Route Pengaturan Pengguna tanpa auth
Route::redirect('settings', 'settings/profile');

Route::get('settings/profile', Profile::class)->name('settings.profile');
Route::get('settings/password', Password::class)->name('settings.password');
Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

// Route Autentikasi Breeze (opsional: boleh dihapus jika login tidak dipakai)
require __DIR__.'/auth.php';
