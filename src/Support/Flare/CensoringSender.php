<?php

namespace FluxErp\Support\Flare;

use Illuminate\Support\Str;
use Spatie\FlareClient\Senders\Sender;

class CensoringSender implements Sender
{
    public const string CENSORED = '<CENSORED>';

    protected const array CENSORED_NAMES = [
        'cache.key',
        'client.address',
        'context.custom',
        'context.exception',
        'context.laravel',
        'db.query.parameters',
        'db.sql.bindings',
        'http.request.body.contents',
        'http.request.cookies',
        'http.request.files',
        'http.request.session',
        'laravel.job.properties',
        'laravel.route.parameters',
        'livewire.component.call.params',
        'livewire.components',
        'log.context',
        'url.query',
        'user.attributes',
        'user.email',
        'user.full_name',
        'view.data',
        'x-forwarded-for',
        'x-real-ip',
    ];

    protected const array MESSAGES = [
        'exception.message',
        'log.message',
        'message',
    ];

    protected const array URLS = [
        'flare.entry_point.value',
        'referer',
        'url.full',
    ];

    public function __construct(protected Sender $sender) {}

    public function post(string $endpoint, string $apiToken, array $payload, mixed ...$arguments): void
    {
        $this->sender->post($endpoint, $apiToken, $this->censor($payload), ...$arguments);
    }

    protected function censor(array $data): array
    {
        if (is_string(data_get($data, 'key')) && is_array(data_get($data, 'value'))) {
            $value = $this->censorValue($data['key'], data_get($data, 'value.stringValue') ?? $data['value']);
            $data['value'] = is_string($value) ? ['stringValue' => $value] : $value;

            return $data;
        }

        foreach ($data as $name => $value) {
            $data[$name] = $this->censorValue((string) $name, $value);
        }

        return $data;
    }

    protected function censorValue(string $name, mixed $value): mixed
    {
        return match (true) {
            $name === 'arguments' => null,
            in_array($name, static::CENSORED_NAMES, true) => static::CENSORED,
            is_string($value) && in_array($name, static::URLS, true) => Str::before($value, '?'),
            is_string($value) && in_array($name, static::MESSAGES, true) => preg_replace(
                '/^(SQLSTATE\[\w+\](?:: [^:]+: \d+)?).*$/s',
                '$1',
                $value
            ),
            is_array($value) => $this->censor($value),
            default => $value,
        };
    }
}
