<?php

namespace FluxErp\Support\Auth;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginThrottle
{
    public const int MAX_ATTEMPTS = 5;

    public static function key(?string $email): string
    {
        return Str::transliterate(Str::lower($email ?? '') . '|' . request()->ip());
    }

    public static function reserve(string $key): bool
    {
        return RateLimiter::hit($key) <= static::MAX_ATTEMPTS;
    }

    public static function message(string $key): string
    {
        return __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]);
    }

    public static function clear(string $key): void
    {
        RateLimiter::clear($key);
    }
}
