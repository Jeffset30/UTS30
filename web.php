<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;


// TODO: daftarkan route anda di sini

// Halaman utama
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Halaman dashboard (login & verified)
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Group route yang butuh login
Route::middleware(['auth'])->group(function () {
    // Setting
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // ROUTE CRUD PRODUK
    Route::resource('produk', ProdukController::class);
});

require __DIR__.'/auth.php';