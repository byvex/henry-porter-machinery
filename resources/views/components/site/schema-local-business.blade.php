@php
    $siteUrl = rtrim(config('app.url'), '/');
    $localBusinessData = [
        '@context' => 'https://schema.org',
        '@type' => 'AutomotiveBusiness',
        'name' => 'Henry Porter Machinery',
        'image' => $siteUrl . '/images/logo.jpeg',
        'url' => $siteUrl,
        'telephone' => '+442892622611',
        'foundingDate' => '2006',
        'priceRange' => '£££',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '13 Carnbane Road',
            'addressLocality' => 'Hillsborough',
            'addressRegion' => 'Co. Down',
            'postalCode' => 'BT26 6NQ',
            'addressCountry' => 'GB',
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => '54.4636',
            'longitude' => '-6.0645',
        ],
        'areaServed' => [
            'Northern Ireland',
            'Ireland',
            'United Kingdom',
        ],
        'sameAs' => [
            'https://www.facebook.com/henryportermachinery',
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($localBusinessData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
