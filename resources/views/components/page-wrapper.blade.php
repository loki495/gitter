@props([
    'title' => '',
    'subtitle' => '',
    'back' => 0,
    'backUrl' => '',
    'button_text' => '',
    'button_url' => '',
])
<div class="w-full mx-auto space-y-6">
    @if ($back)
    <div class="mb-8">
        <a href="{{ $backUrl ?: url()->previous() }}" class="text-neutral-300 hover:text-neutral-400 transition"><< Back</a>
    </div>
    @endif
    <div class="flex justify-between items-center mb-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold">{{ $title }}</h1>
            <h2 class="text-2xl font-bold text-zinc-400 text-lg">{{ $subtitle }}</h2>
        </div>

        @if ($button_url)
        <x-button href="{{ $button_url }}"
            variant="primary">
            {{ $button_text }}
        </x-button>
        @endif

    </div>

    <div {{ $attributes->merge(['class' => 'flex flex-wrap gap-4']) }}>
        {{ $slot }}
    </div>
</div>

