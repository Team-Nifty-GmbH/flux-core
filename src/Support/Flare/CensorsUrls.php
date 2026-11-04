<?php

namespace FluxErp\Support\Flare;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait CensorsUrls
{
    protected function censorUrl(string $url): string
    {
        if (! Str::startsWith($url, ['http://', 'https://', '/'])) {
            return $url;
        }

        return rescue(
            function () use ($url): string {
                $request = Request::create($url);
                $route = Arr::first(
                    app('router')->getRoutes()->getRoutes(),
                    fn (Route $route): bool => $route->matches($request, false)
                );

                return (str_starts_with($url, '/') ? '' : $request->getSchemeAndHttpHost())
                    . '/' . ltrim($route?->uri() ?? static::CENSORED, '/');
            },
            static::CENSORED,
            false
        );
    }
}
