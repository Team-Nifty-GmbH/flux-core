<?php

namespace FluxErp\Traits\Model;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\LaravelPasskeys\Models\Passkey;

trait InteractsWithPasskeys
{
    use \Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;

    public function passkeys(): MorphMany
    {
        return $this->morphMany(Passkey::class, 'authenticatable');
    }
}
