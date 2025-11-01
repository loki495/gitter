<?php


use App\Actions\Branch\PullDeploymentBranches;
use Livewire\Volt\Component;
use App\Models\Deployment;
use App\Models\Branch;
use App\Actions\Branch\SetActiveBranch;
use App\Actions\Branch\DeleteBranch;

new class extends Component {
    public Deployment $deployment;
    public $branches;

    public function mount(Deployment $deployment): void
    {
        $this->deployment = $deployment;
        $this->branches = $deployment->branches()->get();
    }

    public function pullBranches(): void
    {
        app(PullDeploymentBranches::class)->execute($this->deployment);
        $this->branches = $this->deployment->branches()->get();
    }

    public function setActiveBranch(int $branchId): void
    {
        $branch = Branch::findOrFail($branchId);
        app(SetActiveBranch::class)->execute($branch);
        $this->branches = $this->deployment->branches()->get();
    }

    public function deleteBranch(int $branchId): void
    {
        $branch = Branch::findOrFail($branchId);
        app(DeleteBranch::class)->execute($branch);
        $this->branches = $this->deployment->branches()->get();
    }
};
?>

<x-page-wrapper :title="'Branches for ' . $deployment->website->name . '@' . $deployment->machine->name" :subtitle="$deployment->path" class="flex flex-col gap-4" back="1" backUrl="{{ route('deployments.index', [$deployment]) }}" button_text="Pull Branches" button_action="pullBranches">

    <table class="w-full mt-4 text-left">
        <thead>
            <tr class="bg-zinc-900">
                <th class="p-2 text-center">Name</th>
                <th class="p-2 text-center">Active</th>
                <th class="p-2 text-center">Tracking Remote</th>
                <th class="p-2 text-center">Last Commit</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($branches as $branch)
                <tr class="border-t border-zinc-700 {{ $branch->is_active ? 'font-bold' : '' }}">
                    <td class="p-2 text-left">{{ $branch->name }}</td>
                    <td class="p-2 text-center">{{ $branch->is_active ? 'Yes' : 'No' }}</td>
                    <td class="p-2 text-center">{{ $branch->is_tracking_remote ? 'Yes' : 'No' }}</td>
                    <td class="p-2 text-center">{{ $branch->last_commit ?? '-' }}</td>
                    <td class="p-2 text-right">
                        @if (!$branch->is_active)
                            <x-button wire:click="setActiveBranch({{ $branch->id }})" class="text-green-400">Activate</x-button>
                        @endif
                        {{--<x-button href="{{ route('branches.edit', [$deployment->website, $deployment, $branch]) }}" variant="primary">Edit</x-button>--}}
                        <x-button wire:click="deleteBranch({{ $branch->id }})" wire:confirm="Delete branch '{{ $branch->name }}'?" variant="danger">Delete</x-button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-page-wrapper>

