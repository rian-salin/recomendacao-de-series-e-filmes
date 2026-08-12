<?php

use App\Livewire\Posts\Create;
use App\Livewire\Posts\Feed;
use App\Livewire\Posts\Mine;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware('auth')->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('publicacoes', Feed::class)->name('posts.index');
    Route::get('minhas-publicacoes', Mine::class)->name('posts.mine');
    Route::get('publicacoes/nova', Create::class)->name('posts.create');
});

require __DIR__.'/auth.php';
