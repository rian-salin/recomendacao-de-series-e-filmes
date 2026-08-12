@use('App\Enums\PostStatus')

<div class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-xl font-semibold text-gray-800">{{ __('My publications') }}</h1>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @forelse ($posts as $post)
            <article class="mb-4 rounded-lg bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $post->title }}</h2>

                    <span @class([
                        'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium',
                        'bg-green-100 text-green-800' => $post->status === PostStatus::Open,
                        'bg-gray-200 text-gray-600' => $post->status === PostStatus::Closed,
                    ])>
                        {{ $post->status->label() }}
                    </span>
                </div>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $post->type->label() }} &middot; {{ $post->created_at->format('d/m/Y') }}
                </p>

                <p class="mt-3 text-sm text-gray-700">{{ Str::limit($post->description, 200) }}</p>
            </article>
        @empty
            <div class="rounded-lg bg-white p-10 text-center shadow-sm">
                <p class="text-sm text-gray-600">{{ __('You have not created any publications yet.') }}</p>
            </div>
        @endforelse

        @if ($posts->hasPages())
            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
