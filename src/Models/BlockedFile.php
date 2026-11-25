<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasUserModification;

class BlockedFile extends FluxModel
{
    use HasUserModification;

    public static function isBlocked(string $hash): bool
    {
        return static::query()
            ->where('hash', $hash)
            ->exists();
    }
}
