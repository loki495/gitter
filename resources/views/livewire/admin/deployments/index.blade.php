<?php


use App\Actions\Branch\PullDeploymentBranches;
use App\Models\Deployment;
use App\Models\Website;
use Livewire\Volt\Component;

new class extends Component {
    public array $deployments = [];
    public Website $website;

    public function mount(Website $website): void
    {
        $this->website = $website;
        $this->deployments = Deployment::with(['website', 'machine'])->withCount('branches')->get()->toArray();
    }

    public function deleteDeployment(int $id): void
    {
        $deployment = Deployment::findOrFail($id);
        $deployment->delete();
        session()->flash('success', 'Deployment deleted successfully.');

        // Refresh the list
        $this->deployments = Deployment::with(['website', 'machine'])->get()->toArray();
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
            $branches = app(PullDeploymentBranches::class)->execute($this->deployment);
            dd($branches);

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

<x-page-wrapper title="Deployments" :subtitle="$website->name" class="flex flex-col gap-4" back="1" backUrl="{{ route('websites.index') }}" button_text="Add Deployment" button_url="{{ route('deployments.create', ['website' => $website]) }}">
    <table class="min-w-full bg-zinc-700 rounded overflow-hidden shadow divide-y divide-zinc-600">
        <thead class="bg-zinc-600">
            <tr class="text-left text-zinc-300">
                <th class="px-4 py-2">Machine</th>
                <th class="px-4 py-2">Path</th>
                <th class="px-4 py-2">URL</th>
                <th class="px-4 py-2">Branches</th>
                <th class="px-4 py-2">Primary</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-700">
            @foreach($deployments as $deployment)
            <tr class="hover:bg-zinc-700">
                <td class="px-4 py-2">{{ $deployment['machine']['name'] ?? '-' }}</td>
                <td class="px-4 py-2">{{ $deployment['path'] }}</td>
                <td class="px-4 py-2">
                    @if($deployment['url'])
                    <a href="{{ $deployment['url'] }}" class="text-indigo-400 hover:underline" target="_blank">
                        {{ $deployment['url'] }}
                    </a>
                    @else
                    -
                    @endif
                </td>
                <td class="px-4 py-2 flex gap-2">
                    <span class="text-zinc-400 font-semibold">{{ $deployment['branches_count'] }}</span>
                    <x-button
                        wire:click="pullBranches({{ $deployment['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="pullBranches({{ $deployment['id'] }})"
                        size="sm">
                        Pull
                    </x-button>

                    <x-button
                        href="{{ route('branches.index', ['website' => $website, 'deployment' => $deployment['id']]) }}"
                        variant="primary"
                        size="sm">
                        View
                    </x-button>
                </td>
                <td class="px-4 py-2">
                    @if($deployment['is_primary'])
                    <span class="text-green-400 font-semibold">Yes</span>
                    @else
                    No
                    @endif
                </td>
                <td class="px-4 py-2 text-right flex justify-end gap-2">
                    <x-button href="{{ route('deployments.edit', ['website' => $website, 'deployment' => $deployment['id']]) }}"
                        variant="primary">
                        Edit
                    </x-button>
                    <x-button
                        wire:click="deleteDeployment({{ $deployment['id'] }})"
                        onclick="confirm('Are you sure?') || event.stopImmediatePropagation()"
                        variant="danger">
                        Delete
                    </x-button>
                </td>
            </tr>
            @endforeach

            @if(count($deployments) === 0)
            <tr>
                <td colspan="6" class="px-4 py-6 text-center text-zinc-400">
                    No deployments found.
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</x-page-wrapper>
