@use('App\Enums\PostStatus')

<div class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-xl font-semibold text-gray-800">{{ __('My publications') }}</h1>

            <a href="{{ route('posts.create') }}" class="inline-flex shrink-0 items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700" wire:navigate>
                {{ __('New publication') }}
            </a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($actionMessage)
            <div class="mb-6 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ $actionMessage }}
            </div>
        @endif

        @if ($actionError)
            <div class="mb-6 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $actionError }}
            </div>
        @endif

        @forelse ($posts as $post)
            @php
                $isClosed = $post->status === PostStatus::Closed;
                $hasThirdPartyInteraction = $post->has_third_party_votes || $post->has_third_party_follows;
            @endphp

            <article wire:key="post-{{ $post->id }}" @class([
                'mb-4 rounded-lg p-6 shadow-sm',
                'bg-white' => ! $isClosed,
                'bg-gray-50' => $isClosed,
            ])>
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $post->title }}</h2>

                    <span @class([
                        'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium',
                        'bg-green-100 text-green-800' => ! $isClosed,
                        'bg-gray-200 text-gray-600' => $isClosed,
                    ])>
                        {{ $post->status->label() }}
                    </span>
                </div>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $post->type->label() }} &middot; {{ $post->created_at->format('d/m/Y') }}
                    @if ($isClosed)
                        &middot; {{ __('Closed on :date', ['date' => $post->closed_at->format('d/m/Y')]) }}
                    @endif
                </p>

                <p class="mt-3 text-sm text-gray-700">{{ Str::limit($post->description, 200) }}</p>

                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600">
                    <span>{{ __(':count recommend', ['count' => $post->recommendations_count]) }}</span>
                    <span>{{ __(':count do not recommend', ['count' => $post->not_recommendations_count]) }}</span>
                    <span>{{ __(':count following', ['count' => $post->followers_count]) }}</span>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @unless ($isClosed)
                        <x-confirm-action
                            :heading="__('Close publication?')"
                            :description="__('Once closed, the publication stops receiving votes and follows.')"
                            :action="__('Close')"
                            wire:click="close({{ $post->id }})"
                        >{{ __('Close') }}</x-confirm-action>
                    @endunless

                    @if ($hasThirdPartyInteraction)
                        <button type="button" disabled class="cursor-not-allowed rounded-md bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-400">
                            {{ __('Delete') }}
                        </button>
                    @else
                        <x-confirm-action
                            :heading="__('Delete publication?')"
                            :description="__('This action cannot be undone.')"
                            :action="__('Delete')"
                            variant="danger"
                            wire:click="delete({{ $post->id }})"
                        >{{ __('Delete') }}</x-confirm-action>
                    @endif
                </div>

                @if ($hasThirdPartyInteraction)
                    <p class="mt-2 text-xs text-gray-500">
                        {{ __('It is not possible to delete: the publication has already received interaction from other users.') }}
                    </p>
                @endif
            </article>
        @empty
            <div class="rounded-lg bg-white p-10 text-center shadow-sm">
                <p class="text-sm text-gray-600">{{ __('You have not created any publications yet.') }}</p>

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
