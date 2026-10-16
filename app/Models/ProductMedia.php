<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductMedia extends Pivot
{
    use HasUuids;

    protected $table = 'product_media';

    public $timestamps = false;
}
