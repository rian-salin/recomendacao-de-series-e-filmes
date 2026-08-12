<?php

use App\Enums\VoteType;
use App\Exceptions\PostAlreadyClosedException;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use App\Services\VoteService;
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

test('a closed publication does not accept votes', function () {
    $post = Post::factory()->closed()->create();
    $user = User::factory()->create();

    expect(fn () => app(VoteService::class)->vote($post, $user, VoteType::Recommend))
        ->toThrow(PostAlreadyClosedException::class);

    expect(Vote::count())->toBe(0)
        ->and(Follow::count())->toBe(0);
});

test('a closed publication does not accept follows', function () {
    $post = Post::factory()->closed()->create();
    $user = User::factory()->create();

    expect(fn () => app(VoteService::class)->follow($post, $user))
        ->toThrow(PostAlreadyClosedException::class);

    expect(Follow::count())->toBe(0);
});

test('voting also registers the follow in the same operation', function () {
    $post = Post::factory()->create();
    $user = User::factory()->create();

    app(VoteService::class)->vote($post, $user, VoteType::Recommend);

    expect(Vote::count())->toBe(1)
        ->and(Follow::count())->toBe(1)
        ->and(Follow::sole()->user_id)->toBe($user->id)
        ->and(Follow::sole()->post_id)->toBe($post->id);
});

test('changing the vote updates the existing row and preserves created_at', function () {
    $post = Post::factory()->create();
    $user = User::factory()->create();

    $original = Vote::factory()->for($post)->for($user)->recommend()->create([
        'created_at' => now()->subDay(),
    ]);

    app(VoteService::class)->vote($post, $user, VoteType::NotRecommend);

    $vote = Vote::sole();

    expect(Vote::count())->toBe(1)
        ->and($vote->id)->toBe($original->id)
        ->and($vote->type)->toBe(VoteType::NotRecommend)
        ->and($vote->created_at->toDateTimeString())->toBe($original->created_at->toDateTimeString());
});

test('the author cannot vote on or follow their own publication', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    expect($author->can('vote', $post))->toBeFalse()
        ->and($author->can('follow', $post))->toBeFalse();
});

test('another user can vote on and follow the publication', function () {
    $post = Post::factory()->create();
    $stranger = User::factory()->create();

    expect($stranger->can('vote', $post))->toBeTrue()
        ->and($stranger->can('follow', $post))->toBeTrue();
});
