<?php

namespace App\Services\Catalogue;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class MediaFolders
{
    public function folder(Product|Category|Brand $owner): string
    {
        $date = CarbonImmutable::parse($owner->getRawOriginal('media_folder_date') ?? $owner->created_at ?? now());
        $owner->setAttribute('media_folder_date', $date);

        return $owner->getTable().'/'.$date->format('Y/m-d').'/'.$owner->getKey();
    }

    public function filename(string $original, string $extension): string
    {
        $name = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'image';
        $suffix = '-'.Str::uuid7().'.'.strtolower($extension);

        return substr($name, 0, 100 - strlen($suffix)).$suffix;
    }
}
