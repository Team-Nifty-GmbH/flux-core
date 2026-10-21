<?php

namespace FluxErp\Support\Flare;

use Closure;
use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;

/**
 * Error reports leave the server, so they must not carry personal data.
 * Livewire posts the whole component state (addresses, contacts, orders) as
 * request body, query strings carry search terms, and the user and bound route
 * models are serialized with all their attributes.
 */
class CensorPersonalData implements FlareMiddleware
{
    public const string CENSORED = '<CENSORED>';

    public function handle(Report $report, Closure $next)
    {
        $context = $report->allContext();

        // Console and queue reports carry no request.
        if (! array_key_exists('request_data', $context)) {
            return $next($report);
        }

        // allContext() merges the provided context recursively over the
        // collected one, so an array would only add keys. A scalar replaces
        // the whole branch.
        $censored = [
            'request_data' => [
                'queryString' => static::CENSORED,
                'body' => static::CENSORED,
                'files' => static::CENSORED,
            ],
            'cookies' => static::CENSORED,
            'session' => static::CENSORED,
        ];

        if ($url = data_get($context, 'request.url')) {
            $censored['request']['url'] = strtok($url, '?');
        }

        if (is_array($user = data_get($context, 'user'))) {
            $censored['user'] = array_map(
                fn (mixed $value) => static::CENSORED,
                array_diff_key($user, array_flip(['id', 'uuid']))
            );
        }

        foreach (data_get($context, 'route.routeParameters') ?? [] as $name => $parameter) {
            if (is_array($parameter)) {
                $censored['route']['routeParameters'][$name] = static::CENSORED;
            }
        }

        $report->userProvidedContext(array_replace_recursive($context, $censored));

        return $next($report);
    }
}
