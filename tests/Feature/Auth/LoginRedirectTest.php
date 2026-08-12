<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

test('a successful login lands on the feed', function () {
    $user = User::factory()->create(['password' => 'correct-password']);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'correct-password')
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('posts.index', absolute: false));
});

test('the dashboard route no longer exists', function () {
    expect(Route::has('dashboard'))->toBeFalse();
});
