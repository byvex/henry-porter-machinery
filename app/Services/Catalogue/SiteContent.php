<?php

namespace App\Services\Catalogue;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SiteContent
{
    /** @return array<string,mixed> */
    public function data(): array
    {
        return json_decode(File::get(resource_path('data/legacy-pages.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return list<array<string,mixed>> */
    public function news(): array
    {
        $items = $this->data()['news'];
        usort($items, fn ($a, $b) => strcmp($b['published_at'], $a['published_at']));

        return array_map(fn ($n) => $n + ['url' => route('news.show', $n['slug']), 'date' => date('j F Y', strtotime($n['published_at'])), 'description' => Str::limit(trim(html_entity_decode(strip_tags($n['body_html']), ENT_QUOTES, 'UTF-8')), 160)], $items);
    }

    public function html(string $html): string
    {
        return app(CatalogueAdmin::class)->clean($html);
    }
}
