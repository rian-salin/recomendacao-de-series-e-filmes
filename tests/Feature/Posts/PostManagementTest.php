<?php

use App\Models\Post;
use App\Models\User;

test('only the author can close and delete their publication', function () {
    $author = User::factory()->create();
    $stranger = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    expect($author->can('close', $post))->toBeTrue()
        ->and($author->can('delete', $post))->toBeTrue()
        ->and($stranger->can('close', $post))->toBeFalse()
        ->and($stranger->can('delete', $post))->toBeFalse();
});
