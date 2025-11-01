<?php

declare(strict_types=1);

namespace App\Livewire\SshKeys;

use App\Models\SshKey;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Actions\SshKey\DeleteSshKey;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public $file;
    public string $type = 'private';

    public function delete(DeleteSshKey $delete, int $id): void
    {
        $key = SshKey::findOrFail($id);
        $delete->execute($key);
    }

    public function with(): array
    {
        return [
            'keys' => SshKey::with('machines')->latest()->get(),
        ];
    }
};
?>

<x-page-wrapper title="SSH Keys" back="1" backUrl="{{ route('dashboard') }}" button_text="Add SSH Key" button_url="{{ route('ssh-keys.create') }}">
    <table class="w-full text-sm text-zinc-300">
        <thead>
            <tr class="border-b border-zinc-700">
                <th>Name</th>
                <th>Type</th>
                <th>Fingerprint</th>
                <th>Machine</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($keys as $key)
                <tr class="border-b border-zinc-800">
                    <td>{{ $key->name }}</td>
                    <td>{{ ucfirst($key->type) }}</td>
                    <td>{{ $key->fingerprint ?? '—' }}</td>
                    <td>
                        @foreach ($key->machines as $machine)
                            {{ $machine->name }}<br>
                        @endforeach
                    </td>
                    <td class="text-right">
                        <x-button wire:navigate href="{{ route('ssh-keys.edit', $key) }}" class="text-indigo-500 hover:text-indigo-400">Edit</x-button>
                        <x-button wire:click="delete({{ $key->id }})" wire:confirm="Delete this key?" class="text-red-500 hover:text-red-400">Delete</x-button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-page-wrapper>

