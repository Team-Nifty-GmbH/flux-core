<?php

namespace FluxErp\Traits\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\LaravelPasskeys\Models\Passkey;

trait InteractsWithPasskeys
{
    use \Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;

    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkey::class, 'authenticatable_id')
            ->where('authenticatable_type', $this->getMorphClass());
    }
}
