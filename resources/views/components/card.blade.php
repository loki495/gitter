@props([
'header' => '',
'subheader' => '',
'border' => true
])
<div {{ $attributes->class([
    'flex flex-col rounded bg-zinc-800',
    'border border-zinc-700' => $border,
    ]) }}>
    @if ($header)<div class="flex items-center justify-between bg-zinc-700 p-2 font-bold">{{ $header }}
        @if ($subheader)
        <flux:tooltip content="{{ $subheader }}" toggleable>
            <flux:button icon="information-circle" size="sm" variant="ghost" />
            <flux:tooltip.content class="max-w-[20rem] space-y-2">
               <div class="!text-3xl">
                    {{ $subheader }}
               </div>
            </flux:tooltip.content>
        </flux:tooltip>
        @endif
    </div>@endif
    {{ $slot }}
</div>
