<?php

namespace App\Services;

use App\Exceptions\AccountLockedException;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * @var array<int, int> Número da tentativa => duração do bloqueio em horas.
     */
    private const LOCKOUT_HOURS_BY_ATTEMPT = [
        3 => 1,
        4 => 3,
        5 => 8,
    ];

    private const PERMANENT_LOCK_AT_ATTEMPT = 6;

    public function attemptLogin(string $email, string $password, bool $remember): void
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->ensureIsNotLocked($user);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            if ($user) {
                $this->registerFailedAttempt($user);
            }

            throw new InvalidCredentialsException;
        }

        if ($user) {
            $this->clearLockout($user);
        }
    }

    private function ensureIsNotLocked(User $user): void
    {
        if ($user->login_locked_permanently) {
            throw new AccountLockedException;
        }

        if ($user->locked_until !== null && $user->locked_until->isFuture()) {
            throw new AccountLockedException($user->locked_until);
        }
    }

    private function registerFailedAttempt(User $user): void
    {
        $attempts = $user->login_attempts + 1;

        $state = ['login_attempts' => $attempts];

        if ($attempts >= self::PERMANENT_LOCK_AT_ATTEMPT) {
            $state['login_locked_permanently'] = true;
        } elseif (isset(self::LOCKOUT_HOURS_BY_ATTEMPT[$attempts])) {
            $state['locked_until'] = Carbon::now()->addHours(self::LOCKOUT_HOURS_BY_ATTEMPT[$attempts]);
        }

        $user->forceFill($state)->save();
    }

    private function clearLockout(User $user): void
    {
        $user->forceFill([
            'login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }
}
