@props(['name'])
<svg {{ $attributes->class(['size-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('conversation')
            <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z"/><path d="M8 10h8M8 14h5"/>
            @break
        @case('machinery')
            <circle cx="7" cy="17" r="4"/><circle cx="19" cy="18" r="3"/><path d="M11 17h5M3 13V7h8l3 6h5v2M7 7V3h4M14 13h-3V7M17 13V9"/>
            @break
        @case('support')
            <path d="m14 6 4 4M4 20l9-9a6 6 0 0 1 7-8l-4 4 1 3 3 1 4-4a6 6 0 0 1-8 7l-9 9Z" transform="translate(-2 -1)"/>
            @break
    @endswitch
</svg>
