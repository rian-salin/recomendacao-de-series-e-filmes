<?php

namespace App\Livewire\Posts;

use App\Livewire\Concerns\InteractsWithPosts;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Feed extends Component
{
    use InteractsWithPosts, WithPagination;

    public function render(): View
    {
        return view('livewire.posts.feed', [
            'posts' => Post::query()
                ->open()
                ->withInteractionCounts()
                ->latest()
                ->paginate(10),
        ]);
    }
}
