@props(['title', 'description' => null])
<section {{ $attributes->class(['admin-panel']) }}>
    <div class="mb-5"><h2 class="text-lg font-semibold">{{ $title }}</h2>@if($description)<p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>@endif</div>
    {{ $slot }}
</section>
