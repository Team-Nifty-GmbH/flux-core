<?php

namespace FluxErp\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasPasskeys
{
    public function passkeys(): MorphMany;

    public function getPassKeyName(): string;

    public function getPassKeyId(): string;

    public function getPassKeyDisplayName(): string;
}
