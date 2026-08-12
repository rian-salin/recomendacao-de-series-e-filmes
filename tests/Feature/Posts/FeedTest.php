<?php

use App\Enums\VoteType;
use App\Livewire\Posts\Feed;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Livewire\Livewire;

test('the feed lists only open publications', function () {
    Post::factory()->create(['title' => 'Duna']);
    Post::factory()->closed()->create(['title' => 'Matrix']);

    Livewire::actingAs(User::factory()->create())
        ->test(Feed::class)
        ->assertSee('Duna')
        ->assertDontSee('Matrix');
});

test('the feed counts recommendations and follows per publication', function () {
    $post = Post::factory()->create();

    Vote::factory()->for($post)->recommend()->count(2)->create();
    Vote::factory()->for($post)->notRecommend()->create();
    Follow::factory()->for($post)->count(4)->create();

    Livewire::actingAs(User::factory()->create())
        ->test(Feed::class)
        ->assertViewHas('posts', function ($posts) {
            $listed = $posts->first();

            return (int) $listed->recommendations_count === 2
                && (int) $listed->not_recommendations_count === 1
                && (int) $listed->followers_count === 4;
        });
});

test('recommending through the component records the vote', function () {
    $post = Post::factory()->create();
    $voter = User::factory()->create();

    Livewire::actingAs($voter)
        ->test(Feed::class)
        ->call('recommend', $post->id)
        ->assertHasNoErrors();

    expect(Vote::sole()->type)->toBe(VoteType::Recommend)
        ->and(Vote::sole()->user_id)->toBe($voter->id)
        ->and(Follow::count())->toBe(1);
});

test('the author gets a 403 when voting on their own publication', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    Livewire::actingAs($author)
        ->test(Feed::class)
        ->call('recommend', $post->id)
        ->assertForbidden();

    expect(Vote::count())->toBe(0);
});

test('the author gets a 403 when following their own publication', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    Livewire::actingAs($author)
        ->test(Feed::class)
        ->call('follow', $post->id)
        ->assertForbidden();

    expect(Follow::count())->toBe(0);
});

test('interacting with a closed publication surfaces the error message', function () {
    $post = Post::factory()->closed()->create();

    Livewire::actingAs(User::factory()->create())
        ->test(Feed::class)
        ->call('recommend', $post->id)
        ->assertSet('interactionError', __('This publication has already been closed.'));

    expect(Vote::count())->toBe(0);
});
