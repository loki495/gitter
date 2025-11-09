<?php



use App\Actions\Branch\PullDeploymentBranches;
use App\Models\Deployment;
use Livewire\Volt\Component;
use App\Models\Machine;
use App\Actions\Machine\DeleteMachine;

new class extends Component {
    public Machine $machine;

    public function mount(Machine $machine): void
    {
        $this->machine = $machine;
    }

    public function deleteMachine(DeleteMachine $delete): void
    {
        $delete->execute($this->machine);
    }

    /**
     * Pull all branches for a given deployment
     */
    public function pullBranches(int $deploymentId): void
    {
        /** @var Deployment|null $deployment */
        $deployment = Deployment::find($deploymentId);

        if (! $deployment) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Deployment not found.",
            ]);
            return;
        }

        try {
            $branches = (new PullDeploymentBranches)->execute($deployment);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Branches pulled successfully for deployment '{$deployment->path}'.",
            ]);

            // Optionally refresh branches count or reload deployment info
            $deployment->refresh();
        } catch (\Throwable $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Failed to pull branches: " . $e->getMessage(),
            ]);
        }
    }
};
?>

<x-page-wrapper title="Deployments" :subtitle="$machine->name" class="flex flex-col gap-4">
    <table class="min-w-full bg-zinc-700 rounded overflow-hidden shadow divide-y divide-zinc-600">
        <thead class="bg-zinc-600">
            <tr>
                <th class="px-4 py-2">Website</th>
                <th class="px-4 py-2">Path</th>
                <th class="px-4 py-2">URL</th>
                <th class="px-4 py-2">Primary</th>
                <th class="px-4 py-2">Branches</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-600">
            @foreach($machine->deployments as $deployment)
            <tr class="hover:bg-zinc-700">
                <td class="px-4 py-2">{{ $deployment->website->name }}</td>
                <td class="px-4 py-2">{{ $deployment->path }}</td>
                <td class="px-4 py-2">
                    @if($deployment->url)
                        <a href="{{ $deployment->url }}" class="text-indigo-400 hover:underline" target="_blank">
                            {{ $deployment->url }}
                        </a>
                    @else
                        -
                    @endif
                </td>
                <td class="px-4 py-2">
                    @if($deployment->is_primary)
                        <span class="text-green-400 font-semibold">Yes</span>
                    @else
                        No
                    @endif
                </td>
                <td class="px-4 py-2">
                    {{ $deployment->branches()->count() }}

                    <x-button wire:navigate href="{{ route('branches.index', ['website' => $deployment->website_id, 'deployment' => $deployment->id]) }}">
                        View
                    </x-button>
                </td>
                <td class="px-4 py-2 text-right">
                    <x-button
                        wire:click="pullBranches({{ $deployment->id }})"
                        wire:loading.attr="disabled"
                        wire:target="pullBranches({{ $deployment->id }})"
                        size="sm">
                        Pull
                    </x-button>
                    <a href="{{ route('deployments.edit', ['website' => $deployment->website_id, 'deployment' => $deployment->id]) }}"
                       class="px-3 py-1 rounded-lg bg-yellow-600 text-white hover:bg-yellow-700 transition">
                        Edit
                    </a>
                    <button type="button"
                            wire:click="deleteDeployment({{ $deployment->id }})"
                            onclick="confirm('Are you sure?') || event.stopImmediatePropagation()"
                            class="px-3 py-1 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                        Delete
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-page-wrapper>

