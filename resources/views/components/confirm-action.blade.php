@props([
    'heading',
    'description',
    'action',
    'variant' => 'neutral',
])

<div x-data="{ open: false }" class="inline-block">
    <button type="button" @click="open = true" @class([
        'rounded-md px-3 py-1.5 text-sm font-medium transition',
        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $variant === 'neutral',
        'bg-red-50 text-red-700 hover:bg-red-100' => $variant === 'danger',
    ])>
        {{ $slot }}
    </button>

    <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-gray-900/50"></div>

        <div x-show="open" x-transition class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-gray-900">{{ $heading }}</h2>

            <p class="mt-2 text-sm text-gray-600">{{ $description }}</p>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="open = false" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">
                    {{ __('Cancel') }}
                </button>

                <button type="button" @click="open = false" {{ $attributes->class([
                    'rounded-md px-4 py-2 text-sm font-semibold text-white transition',
                    'bg-gray-800 hover:bg-gray-700' => $variant === 'neutral',
                    'bg-red-600 hover:bg-red-500' => $variant === 'danger',
                ]) }}>
                    {{ $action }}
                </button>
            </div>
        </div>
    </div>
</div>
