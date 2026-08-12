<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function close(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function vote(User $user, Post $post): bool
    {
        return $user->id !== $post->user_id;
    }

    public function follow(User $user, Post $post): bool
    {
        return $user->id !== $post->user_id;
    }
}
