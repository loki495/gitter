<?php

use Livewire\Volt\Component;
use App\Models\Deployment;
use App\Models\Branch;
use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\UpdateBranch;

new class extends Component {
    public Deployment $deployment;
    public ?Branch $branch = null;
    public string $name = '';
    public bool $is_tracking_remote = false;

    public function mount(Deployment $deployment, ?Branch $branch = null): void
    {
        $this->deployment = $deployment;
        $this->branch = $branch;
        if ($branch->exists) {
            $this->name = $branch->name;
            $this->is_tracking_remote = $branch->is_tracking_remote;
        }
    }

    public function save(): void
    {
        $data = [
            'name' => $this->name,
            'is_tracking_remote' => $this->is_tracking_remote,
        ];

        if ($this->branch->exists) {
            app(UpdateBranch::class)->execute($this->branch, $data);
        } else {
            app(CreateBranch::class)->execute($this->deployment, $data);
        }

        $this->redirectRoute('branches.index', $this->deployment);
    }

    public function render()
    {
        return view('livewire.branches.edit');
    }
};
?>

<div class="p-4 bg-zinc-800 text-white rounded-2xl max-w-lg">
    <h1 class="text-xl mb-4">
        {{ $branch ? "Edit Branch '{$branch->name}'" : 'Create Branch' }}
    </h1>

    <form wire:submit.prevent="save" class="space-y-3">
        <label class="flex items-center justify-between">
            <span>Name</span>
            <input type="text" wire:model="name" required class="text-black rounded p-1 w-2/3">
        </label>

        <label class="flex items-center justify-between">
            <span>Track Remote</span>
            <input type="checkbox" wire:model="is_tracking_remote">
        </label>

        <div class="pt-3">
            <button type="submit" class="px-3 py-1 bg-green-600 rounded" wire:loading.attr="disabled">
                Save
            </button>
        </div>
    </form>
</div>

