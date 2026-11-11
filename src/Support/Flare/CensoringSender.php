<?php

namespace FluxErp\Support\Flare;

use Illuminate\Support\Str;
use Spatie\FlareClient\Senders\Sender;

class CensoringSender implements Sender
{
    use CensorsCommandLines, CensorsUrls;

    public const string CENSORED = '<CENSORED>';

    protected const array CENSORED_NAMES = [
        'cache.key',
        'client.address',
        'context.custom',
        'context.exception',
        'context.laravel',
        'db.query.parameters',
        'db.sql.bindings',
        'dump.html',
        'filesystem.as_path',
        'filesystem.found_paths',
        'filesystem.from_path',
        'filesystem.full_path',
        'filesystem.path',
        'filesystem.paths',
        'filesystem.to_path',
        'filesystem.url',
        'glow.context',
        'http.request.body.contents',
        'http.request.cookies',
        'http.request.files',
        'http.request.session',
        'laravel.job.properties',
        'laravel.job.tags',
        'laravel.route.parameters',
        'laravel.url',
        'livewire.component.call.params',
        'livewire.components',
        'log.context',
        'notification.to',
        'process.command_args',
        'url.fragment',
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

    protected const string REQUEST_SPAN_PREFIX = 'Request - ';

    protected const array URLS = [
        'referer',
        'url.full',
        'url.path',
    ];

    public function __construct(protected Sender $sender) {}

    public function post(string $endpoint, string $apiToken, array $payload, mixed ...$arguments): void
    {
        $this->sender->post($endpoint, $apiToken, $this->censor($payload), ...$arguments);
    }

    protected function censor(array $data): array
    {
        if (is_string(data_get($data, 'key')) && is_array(data_get($data, 'value'))) {
            return $this->censorOpenTelemetryAttribute($data);
        }

        foreach ($data as $name => $value) {
            $data[$name] = $this->censorValue((string) $name, $value);
        }

        return $data;
    }

    protected function censorOpenTelemetryAttribute(array $attribute): array
    {
        $value = $this->censorValue($attribute['key'], data_get($attribute, 'value.stringValue') ?? $attribute['value']);
        $attribute['value'] = is_string($value) ? ['stringValue' => $value] : $value;

        return $attribute;
    }

    protected function censorValue(string $name, mixed $value): mixed
    {
        return match (true) {
            $name === 'arguments' => null,
            in_array($name, static::CENSORED_NAMES, true) => static::CENSORED,
            is_string($value) && $name === 'flare.entry_point.value' => implode(
                ' ',
                $this->censorCommandLine(explode(' ', $this->censorUrl($value)))
            ),
            is_string($value) && in_array($name, static::URLS, true) => $this->censorUrl($value),
            is_string($value)
                && $name === 'name'
                && str_starts_with($value, static::REQUEST_SPAN_PREFIX) => static::REQUEST_SPAN_PREFIX
                . $this->censorUrl(Str::after($value, static::REQUEST_SPAN_PREFIX)),
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
