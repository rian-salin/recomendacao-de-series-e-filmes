<?php

namespace App\Services;

use App\Enums\PostStatus;
use App\Enums\VoteType;
use App\Exceptions\PostAlreadyClosedException;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class VoteService
{
    public function vote(Post $post, User $user, VoteType $type): void
    {
        DB::transaction(function () use ($post, $user, $type) {
            $lockedPost = $this->lockOpenPost($post);

            Vote::updateOrCreate(
                ['post_id' => $lockedPost->id, 'user_id' => $user->id],
                ['type' => $type],
            );

            Follow::firstOrCreate(['post_id' => $lockedPost->id, 'user_id' => $user->id]);
        });
    }

    public function follow(Post $post, User $user): void
    {
        DB::transaction(function () use ($post, $user) {
            $lockedPost = $this->lockOpenPost($post);

            Follow::firstOrCreate(['post_id' => $lockedPost->id, 'user_id' => $user->id]);
        });
    }

    /**
     * Recarrega a publicacao sob trava: o $post recebido carrega o estado de
     * quando a pagina foi renderizada, e a checagem precisa vir depois do lock
     * para enxergar um encerramento commitado por outra transacao.
     */
    private function lockOpenPost(Post $post): Post
    {
        $lockedPost = Post::whereKey($post->id)->lockForUpdate()->firstOrFail();

        if ($lockedPost->status === PostStatus::Closed) {
            throw new PostAlreadyClosedException;
        }

        return $lockedPost;
    }
}
