<?php

declare(strict_types=1);

use App\Models\Machine;
use App\Models\Website;
use Livewire\Volt\Component;

new class extends Component {

    public array $websites = [];
    public array $machines = [];

    public function mount(): void
    {
        $this->websites = Website::with(['deployments'])->get()->toArray();
        $this->machines = Machine::with(['deployments'])->get()->toArray();
    }
}
?>
<x-page-wrapper title="Dashboard" class="flex flex-col gap-4">
    <x-card border="0" class="mb-8">
        <x-slot name="header">Websites</x-slot>
        <div class="flex flex-wrap gap-4">
            @foreach($websites as $website)
            <x-card class="mt-4">
                <x-slot name="header">{{ $website['name'] }}</x-slot>
                <x-slot name="subheader">{{ $website['description'] }}</x-slot>
                <div class="p-4">
                    Deployments: <x-button href="{{ route('deployments.index', ['website' => $website['id']]) }}" class="text-blue-400 hover:underline ml-4 p-2">{{ count($website['deployments']) }}</x-button>
                </div>
            </x-card>
            @endforeach
        </div>
    </x-card>

    <x-card border="0">
        <div class="flex flex-wrap gap-4">
            @foreach($machines as $machine)
            <x-slot name="header">Machines</x-slot>
            <x-card class="mt-4">
                <x-slot name="header">{{ $machine['name'] }}</x-slot>
                <x-slot name="subheader">{{ $machine['notes'] }}</x-slot>
                <div class="p-4">
                    Deployments: <x-button href="{{ route('machines.deployments', ['machine' => $machine['id']]) }}" class="text-blue-400 hover:underline ml-4 p-2">{{ count($machine['deployments']) }}</x-button>
                </div>
            </x-card>
            @endforeach
        </div>
    </x-card>
</x-page-wrapper>
