@use('App\Enums\PostType')

<div class="py-12">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <h1 class="mb-6 text-xl font-semibold text-gray-800">{{ __('New publication') }}</h1>

            <form wire:submit="save" class="space-y-5">
                <div>
                    <x-input-label for="title" :value="__('Title')" />
                    <x-text-input wire:model="title" id="title" class="block w-full" type="text" name="title" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label :value="__('Type')" />

                    <div class="mt-1 flex gap-6">
                        @foreach (PostType::cases() as $postType)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input wire:model="type" type="radio" name="type" value="{{ $postType->value }}" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                {{ $postType->label() }}
                            </label>
                        @endforeach
                    </div>

                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <x-textarea wire:model="description" id="description" class="block w-full" name="description" rows="6" required />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="space-y-3 pt-2">
                    <x-primary-button>{{ __('Publish') }}</x-primary-button>

                    <p class="text-center text-sm">
                        <a href="{{ route('posts.mine') }}" class="text-gray-600 hover:text-gray-900" wire:navigate>{{ __('Cancel') }}</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
