@props(['item'])
<div {{ $attributes->class(['flex items-center gap-3 font-display text-xs font-semibold text-muted']) }}><div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand/8 text-brand" aria-hidden="true"><x-icon :name="$item['icon']" filled class="size-5" /></div><span>{{ $item['title'] }}</span></div>
