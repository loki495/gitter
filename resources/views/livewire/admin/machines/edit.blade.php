<?php


use App\Models\User;
use Livewire\Volt\Component;
use App\Actions\Machine\{CreateMachine, UpdateMachine};
use App\Models\Machine;

new class extends Component
{
    public ?Machine $machine = null;

    public string $name = '';
    public string $type = '';
    public ?string $ip = null;
    public string $ssh_user = '';
    public int $ssh_port = 22;
    public ?string $ssh_key_id = null;
    public ?string $notes = null;
    public $sshKeys = [];

    public function mount(?Machine $machine): void
    {
        $user = auth()->user();

        $this->machine = $machine;
        $this->sshKeys = $user->sshKeys->toArray();

        if ($machine->exists) {
            $this->name = $machine->name;
            $this->type = $machine->type;
            $this->ip = $machine->ip;
            $this->ssh_user = $machine->ssh_user;
            $this->ssh_port = $machine->ssh_port;
            $this->ssh_key_id = $machine->ssh_key_id ?? '';
            $this->notes = $machine->notes;
        }
    }

    public function save(CreateMachine $create, UpdateMachine $update): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'ip' => 'nullable|min:7',
            'ssh_user' => 'required|string|max:255',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'ssh_key_id' => 'required|exists:ssh_keys,id',
            'notes' => 'nullable|string',
        ]);

        if ($this->machine->exists) {
            $update->execute($this->machine, $validated);
            session()->flash('success', 'Machine updated successfully!');
        } else {
            $create->execute($validated);
            session()->flash('success', 'Machine created successfully!');
        }

        $this->redirectRoute('machines.index');
    }

    public function title(): string
    {
        return $this->machine->exists
            ? "Edit Machine: {$this->machine->name}"
            : 'Create Machine';
    }
};
?>

<div class="max-w-3xl mx-auto p-8 bg-zinc-800 rounded-2xl shadow-lg text-zinc-100 space-y-6">
    <h1 class="text-2xl font-semibold text-zinc-100">
        {{ $this->title() }}
    </h1>

    <form wire:submit.prevent="save" class="space-y-5">

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
                <input type="text" wire:model="type"
                    class="w-full rounded-lg bg-zinc-700 border border-zinc-600 text-zinc-100 p-2 focus:ring focus:ring-indigo-500" />
            </div>
            @error('type')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- IP --}}
        <div>
            <div class="flex items-center gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">IP</label>
                <input type="text" wire:model="ip" placeholder="192.168.1.10"
                    class="w-full rounded-lg bg-zinc-700 border border-zinc-600 text-zinc-100 p-2" />
            </div>
            @error('ip')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- SSH User --}}
        <div>
            <div class="flex items-center gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">SSH User <span class="text-red-500">*</span></label>
                <input type="text" wire:model="ssh_user" placeholder="ubuntu"
                    class="w-full rounded-lg bg-zinc-700 border border-zinc-600 text-zinc-100 p-2" />
            </div>
            @error('ssh_user')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- SSH Port --}}
        <div>
            <div class="flex items-center gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">SSH Port</label>
                <input type="number" wire:model="ssh_port" min="1" max="65535"
                    class="w-24 rounded-lg bg-zinc-700 border border-zinc-600 text-zinc-100 p-2" />
            </div>
            @error('ssh_port')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- SSH Key --}}
        <div>
            <div class="flex items-center gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">SSH Key</label>
                <select id="ssh_key_id" wire:model="ssh_key_id" class="w-2/3 bg-zinc-700 rounded p-2 text-white">
                    <option value="">-- Select SSH Key --</option>
                    @foreach ($sshKeys as $sshKey)
                        <option value="{{ $sshKey['id'] }}">{{ $sshKey['name'] }}</option>
                    @endforeach
                </select>
                <x-button href="{{ route('ssh-keys.create') }}" class="text-blue-400 hover:underline ml-4 p-2">Create SSH Key</x-button>
            </div>
            @error('ssh_key_id')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Notes --}}
        <div>
            <div class="flex items-start gap-4">
                <label class="w-40 text-sm font-medium text-zinc-300">Notes</label>
                <textarea wire:model="notes" rows="3"
                    class="w-full rounded-lg bg-zinc-700 border border-zinc-600 text-zinc-100 p-2"></textarea>
            </div>
            @error('notes')
                <p class="text-red-400 text-sm ml-40 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Buttons --}}
        <div class="flex justify-end gap-3 pt-6">
            <a href="{{ route('machines.index') }}"
                class="px-4 py-2 rounded-lg bg-zinc-700 hover:bg-zinc-600 text-zinc-100">
                Cancel
            </a>
            <button type="submit"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-70"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-white font-semibold flex items-center gap-2">
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save" class="animate-pulse">Saving...</span>
            </button>
        </div>
    </form>
</div>

