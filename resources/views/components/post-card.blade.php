@use('App\Enums\PostStatus')
@use('App\Enums\VoteType')

@props(['post'])

@php
    $currentVote = $post->voteFromCurrentUser?->type;
    $isFollowing = $post->followFromCurrentUser !== null;
    $isAuthor = $post->user_id === auth()->id();
    $isClosed = $post->status === PostStatus::Closed;
    $canInteract = auth()->user()->can('vote', $post) && ! $isClosed;
@endphp

<article class="mb-4 rounded-lg bg-white p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $post->title }}</h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $post->user->name }} &middot; {{ $post->type->label() }} &middot; {{ $post->created_at->format('d/m/Y') }}
            </p>
        </div>

        <span @class([
            'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium',
            'bg-green-100 text-green-800' => $post->status === PostStatus::Open,
            'bg-gray-200 text-gray-600' => $post->status === PostStatus::Closed,
        ])>
            {{ $post->status->label() }}
        </span>
    </div>

    <div class="mt-3" x-data="{ expanded: false }">
        <p class="text-sm text-gray-700" :class="expanded ? '' : 'line-clamp-3'">{{ $post->description }}</p>

        @if (Str::length($post->description) > 240)
            <button type="button" class="mt-1 text-sm font-medium text-indigo-600 hover:text-indigo-500" x-show="! expanded" @click="expanded = true">
                {{ __('Show more') }}
            </button>
        @endif
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600">
        <span>{{ __(':count recommend', ['count' => $post->recommendations_count]) }}</span>
        <span>{{ __(':count do not recommend', ['count' => $post->not_recommendations_count]) }}</span>
        <span>{{ __(':count following', ['count' => $post->followers_count]) }}</span>
    </div>

    @if ($canInteract)
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="button" wire:click="recommend({{ $post->id }})" @class([
                'rounded-md px-3 py-1.5 text-sm font-medium transition',
                'bg-green-600 text-white hover:bg-green-500' => $currentVote === VoteType::Recommend,
                'bg-gray-100 text-gray-700 hover:bg-gray-200' => $currentVote !== VoteType::Recommend,
            ])>
                {{ __('Recommend') }}
            </button>

            <button type="button" wire:click="notRecommend({{ $post->id }})" @class([
                'rounded-md px-3 py-1.5 text-sm font-medium transition',
                'bg-red-600 text-white hover:bg-red-500' => $currentVote === VoteType::NotRecommend,
                'bg-gray-100 text-gray-700 hover:bg-gray-200' => $currentVote !== VoteType::NotRecommend,
            ])>
                {{ __('Do not recommend') }}
            </button>

            @if ($isFollowing)
                <span class="rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700">
                    {{ __('Following') }}
                </span>
            @else
                <button type="button" wire:click="follow({{ $post->id }})" class="rounded-md bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200">
                    {{ __('Follow') }}
                </button>
            @endif
        </div>
    @elseif ($isAuthor)
        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-400">
            {{ __('Your publication') }}
        </p>
    @endif
</article>
