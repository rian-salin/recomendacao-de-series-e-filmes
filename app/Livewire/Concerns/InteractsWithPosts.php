<?php

namespace App\Livewire\Concerns;

use App\Enums\VoteType;
use App\Exceptions\PostAlreadyClosedException;
use App\Models\Post;
use App\Services\VoteService;
use Closure;

trait InteractsWithPosts
{
    public ?string $interactionError = null;

    public function recommend(VoteService $votes, int $postId): void
    {
        $this->castVote($votes, $postId, VoteType::Recommend);
    }

    public function notRecommend(VoteService $votes, int $postId): void
    {
        $this->castVote($votes, $postId, VoteType::NotRecommend);
    }

    public function follow(VoteService $votes, int $postId): void
    {
        $post = Post::findOrFail($postId);

        $this->authorize('follow', $post);

        $this->runInteraction(fn () => $votes->follow($post, auth()->user()));
    }

    /**
     * Protegido de proposito: toda action publica de um componente Livewire e
     * chamavel pelo console do navegador, e uma versao publica aceitaria um
     * VoteType arbitrario vindo do cliente.
     */
    protected function castVote(VoteService $votes, int $postId, VoteType $type): void
    {
        $post = Post::findOrFail($postId);

        $this->authorize('vote', $post);

        $this->runInteraction(fn () => $votes->vote($post, auth()->user(), $type));
    }

    protected function runInteraction(Closure $interaction): void
    {
        $this->interactionError = null;

        try {
            $interaction();
        } catch (PostAlreadyClosedException) {
            $this->interactionError = __('This publication has already been closed.');
        }
    }
}
