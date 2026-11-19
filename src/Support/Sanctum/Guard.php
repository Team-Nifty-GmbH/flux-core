<?php

namespace FluxErp\Support\Sanctum;

use FluxErp\Models\Token;
use Laravel\Sanctum\Sanctum;

class Guard extends \Laravel\Sanctum\Guard
{
    protected function hasValidProvider($tokenable): bool
    {
        if (is_null($this->provider)) {
            return true;
        }

        $providerModel = config("auth.providers.$this->provider.model");

        return is_a(
            morphed_model($tokenable->getMorphClass()) ?? $providerModel,
            $providerModel,
            true,
        );
    }

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

        $tokenable = $accessToken->tokenable;
        $ownsToken = ! is_null($tokenable) && $this->hasValidProvider($tokenable);

        $isValid =
            $ownsToken
            && (! $this->expiration || $accessToken->last_used_at->gt(now()->subMinutes($this->expiration)))
            && (! $tokenable instanceof Token || $tokenable->isValid());

        if (is_callable(Sanctum::$accessTokenAuthenticationCallback)) {
            $isValid = (bool) (Sanctum::$accessTokenAuthenticationCallback)($accessToken, $isValid);
        }

        if ($tokenable instanceof Token) {
            $isValid = $isValid && $tokenable->use();

            if ($ownsToken && ! $tokenable->isValid()) {
                $accessToken->delete();
            }
        } elseif (! $isValid && ($ownsToken || is_null($tokenable))) {
            $accessToken->delete();
        }

        return $isValid;
    }
}
