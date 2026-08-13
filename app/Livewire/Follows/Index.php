<?php

namespace App\Livewire\Follows;

use App\Livewire\Concerns\InteractsWithPosts;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use InteractsWithPosts, WithPagination;

    public function render(): View
    {
        return view('livewire.follows.index', [
            'posts' => auth()->user()
                ->followedPosts()
                ->withInteractionCounts()
                ->withCardRelations()
                ->latest('follows.created_at')
                ->paginate(10),
        ]);
    }
}
