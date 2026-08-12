<?php

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Livewire\Posts\Create;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

test('title, type and description are required', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Create::class)
        ->set('title', '')
        ->set('type', '')
        ->set('description', '')
        ->call('save')
        ->assertHasErrors([
            'title' => 'required',
            'type' => 'required',
            'description' => 'required',
        ]);

    expect(Post::count())->toBe(0);
});

test('type must be one of the enum values', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Create::class)
        ->set('title', 'Duna')
        ->set('type', 'documentario')
        ->set('description', 'Vale a pena?')
        ->call('save')
        ->assertHasErrors(['type']);

    expect(Post::count())->toBe(0);
});

test('description is capped at 2000 characters', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Create::class)
        ->set('title', 'Duna')
        ->set('type', PostType::Movie->value)
        ->set('description', str_repeat('a', 2001))
        ->call('save')
        ->assertHasErrors(['description' => 'max']);

    expect(Post::count())->toBe(0);
});

test('a valid publication is stored as open and owned by the authenticated user', function () {
    $author = User::factory()->create();
    User::factory()->create();

    Livewire::actingAs($author)
        ->test(Create::class)
        ->set('title', 'Duna')
        ->set('type', PostType::Movie->value)
        ->set('description', 'Vale a pena assistir antes da parte 2?')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('posts.mine'));

    $post = Post::sole();

    expect($post->title)->toBe('Duna')
        ->and($post->type)->toBe(PostType::Movie)
        ->and($post->status)->toBe(PostStatus::Open)
        ->and($post->closed_at)->toBeNull()
        ->and($post->user_id)->toBe($author->id);
});
