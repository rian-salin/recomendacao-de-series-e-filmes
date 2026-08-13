<?php

use App\Enums\PostStatus;
use App\Exceptions\PostAlreadyClosedException;
use App\Livewire\Posts\Feed;
use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Livewire\Livewire;

test('only the author can close and delete their publication', function () {
    $author = User::factory()->create();
    $stranger = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    expect($author->can('close', $post))->toBeTrue()
        ->and($author->can('delete', $post))->toBeTrue()
        ->and($stranger->can('close', $post))->toBeFalse()
        ->and($stranger->can('delete', $post))->toBeFalse();
});

test('closing a publication marks it as closed and records when', function () {
    $post = Post::factory()->create();

    app(PostService::class)->close($post);

    $post->refresh();

    expect($post->status)->toBe(PostStatus::Closed)
        ->and($post->closed_at)->not->toBeNull();
});

test('closing an already closed publication is rejected', function () {
    $post = Post::factory()->closed()->create();

    expect(fn () => app(PostService::class)->close($post))
        ->toThrow(PostAlreadyClosedException::class);
});

test('a closed publication leaves the feed', function () {
    $post = Post::factory()->create(['title' => 'Duna']);

    app(PostService::class)->close($post);

    Livewire::actingAs(User::factory()->create())
        ->test(Feed::class)
        ->assertDontSee('Duna');
});
