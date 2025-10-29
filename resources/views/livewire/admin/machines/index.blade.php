<?php

use Livewire\Volt\Component;
use App\Models\Machine;
use App\Actions\Machine\DeleteMachine;

new class extends Component {
    public array $machines = [];

    public function mount(): void
    {
        $this->loadMachines();
    }

    public function loadMachines(): void
    {
        $this->machines = Machine::query()
            ->with(['deployments'])
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    public function deleteMachine(Machine $machine, DeleteMachine $deleteAction): void
    {
        $this->authorize('delete', Machine::class);
        $deleteAction->execute($machine);
        $this->loadMachines();
    }
};
?>
<x-page-wrapper title="Machines" back="1" backUrl="{{ route('dashboard') }}" button_text="Add Machine" button_url="{{ route('machines.create') }}">
    <table class="min-w-full bg-zinc-700 rounded overflow-hidden shadow divide-y divide-zinc-600">
        <thead class="bg-zinc-600">
            <tr>
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2">Type</th>
                <th class="px-4 py-2">IP</th>
                <th class="px-4 py-2">Deployments</th>
                <th class="px-4 py-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-600">
            @foreach($machines as $machine)
            <tr class="hover:bg-zinc-600 transition-colors">
                <td class="px-4 py-2 font-medium">{{ $machine['name'] }}</td>
                <td class="px-4 py-2 text-center">{{ ucfirst($machine['type']) }}</td>
                <td class="px-4 py-2">{{ $machine['ip'] ?? __('Local') }}</td>
                <td class="px-4 py-2 text-center">{{ count($machine['deployments']) }} <x-button href="{{ route('machines.deployments', ['machine' => $machine['id']]) }}" class="text-blue-400 hover:underline ml-4">View</x-button></td>
                <td class="px-4 py-2 flex gap-2 justify-center">
                    <a href="{{ route('machines.edit', $machine['id']) }}"
                        class="bg-green-700 hover:bg-green-800 px-3 py-1 rounded shadow transition">
                        Edit
                    </a>
                    <button
                        wire:click="deleteMachine(\App\Models\Machine::find({{ $machine['id'] }}), \App\Actions\Machine\DeleteMachine::class)"
                        wire:confirm="Are you sure you want to delete this machine?"
                        class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded shadow transition cursor-pointer">
                        Delete
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-page-wrapper>
