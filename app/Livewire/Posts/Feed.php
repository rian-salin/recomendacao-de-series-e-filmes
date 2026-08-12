<?php

namespace App\Livewire\Posts;

use App\Enums\VoteType;
use App\Livewire\Concerns\InteractsWithPosts;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
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
                ->with(['user:id,name', 'voteFromCurrentUser', 'followFromCurrentUser'])
                ->withCount([
                    'votes as recommendations_count' => fn (Builder $query) => $query->where('type', VoteType::Recommend),
                    'votes as not_recommendations_count' => fn (Builder $query) => $query->where('type', VoteType::NotRecommend),
                    'follows as followers_count',
                ])
                ->latest()
                ->paginate(10),
        ]);
    }
}
