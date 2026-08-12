<?php

use App\Enums\PostStatus;
use App\Enums\VoteType;
use App\Livewire\Follows\Index;
use App\Models\Post;
use App\Models\User;
use App\Services\VoteService;
use Livewire\Livewire;

test('the page lists publications reached by voting and by following only', function () {
    $user = User::factory()->create();

    $recommended = Post::factory()->create(['title' => 'Duna']);
    $notRecommended = Post::factory()->create(['title' => 'Matrix']);
    $onlyFollowed = Post::factory()->create(['title' => 'Arrival']);
    Post::factory()->create(['title' => 'Interstellar']);

    $votes = app(VoteService::class);
    $votes->vote($recommended, $user, VoteType::Recommend);
    $votes->vote($notRecommended, $user, VoteType::NotRecommend);
    $votes->follow($onlyFollowed, $user);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('Duna')
        ->assertSee('Matrix')
        ->assertSee('Arrival')
        ->assertDontSee('Interstellar');
});

test('a closed publication stays visible on the followed page', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['title' => 'Duna']);

    app(VoteService::class)->vote($post, $user, VoteType::Recommend);

    $post->forceFill(['status' => PostStatus::Closed, 'closed_at' => now()])->save();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('Duna')
        ->assertSee(__('Closed'));
});

test('the page does not list publications followed by someone else', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $post = Post::factory()->create(['title' => 'Duna']);

    app(VoteService::class)->follow($post, $stranger);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertDontSee('Duna');
});
