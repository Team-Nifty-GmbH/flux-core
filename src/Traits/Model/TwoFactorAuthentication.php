<?php

namespace FluxErp\Traits\Model;

use FluxErp\Contracts\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys as SpatieHasPasskeys;

trait TwoFactorAuthentication
{
    use \Laragear\TwoFactor\TwoFactorAuthentication;

    public function hasTwoFactorMethodConfigured(): bool
    {
        return $this->hasTwoFactorEnabled()
            || (
                ($this instanceof HasPasskeys || $this instanceof SpatieHasPasskeys)
                && $this->passkeys()->exists()
            );
    }
}
