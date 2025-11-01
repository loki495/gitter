<?php

declare(strict_types=1);

namespace App\Http\Livewire\Deployment;

use App\Models\DeploymentLog;
use Livewire\Volt\Component;

new class extends Component
{
    public string $search = '';

    public function mount(): void
    {
    }

    public function getLogsProperty()
    {
        $logs = DeploymentLog::with('deployment.machine')->latest()->take(100)->get();

        return $logs->filter(function ($log) {
            return str_contains(strtolower($log->command), strtolower($this->search))
                || str_contains(strtolower($log->deployment->machine->ip ?? ''), strtolower($this->search))
                || str_contains(strtolower($log->action), strtolower($this->search));
        });
    }

}
?>
<div class="bg-zinc-800 p-4 rounded-lg shadow-lg overflow-auto">
    <div class="mb-4">
        <label for="search" class="block text-sm font-medium text-gray-300">Filter Logs</label>
        <input
            type="text"
            id="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Type to filter..."
            class="mt-1 block w-full rounded-md bg-zinc-700 border border-zinc-600 text-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
    </div>

    <table class="min-w-full divide-y divide-zinc-700">
        <thead class="bg-zinc-900">
            <tr>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300">ID</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300">IP</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300">Action</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300">Command</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-300">Created At</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-700" wire:loading>
            <tr>
                <td colspan="5" class="px-4 py-2 text-center text-gray-400">Loading...</td>
            </tr>
        </tbody>
        <tbody class="divide-y divide-zinc-700" wire:loading.remove>
            @forelse($this->logs as $log)
                <tr class="hover:bg-zinc-700">
                    <td class="px-4 py-2 text-sm text-gray-200">{{ $log->id }}</td>
                    <td class="px-4 py-2 text-sm text-gray-200">{{ $log->deployment->machine->ip }}</td>
                    <td class="px-4 py-2 text-sm text-gray-200">{{ $log->action }}</td>
                    <td class="px-4 py-2 text-sm text-gray-200">{{ $log->command }}</td>
                    <td class="px-4 py-2 text-sm text-gray-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-2 text-center text-gray-400">No logs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

