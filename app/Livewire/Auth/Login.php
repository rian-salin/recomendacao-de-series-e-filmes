<?php

namespace App\Livewire\Auth;

use App\Exceptions\AccountLockedException;
use App\Exceptions\InvalidCredentialsException;
use App\Services\AuthService;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function authenticate(AuthService $authService): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $authService->attemptLogin($this->email, $this->password);
        } catch (AccountLockedException $e) {
            throw ValidationException::withMessages([
                'email' => $this->lockoutMessage($e),
            ]);
        } catch (InvalidCredentialsException) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        Session::regenerate();

        $this->redirectIntended(default: route('posts.index', absolute: false), navigate: true);
    }

    private function lockoutMessage(AccountLockedException $exception): string
    {
        if ($exception->retryAt === null) {
            return __('This account has been locked due to too many failed login attempts. Please contact support.');
        }

        $minutes = max(1, (int) ceil(now()->diffInMinutes($exception->retryAt)));

        return __('Too many login attempts. Please try again in :minutes minutes.', ['minutes' => $minutes]);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
