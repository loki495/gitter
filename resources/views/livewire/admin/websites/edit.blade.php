<?php

use App\Models\Website;
use App\Actions\Website\CreateWebsite;
use App\Actions\Website\UpdateWebsite;
use Livewire\Volt\Component;

new class extends Component {
    public ?Website $website = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $branch = null;

    public function mount(?Website $website): void
    {
        $this->website = $website;

        if ($website->exists()) {
            $this->fill($website->only(['name', 'description' ]));
        }
    }

    public function save(UpdateWebsite $updateWebsite): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        if ($this->website->exists) {
            (new UpdateWebsite())->execute($this->website, $validated);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Website updated successfully",
                'redirect' => route('websites.index'),
            ]);
        } else {
            (new CreateWebsite())->execute($validated);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Website updated successfully",
                'redirect' => route('websites.index'),
            ]);
        }
        redirect()->route('websites.index');
    }
};
?>

<div class="p-6 space-y-6">
    <h2 class="text-2xl font-bold">Edit Website</h2>

    <form wire:submit.prevent="save" class="space-y-4">
        <div class="space-y-6">

            {{-- Name --}}
            <div>
                <div class="flex items-center gap-4">
                    <label class="w-40 text-sm font-medium text-zinc-300">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        wire:model="name"
                        class="w-full rounded-lg bg-zinc-700 border border-zinc-600 text-zinc-100 p-2 focus:ring focus:ring-indigo-500" />
                </div>
                @error('name')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description (optional, for Websites) --}}
            <div>
                <div class="flex items-center gap-4">
                    <label class="w-40 text-sm font-medium text-zinc-300">Description</label>
                    <textarea
                        wire:model="description"
                        class="w-full rounded-lg bg-zinc-700 border border-zinc-600 text-zinc-100 p-2 focus:ring focus:ring-indigo-500"></textarea/>
                </div>
                @error('description')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-zinc-700">
                <x-button variant="danger" href="{{ route('websites.index') }}"
                    >Cancel</x-button>

                <x-button type="submit"
                    wire:click="save"
                    variant="primary">
                    Save
                </x-button>
            </div>
        </div>
    </form>

    @if (session('success'))
        <p class="text-green-600 text-sm">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p class="text-red-600 text-sm">{{ session('error') }}</p>
    @endif
</div>
