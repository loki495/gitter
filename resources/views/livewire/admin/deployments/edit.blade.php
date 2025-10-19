<?php

use App\Actions\Deployment\CreateDeployment;
use App\Actions\Deployment\UpdateDeployment;
use App\Models\Deployment;
use App\Models\Machine;
use App\Models\Website;
use Livewire\Volt\Component;

new class extends Component {
    public ?Deployment $deployment = null;

    public Website $website;
    public ?int $website_id = null;
    public ?int $machine_id = null;
    public string $path = '';
    public ?string $url = null;
    public bool $is_primary = false;

    public array $machines = [];

    public function mount(Website $website, ?Deployment $deployment): void
    {
        $this->website = $website;
        $this->website_id = $website->id;
        $this->machines = Machine::pluck('name', 'id')->toArray() ?? [];

        if ($deployment->exists) {
            $this->deployment = $deployment;
            $this->fill($deployment->only([
                'website_id',
                'machine_id',
                'path',
                'url',
                'is_primary',
            ]));
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'website_id' => ['required', 'integer', 'exists:websites,id'],
            'machine_id' => ['required', 'integer', 'exists:machines,id'],
            'path' => ['required', 'string'],
            'url' => ['nullable', 'string'],
            'is_primary' => ['boolean'],
        ]);

        if ($this->deployment instanceof \App\Models\Deployment) {
            (new UpdateDeployment())->execute($this->deployment, $validated);
            session()->flash('success', 'Deployment updated successfully.');
        } else {
            $deployment = (new CreateDeployment())->execute($validated);
            session()->flash('success', 'Deployment created successfully.');
        }
        redirect()->route('deployments.index', ['website' => $this->website]);
    }
};
?>

<x-page-wrapper :title="$deployment ? 'Edit Deployment' : 'Create Deployment'" :subtitle="$website->name" back="1">

    <form wire:submit.prevent="save" class="space-y-6">

        {{-- Website --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex items-center gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">Website</label>
                <div class="">
                    {{ $website->name }}
                    <input type="hidden" wire:model="website_id" />
                </div>
            </div>
        </div>

        {{-- Machine --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex items-center gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">Machine <span class="text-red-500">*</span></label>
                <select wire:model="machine_id"
                    class="w-full rounded-lg bg-zinc-700 border border-zinc-600 text-zinc-100 p-2 focus:ring focus:ring-indigo-500">
                    <option value="">Select machine</option>
                    @foreach($machines as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @error('machine_id')
            <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Path --}}
        <x-input
            label="Path"
            id="path"
            wire:model="path"
            placeholder="/var/www/project"
            required
        />

        {{-- URL --}}
        <x-input
            label="URL"
            id="url"
            wire:model="url"
            placeholder="https://example.com"
        />

        {{-- Is Primary --}}
        <div class="flex items-center gap-4">
            <label class="w-40 text-sm font-medium text-zinc-300">Primary Deployment</label>
            <input type="checkbox" wire:model="is_primary" class="h-5 w-5 text-indigo-600 rounded border-zinc-600" />
        </div>

        {{-- Buttons --}}
        <div class="flex justify-end gap-3 pt-4">
            <x-button
                :href="route('deployments.index', ['website' => $website])"
                class="bg-red-600 hover:bg-red-700"
                type="button"
                wire:cancel
            >
                Cancel
            </x-button>

            <x-button type="submit" wire:click="save" variant="primary">
                {{ $deployment ? 'Update Deployment' : 'Create Deployment' }}
            </x-button>
        </div>
    </form>
</x-page-wrapper>
