<?php

namespace App\Livewire\Posts;

use App\Enums\PostType;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    public string $title = '';

    public string $type = '';

    public string $description = '';

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PostType::class)],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        auth()->user()->posts()->create($validated);

        session()->flash('status', __('Publication created.'));

        $this->redirect(route('posts.mine'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.posts.create');
    }
}
