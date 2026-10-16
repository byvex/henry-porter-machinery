@props(['label', 'hint' => null])
<label {{ $attributes->class(['block space-y-2']) }}>
    <span class="block text-sm font-medium">{{ $label }}</span>
    {{ $slot }}
    @if($hint)<span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ $hint }}</span>@endif
</label>
