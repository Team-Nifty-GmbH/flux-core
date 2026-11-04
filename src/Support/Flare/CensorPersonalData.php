<?php

namespace FluxErp\Support\Flare;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Spatie\FlareClient\Context\ContextProvider;

/**
 * Error reports leave the server, so they must not carry personal data.
 * Livewire posts the whole component state (addresses, contacts, orders) as
 * request body, query strings carry search terms, and the user and bound route
 * models are serialized with all their attributes.
 */
class CensorPersonalData
{
    use CensorsUrls;

    public const string CENSORED = '<CENSORED>';

    protected const array CENSORED_BRANCHES = [
        'dumps',
        'laravel_context',
        'logs',
    ];

    protected const array CENSORED_HEADERS = [
        'api-key',
        'authorization',
        'cookie',
        'set-cookie',
        'x-csrf-token',
        'x-forwarded-for',
        'x-real-ip',
        'x-xsrf-token',
    ];

    public function handle(object $report, Closure $next): mixed
    {
        $throwable = $report->getThrowable();
        if ($throwable instanceof QueryException) {
            $report->message($this->censorQueryException($throwable));
        }

        $context = $report->allContext();

        // Console and queue reports carry no request.
        if (! array_key_exists('request_data', $context)) {
            return $next($report);
        }

        $censored = [
            'cookies' => static::CENSORED,
            'session' => static::CENSORED,
        ];

        if ($url = data_get($context, 'request.url')) {
            $censored['request']['url'] = $this->censorUrl($url);
        }

        if ($referer = data_get($context, 'headers.referer')) {
            $censored['headers']['referer'] = $this->censorUrl($referer);
        }

        if (data_get($context, 'request.ip')) {
            $censored['request']['ip'] = null;
        }

        foreach (static::CENSORED_HEADERS as $header) {
            if (array_key_exists($header, data_get($context, 'headers') ?? [])) {
                $censored['headers'][$header] = static::CENSORED;
            }
        }

        if (is_array($user = data_get($context, 'user'))) {
            $censored['user'] = array_map(
                fn (mixed $value) => static::CENSORED,
                array_diff_key($user, array_flip(['id', 'uuid']))
            );
        }

        foreach (data_get($context, 'route.routeParameters') ?? [] as $name => $parameter) {
            if (! is_numeric($parameter)) {
                $censored['route']['routeParameters'][$name] = static::CENSORED;
            }
        }

        $context = array_replace_recursive($context, $censored);
        $context['request_data'] = [
            'queryString' => [],
            'body' => [],
            'files' => [],
        ];

        foreach (static::CENSORED_BRANCHES as $branch) {
            if (array_key_exists($branch, $context)) {
                $context[$branch] = [];
            }
        }

        if (array_key_exists('exception', $context)) {
            $context['exception'] = Arr::only($context['exception'], 'raw_sql');
        }

        if (is_array($components = data_get($context, 'livewire'))) {
            $context['livewire'] = array_map(
                fn (array $component): array => array_map(
                    fn (mixed $value): mixed => is_array($value) ? [] : $value,
                    $component
                ),
                $components
            );
        }

        // The Flare API rejects request_data with a scalar, and an array provided
        // on top of the collected context only adds keys. So the collected context
        // itself has to go, otherwise the real values stay in the report.
        $report->useContext(
            new readonly class($context) implements ContextProvider
            {
                public function __construct(private array $context) {}

                public function toArray(): array
                {
                    return $this->context;
                }
            }
        );
        $report->userProvidedContext($context);

        return $next($report);
    }

    protected function censorQueryException(QueryException $exception): string
    {
        $bindings = array_filter(
            $exception->getBindings(),
            fn (mixed $binding) => is_string($binding) && ! blank($binding)
        );

        return str_replace($bindings, static::CENSORED, $exception->getPrevious()?->getMessage() ?? '')
            . ' (Connection: ' . $exception->getConnectionName() . ', SQL: ' . $exception->getSql() . ')';
    }
}
