<?php


declare(strict_types=1);

use App\Actions\Website\DeleteWebsite;
use App\Models\Website;
use Livewire\Volt\Component;

new class extends Component
{
    public array $websites = [];

    public function mount(): void
    {
        $this->websites = Website::with(['deployments'])->get()->toArray();
    }

    public function deleteWebsite(DeleteWebsite $deleteWebsite, Website $website): void
    {
        try {
            $deleteWebsite->execute($website);
            session()->flash('success', 'Website deleted successfully.');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Failed to delete website.');
        }
    }
}
?>
<x-page-wrapper title="Websites" back="1" backUrl="{{ route('dashboard') }}" button_text="Add Website" button_url="{{ route('websites.create') }}">
    <table class="min-w-full bg-zinc-700 rounded overflow-hidden shadow divide-y divide-zinc-600">
        <thead class="bg-zinc-600">
            <tr>
                <th class="px-4 py-2 text-center">Name</th>
                <th class="px-4 py-2 text-center">Description</th>
                <th class="px-4 py-2 text-center">Deployments</th>
                <th class="px-4 py-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($websites as $website)
            <tr class="border-b border-zinc-700">
                <td class="px-4 py-2">{{ $website['name'] }}</td>
                <td class="px-4 py-2">{{ $website['description'] }}</td>
                <td class="px-4 py-2 text-center">{{ count($website['deployments']) }} <x-button href="{{ route('deployments.index', ['website' => $website['id']]) }}" class="text-blue-400 hover:underline ml-4 p-2">View</x-button></td>
                <td class="px-4 py-2 text-center">
                    <x-button href="{{ route('websites.edit', $website['id']) }}" variant="primary">Edit</x-button>
                    <x-button wire:click="deleteWebsite({{ $website['id'] }})"
                        onclick="return confirm('Delete this website?')"
                        variant="danger">
                        Delete
                    </x-button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-page-wrapper>
