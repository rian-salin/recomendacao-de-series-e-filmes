<?php

namespace App\Livewire\Posts;

use App\Exceptions\PostAlreadyClosedException;
use App\Exceptions\PostHasInteractionsException;
use App\Models\Post;
use App\Services\PostService;
use Closure;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Mine extends Component
{
    use WithPagination;

    public ?string $actionMessage = null;

    public ?string $actionError = null;

    public function close(PostService $posts, int $postId): void
    {
        $post = Post::findOrFail($postId);

        $this->authorize('close', $post);

        $this->runAction(fn () => $posts->close($post), __('Publication closed.'));
    }

    public function delete(PostService $posts, int $postId): void
    {
        $post = Post::findOrFail($postId);

        $this->authorize('delete', $post);

        $this->runAction(fn () => $posts->delete($post), __('Publication deleted.'));
    }

    public function render(): View
    {
        return view('livewire.posts.mine', [
            'posts' => Post::query()
                ->where('user_id', auth()->id())
                ->withInteractionCounts()
                ->withThirdPartyInteraction()
                ->latest()
                ->paginate(10),
        ]);
    }

    private function runAction(Closure $action, string $successMessage): void
    {
        $this->actionMessage = null;
        $this->actionError = null;

        try {
            $action();
            $this->actionMessage = $successMessage;
        } catch (PostAlreadyClosedException) {
            $this->actionError = __('This publication has already been closed.');
        } catch (PostHasInteractionsException) {
            $this->actionError = __('It is not possible to delete: the publication has already received interaction from other users.');
        }
    }
}
