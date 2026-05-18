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

    Route::resource('characters.stats', StatController::class)->shallow()->only(['store', 'update', 'destroy']);
    Route::resource('characters.items', ItemController::class)->shallow()->only(['store', 'update', 'destroy']);

    Route::patch('/items/{item}/toggle-equipped', [ItemController::class, 'toggleEquipped'])
        ->name('items.toggle-equipped');
});

require __DIR__.'/auth.php';
