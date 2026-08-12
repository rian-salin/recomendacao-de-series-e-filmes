<?php

use App\Enums\VoteType;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\QueryException;

test('the database rejects a second vote from the same user on the same publication', function () {
    $post = Post::factory()->create();
    $user = User::factory()->create();

    Vote::factory()->for($post)->for($user)->recommend()->create();

    expect(fn () => Vote::factory()->for($post)->for($user)->notRecommend()->create())
        ->toThrow(QueryException::class);

    expect(Vote::count())->toBe(1);
});

test('the database rejects a second follow from the same user on the same publication', function () {
    $post = Post::factory()->create();
    $user = User::factory()->create();

    Follow::factory()->for($post)->for($user)->create();

    expect(fn () => Follow::factory()->for($post)->for($user)->create())
        ->toThrow(QueryException::class);

    expect(Follow::count())->toBe(1);
});

test('a publication exposes the vote and the follow of the authenticated user', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $post = Post::factory()->create();

    Vote::factory()->for($post)->for($user)->recommend()->create();
    Vote::factory()->for($post)->for($stranger)->notRecommend()->create();
    Follow::factory()->for($post)->for($user)->create();

    $this->actingAs($user);

    $loaded = Post::with(['voteFromCurrentUser', 'followFromCurrentUser'])->findOrFail($post->id);

    expect($loaded->voteFromCurrentUser->type)->toBe(VoteType::Recommend)
        ->and($loaded->followFromCurrentUser)->not->toBeNull();
});

test('a user reaches the publications they follow', function () {
    $user = User::factory()->create();
    $followed = Post::factory()->create();
    Post::factory()->create();

    Follow::factory()->for($followed)->for($user)->create();

    expect($user->followedPosts()->pluck('posts.id')->all())->toEqual([$followed->id]);
});
