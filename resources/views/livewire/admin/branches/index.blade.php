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

<x-page-wrapper :title="$deployment->website->name" :subtitle="$deployment->path" class="flex flex-col gap-4" back="1" backUrl="{{ route('deployments.index', [$deployment]) }}" button_text="Pull Branches" button_action="pullBranches">

    <table class="w-full mt-4 text-left">
        <thead>
            <tr>
                <th>Name</th>
                <th>Active</th>
                <th>Tracking Remote</th>
                <th>Last Commit</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($branches as $branch)
                <tr class="border-t border-zinc-700">
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->is_active ? 'Yes' : 'No' }}</td>
                    <td>{{ $branch->is_tracking_remote ? 'Yes' : 'No' }}</td>
                    <td>{{ $branch->last_commit ?? '-' }}</td>
                    <td class="space-x-2">
                        <button wire:click="setActiveBranch({{ $branch->id }})" class="text-green-400">Activate</button>
                        <a href="{{ route('branches.edit', [$deployment, $branch]) }}" class="text-blue-400">Edit</a>
                        <button wire:click="deleteBranch({{ $branch->id }})" wire:confirm="Delete branch '{{ $branch->name }}'?" class="text-red-400">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-page-wrapper>

