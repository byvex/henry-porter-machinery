@props(['label' => 'Image placeholder', 'src' => null, 'alt' => ''])
@if($src)<img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" {{ $attributes->class(['size-full object-cover']) }}>
@else<div role="img" aria-label="{{ $label }}" data-label="{{ $label }}" {{ $attributes->class(['image-placeholder']) }}></div>@endif
