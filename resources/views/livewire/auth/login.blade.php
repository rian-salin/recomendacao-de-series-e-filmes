<div>
    <h1 class="mb-6 text-xl font-semibold text-gray-800">{{ __('Log in') }}</h1>

    <form wire:submit="authenticate" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button>
            {{ __('Log in') }}
        </x-primary-button>

        <p class="text-center text-sm text-gray-600">
            {{ __('Not registered?') }}
            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500" wire:navigate>{{ __('Create an account') }}</a>
        </p>
    </form>
</div>
