<?php

namespace FluxErp\Support\Sanctum;

use Laravel\Sanctum\Sanctum;

class Guard extends \Laravel\Sanctum\Guard
{
    /**
     * Determine if the provided access token is valid.
     *
     * @param  mixed  $accessToken
     */
    protected function isValidAccessToken($accessToken): bool
    {
        if (! $accessToken) {
            return false;
        }

        $accessToken->last_used_at ?? $accessToken->last_used_at = $accessToken->created_at;

        $isValid =
            (! $this->expiration || $accessToken->last_used_at->gt(now()->subMinutes($this->expiration)))
            && $this->hasValidProvider($accessToken->tokenable)
            && (! $accessToken->tokenable instanceof \FluxErp\Models\Token || $accessToken->tokenable->isValid());

        if (is_callable(Sanctum::$accessTokenAuthenticationCallback)) {
            $isValid = (bool) (Sanctum::$accessTokenAuthenticationCallback)($accessToken, $isValid);
        }

        if ($accessToken->tokenable instanceof \FluxErp\Models\Token) {
            $accessToken->tokenable->use();

            if (! $accessToken->tokenable->isValid()) {
                $accessToken->delete();
            }
        } elseif (! $isValid) {
            $accessToken->delete();
        }

        return $isValid;
    }
}
