<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 */
class Passkey extends \Laravel\Passkeys\Passkey
{
    use HasUuids;
}
