<?php

namespace App\Livewire\Posts;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Mine extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('livewire.posts.mine', [
            'posts' => Post::query()
                ->where('user_id', auth()->id())
                ->latest()
                ->paginate(10),
        ]);
    }
}
