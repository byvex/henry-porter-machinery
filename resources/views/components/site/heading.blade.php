@props(['eyebrow' => null, 'title', 'description' => null, 'centered' => false])
<div {{ $attributes->class(['mb-13', 'text-center' => $centered]) }}>
 @if($eyebrow)<span class="section-eyebrow">{{ $eyebrow }}</span>@endif
 <h2 class="section-title">{{ $title }}</h2>
 @if($description)<p @class(['mt-3.5 max-w-xl text-muted leading-relaxed', 'mx-auto' => $centered])>{{ $description }}</p>@endif
</div>
