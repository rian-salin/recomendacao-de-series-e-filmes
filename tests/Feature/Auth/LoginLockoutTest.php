<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

test('a locked account cannot log in even with the correct password', function () {
    $user = User::factory()->create([
        'password' => 'correct-password',
        'login_attempts' => 3,
        'locked_until' => Carbon::now()->addHour(),
        'login_locked_permanently' => false,
    ]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'correct-password')
        ->call('authenticate')
        ->assertHasErrors('email');

    $this->assertGuest();
});
