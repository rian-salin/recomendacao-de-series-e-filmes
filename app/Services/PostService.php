<?php

namespace App\Services;

use App\Enums\PostStatus;
use App\Exceptions\PostAlreadyClosedException;
use App\Exceptions\PostHasInteractionsException;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostService
{
    /**
     * A condicao vive no proprio WHERE: um UPDATE unico ja e atomico, e o
     * $affected e o que distingue a primeira de duas tentativas simultaneas.
     */
    public function close(Post $post): void
    {
        $affected = Post::whereKey($post->id)
            ->where('status', PostStatus::Open)
            ->update(['status' => PostStatus::Closed, 'closed_at' => now()]);

        if ($affected === 0) {
            throw new PostAlreadyClosedException;
        }
    }

    /**
     * A transacao existe so como veiculo da trava: o delete com cascade ja e um
     * statement unico. Sem ela, um voto commitado entre a verificacao e o delete
     * seria apagado pelo cascade sem deixar rastro.
     */
    public function delete(Post $post): void
    {
        DB::transaction(function () use ($post) {
            $lockedPost = Post::whereKey($post->id)->lockForUpdate()->firstOrFail();

            $hasOthersInteraction = $lockedPost->votes()->where('user_id', '!=', $lockedPost->user_id)->exists()
                || $lockedPost->follows()->where('user_id', '!=', $lockedPost->user_id)->exists();

            if ($hasOthersInteraction) {
                throw new PostHasInteractionsException;
            }

            $lockedPost->delete();
        });
    }
}
