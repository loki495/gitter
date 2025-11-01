<?php




declare(strict_types=1);

namespace App\Livewire\SshKeys;

use App\Actions\SshKey\CreateSshKey;
use App\Actions\SshKey\UpdateSshKey;
use App\Models\SshKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    public ?SshKey $sshKey = null;
    public string $name = '';
    public string $type = 'private';
    public $file = null;
    public bool $replaceFile = false;

    public function mount(?SshKey $sshKey): void
    {
        if ($sshKey->exists) {
            $this->sshKey = $sshKey;
            $this->name = $sshKey->name;
            $this->type = $sshKey->type;
        }
    }

    public function save(CreateSshKey $create, UpdateSshKey $update)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ];

        if (!$this->sshKey || $this->replaceFile) {
            $rules['file'] = 'required|file';
        }

        $validated = $this->validate($rules);

        if ($this->sshKey) {
            if ($this->replaceFile && $this->file) {
                $validated['file'] = $this->file;
            } else {
                unset($validated['file']);
            }

            $update->execute($this->sshKey, $validated);
            session()->flash('success', 'SSH Key updated successfully!');
        } else {
            $create->execute($validated['name'], $validated['file'], $validated['type']);
            session()->flash('message', 'SSH Key saved successfully.');
        }

        return Redirect::route('ssh-keys.index');
    }

    public function cancel()
    {
        return Redirect::route('ssh-keys.index');
    }
};

?>

<x-page-wrapper title="Edit SSH Keys" back="1" backUrl="{{ route('ssh-keys.index') }}">
    <form wire:submit.prevent="save" class="space-y-4">
        {{-- Name --}}
        <div>
            <div class="flex items-center gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name"
                    class="w-full rounded-lg bg-zinc-700 border border-zinc-600 text-zinc-100 p-2 focus:ring focus:ring-indigo-500" />
            </div>
            @error('name')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Type --}}
        <div>
            <div class="flex items-center gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">Type <span class="text-red-500">*</span></label>
                <select id="type" wire:model="type" class="w-2/3 bg-zinc-700 rounded p-2 text-white" required>
                    <option value="private">Private</option>
                    <option value="public">Public</option>
                </select>
            </div>
            @error('type')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- File --}}
        <div>
            <div class="flex items-center gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">
                    File <span class="text-red-500">*</span>
                </label>

                @if ($sshKey && $sshKey->filename)
                <div class="flex flex-col gap-2 w-2/3">
                    <div class="flex items-center justify-between bg-zinc-700 rounded p-2 text-white">
                        <span class="truncate">{{ basename($sshKey->filename) }}</span>
                        <button
                            type="button"
                            wire:click="$set('replaceFile', true)"
                            class="text-xs text-blue-400 hover:text-blue-300"
                        >
                            Replace
                        </button>
                    </div>

                    @if ($replaceFile)
                    <input id="file" type="file" wire:model="file" class="bg-zinc-700 rounded p-2 text-white" />
                    @endif
                </div>
                @else
                <input id="file" type="file" wire:model="file" class="w-2/3 bg-zinc-700 rounded p-2 text-white" />
                @endif
            </div>

            @error('file')
            <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>


        <div class="flex justify-end space-x-2 pt-4">
            <x-button wire:click="cancel" variant="danger" type="button">Cancel</x-button>
            <x-button wire:click="save" variant="primary" wire:loading.attr="disabled">Save</x-button>
        </div>
    </form>
</x-page-wrapper>

