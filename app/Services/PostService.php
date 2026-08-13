<?php

namespace App\Services;

use App\Enums\PostStatus;
use App\Exceptions\PostAlreadyClosedException;
use App\Models\Post;

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
}
