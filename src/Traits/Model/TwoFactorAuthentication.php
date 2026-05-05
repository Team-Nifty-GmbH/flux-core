<?php

namespace FluxErp\Traits\Model;

use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;

trait TwoFactorAuthentication
{
    use \Laragear\TwoFactor\TwoFactorAuthentication;

    public function hasTwoFactorMethodConfigured(): bool
    {
        return $this->hasTwoFactorEnabled()
            || (
                $this instanceof HasPasskeys
                && $this->passkeys()->exists()
            );
    }
}
