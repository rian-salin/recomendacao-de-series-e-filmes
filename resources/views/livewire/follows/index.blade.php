<div class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h1 class="mb-6 text-xl font-semibold text-gray-800">{{ __('Followed publications') }}</h1>

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
                <p class="text-sm text-gray-600">{{ __('You are not following any publications yet.') }}</p>

                <a href="{{ route('posts.index') }}" class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500" wire:navigate>
                    {{ __('Publications') }}
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
