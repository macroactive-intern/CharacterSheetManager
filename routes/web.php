<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('characters.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('characters.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('characters', CharacterController::class);

    Route::post('/characters/{character}/stats', [StatController::class, 'store'])
        ->name('characters.stats.store');

    Route::put('/characters/{character}/stats/{stat}', [StatController::class, 'update'])
        ->name('characters.stats.update');

    Route::delete('/characters/{character}/stats/{stat}', [StatController::class, 'destroy'])
        ->name('characters.stats.destroy');

    Route::post('/characters/{character}/items', [ItemController::class, 'store'])
        ->name('characters.items.store');

    Route::put('/characters/{character}/items/{item}', [ItemController::class, 'update'])
        ->name('characters.items.update');

    Route::delete('/characters/{character}/items/{item}', [ItemController::class, 'destroy'])
        ->name('characters.items.destroy');

    Route::patch('/characters/{character}/items/{item}/toggle-equipped', [ItemController::class, 'toggleEquipped'])
        ->name('characters.items.toggle-equipped');
});

require __DIR__.'/auth.php';
