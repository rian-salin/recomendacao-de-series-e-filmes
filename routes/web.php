<?php

use App\Livewire\Posts\Mine;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware('auth')->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('minhas-publicacoes', Mine::class)->name('posts.mine');
});

require __DIR__.'/auth.php';
