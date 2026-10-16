@props([
    'title',
    'description',
])

<div class="auth-header flex w-full flex-col text-center">
    <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
    <flux:subheading>{{ $description }}</flux:subheading>
</div>
