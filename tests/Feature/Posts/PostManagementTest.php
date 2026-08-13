<?php

use App\Enums\PostStatus;
use App\Exceptions\PostAlreadyClosedException;
use App\Exceptions\PostHasInteractionsException;
use App\Livewire\Posts\Feed;
use App\Livewire\Posts\Mine;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use App\Services\PostService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
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

test('the author cannot delete a publication with a vote from another user', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    Vote::factory()->for($post)->recommend()->create();

    expect(fn () => app(PostService::class)->delete($post))
        ->toThrow(PostHasInteractionsException::class);

    expect(Post::whereKey($post->id)->exists())->toBeTrue()
        ->and(Vote::count())->toBe(1);
});

test('the author cannot delete a publication that another user follows', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    Follow::factory()->for($post)->create();

    expect(fn () => app(PostService::class)->delete($post))
        ->toThrow(PostHasInteractionsException::class);

    expect(Post::whereKey($post->id)->exists())->toBeTrue()
        ->and(Follow::count())->toBe(1);
});

test('the author deletes a publication with no interaction at all', function () {
    $post = Post::factory()->create();

    app(PostService::class)->delete($post);

    expect(Post::count())->toBe(0);
});

test('a closed publication with no interaction can still be deleted', function () {
    $post = Post::factory()->closed()->create();

    app(PostService::class)->delete($post);

    expect(Post::count())->toBe(0);
});

test('the third party interaction flags ignore the author and catch other users', function () {
    $author = User::factory()->create();
    $untouched = Post::factory()->for($author)->create();
    $votedByAuthor = Post::factory()->for($author)->create();
    $votedByStranger = Post::factory()->for($author)->create();
    $followedByStranger = Post::factory()->for($author)->create();

    Vote::factory()->for($votedByAuthor)->for($author)->recommend()->create();
    Vote::factory()->for($votedByStranger)->recommend()->create();
    Follow::factory()->for($followedByStranger)->create();

    $posts = Post::query()->withThirdPartyInteraction()->get()->keyBy('id');

    expect($posts[$untouched->id]->has_third_party_votes)->toBeFalse()
        ->and($posts[$untouched->id]->has_third_party_follows)->toBeFalse()
        ->and($posts[$votedByAuthor->id]->has_third_party_votes)->toBeFalse()
        ->and($posts[$votedByStranger->id]->has_third_party_votes)->toBeTrue()
        ->and($posts[$followedByStranger->id]->has_third_party_follows)->toBeTrue();
});

test('the author closes their publication through the component', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    Livewire::actingAs($author)
        ->test(Mine::class)
        ->call('close', $post->id)
        ->assertSet('actionMessage', __('Publication closed.'))
        ->assertSet('actionError', null);

    expect($post->refresh()->status)->toBe(PostStatus::Closed);
});

test('the author deletes their publication through the component', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    Livewire::actingAs($author)
        ->test(Mine::class)
        ->call('delete', $post->id)
        ->assertSet('actionMessage', __('Publication deleted.'));

    expect(Post::count())->toBe(0);
});

test('a forged delete on a publication with interaction is refused by the backend', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    Vote::factory()->for($post)->recommend()->create();

    Livewire::actingAs($author)
        ->test(Mine::class)
        ->call('delete', $post->id)
        ->assertSet('actionError', __('It is not possible to delete: the publication has already received interaction from other users.'));

    expect(Post::count())->toBe(1);
});

test('another user gets a 403 when closing or deleting a publication they do not own', function () {
    $post = Post::factory()->create();
    $stranger = User::factory()->create();

    Livewire::actingAs($stranger)
        ->test(Mine::class)
        ->call('close', $post->id)
        ->assertForbidden();

    Livewire::actingAs($stranger)
        ->test(Mine::class)
        ->call('delete', $post->id)
        ->assertForbidden();

    expect(Post::count())->toBe(1)
        ->and(Post::sole()->status)->toBe(PostStatus::Open);
});

test('closing or deleting a publication that no longer exists sets a graceful error instead of a 404', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();
    $postId = $post->id;

    $post->delete();

    Livewire::actingAs($author)
        ->test(Mine::class)
        ->call('close', $postId)
        ->assertOk()
        ->assertSet('actionError', __('This publication no longer exists.'));

    Livewire::actingAs($author)
        ->test(Mine::class)
        ->call('delete', $postId)
        ->assertOk()
        ->assertSet('actionError', __('This publication no longer exists.'));
});

test('the confirmation component forwards the action to the confirm button, not to the trigger', function () {
    $rendered = Blade::render(
        '<x-confirm-action heading="Excluir publicação?" description="Sem volta." action="Excluir" variant="danger" wire:click="delete(7)">Excluir</x-confirm-action>'
    );

    expect($rendered)->toContain('x-data="{ open: false }"')
        ->and($rendered)->toContain('wire:click="delete(7)"')
        ->and(Str::before($rendered, 'Excluir publicação?'))->not->toContain('wire:click');
});
