{{-- Renders a generated Material Symbols icon by name, e.g. <x-icon :name="$item['icon']" filled /> --}}
@props(['name', 'filled' => false])
<x-dynamic-component :component="'icon.'.str_replace('_', '-', $name)" :filled="$filled" {{ $attributes }} />
