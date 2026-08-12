<?php

namespace App\Livewire\Follows;

use App\Enums\VoteType;
use App\Livewire\Concerns\InteractsWithPosts;
use Illuminate\Database\Eloquent\Builder;
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
                ->with(['user:id,name', 'voteFromCurrentUser', 'followFromCurrentUser'])
                ->withCount([
                    'votes as recommendations_count' => fn (Builder $query) => $query->where('type', VoteType::Recommend),
                    'votes as not_recommendations_count' => fn (Builder $query) => $query->where('type', VoteType::NotRecommend),
                    'follows as followers_count',
                ])
                ->latest('follows.created_at')
                ->paginate(10),
        ]);
    }
}
