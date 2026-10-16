@props(['item'])
<blockquote {{ $attributes->class(['catalogue-prose rounded-card border border-line bg-white p-7 text-sm leading-relaxed']) }}><span class="mb-3 block font-display text-5xl text-brand" aria-hidden="true">&ldquo;</span>{!! $item['html'] ?? e($item['quote'] ?? '') !!}</blockquote>
