<div class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-xl font-semibold text-gray-800">{{ __('Publications') }}</h1>

            <a href="{{ route('posts.create') }}" class="inline-flex shrink-0 items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700" wire:navigate>
                {{ __('New publication') }}
            </a>
        </div>

        @if ($interactionError)
            <div class="mb-6 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $interactionError }}
            </div>
        @endif

        @forelse ($posts as $post)
            <div wire:key="post-{{ $post->id }}">
                <x-post-card :post="$post" />
            </div>
        @empty
            <div class="rounded-lg bg-white p-10 text-center shadow-sm">
                <p class="text-sm text-gray-600">{{ __('There are no open publications yet.') }}</p>

                <a href="{{ route('posts.create') }}" class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500" wire:navigate>
                    {{ __('New publication') }}
                </a>
            </div>
        @endforelse

        @if ($posts->hasPages())
            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
