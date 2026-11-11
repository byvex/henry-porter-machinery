@props(['title' => 'Henry Porter Machinery | Agricultural Machinery Northern Ireland', 'description' => 'Premium agricultural machinery, genuine parts and expert service from Henry Porter Machinery in Lisburn, Northern Ireland.', 'image'=>null, 'noindex'=>false])
@php
    $navLinks = [
        [
            'label' => 'New Machinery',
            'href' => route('machinery.new'),
            'active' => request()->routeIs('machinery.new') || request()->is('brands/pottinger*') || request()->is('brands/yardex*') || request()->is('brands/multione*') || request()->is('brands/hustler*') || request()->is('brands/bobman*'),
            'children' => [
                [
                    'label' => 'Pottinger Grassland & Tillage',
                    'sublabel' => 'Mowers, tedders, rakes, silage wagons & tillage',
                    'href' => route('brands.show', 'pottinger'),
                    'active' => request()->is('brands/pottinger*'),
                ],
                [
                    'label' => 'Yardex',
                    'sublabel' => 'Mini diggers, handlers and tracked dumpers',
                    'href' => route('brands.show', 'yardex'),
                    'active' => request()->is('brands/yardex*'),
                ],
                [
                    'label' => 'MultiOne Compact Loaders',
                    'sublabel' => 'Articulated mini loaders & multi-functional attachments',
                    'href' => route('brands.show', 'multione'),
                    'active' => request()->is('brands/multione*'),
                ],
                [
                    'label' => 'Hustler Equipment',
                    'sublabel' => 'Bale handling solutions including Unrollers and Softhands',
                    'href' => route('brands.show', 'hustler'),
                    'active' => request()->is('brands/hustler*'),
                ],
                [
                    'label' => 'Bobman Cubicle Bedders',
                    'sublabel' => 'Self-propelled cubicle cleaners & bedders',
                    'href' => route('brands.show', 'bobman'),
                    'active' => request()->is('brands/bobman*'),
                ],
                [
                    'label' => 'Other Machinery',
                    'sublabel' => 'Fleming, Malone, Johnston shear grabs & yard equipment',
                    'href' => route('categories.show', 'other-machinery'),
                    'active' => request()->is('categories/other-machinery*'),
                ],
            ],
        ],
        ['label' => 'Used Machinery', 'href' => route('machinery.used'), 'active' => request()->routeIs('machinery.used')],
        ['label' => 'Parts', 'href' => route('parts'), 'active' => request()->routeIs('parts')],
        ['label' => 'Service', 'href' => route('service'), 'active' => request()->routeIs('service')],
        ['label' => 'About', 'href' => route('about'), 'active' => request()->routeIs('about')],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
@php($canonical = rtrim(config('app.url'),'/').'/'.ltrim(request()->path()==='/'?'':request()->path(),'/').(request()->integer('page',1)>1?'?page='.request()->integer('page'):''))
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $noindex || request()->hasAny(['q','brands','categories','condition','sort']) ? 'noindex,follow' : 'index,follow' }}">
<meta property="og:title" content="{{ $title }}"><meta property="og:description" content="{{ $description }}"><meta property="og:url" content="{{ $canonical }}"><meta property="og:type" content="website">@if($image)<meta property="og:image" content="{{ str_starts_with($image,'/') ? rtrim(config('app.url'),'/').$image : $image }}">@endif
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
@if($image)
<meta name="twitter:image" content="{{ str_starts_with($image,'/') ? rtrim(config('app.url'),'/').$image : $image }}">
@endif
<x-site.schema-local-business />
@stack('schema')
<link rel="stylesheet" href="{{ asset('css/catalogue.css') }}?v={{ filemtime(public_path('css/catalogue.css')) }}">
<script src="{{ asset('js/catalogue.js') }}?v={{ filemtime(public_path('js/catalogue.js')) }}" defer></script>
<link rel="icon" href="/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
@vite('resources/css/app.css')
<link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
@if(request()->routeIs('about'))
<link rel="stylesheet" href="{{ asset('css/about.css') }}?v={{ filemtime(public_path('css/about.css')) }}">
@endif
</head>
<body class="public-site bg-canvas font-body text-ink antialiased">
<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:p-4">Skip to content</a>
<x-site.header :links="$navLinks" />
<main id="main-content">{{ $slot }}</main>
<x-site.footer />
<x-site.mobile-menu :links="$navLinks" />
<x-site.cookie-consent />
</body>
</html>
