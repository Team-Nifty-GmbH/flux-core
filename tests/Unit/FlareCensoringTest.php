<?php

use FluxErp\FluxServiceProvider;
use FluxErp\Models\Address;
use FluxErp\Support\Flare\CensoringSender;
use FluxErp\Support\Flare\CensorPersonalData;
use FluxErp\Tests\Fixtures\FlareFailingJob;
use FluxErp\Tests\Fixtures\RecordingFlareSender;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\FlareClient\Api;
use Spatie\FlareClient\Context\ContextProvider;
use Spatie\FlareClient\Flare;
use Spatie\FlareClient\Sampling\AlwaysSampler;
use Spatie\FlareClient\Senders\Sender;
use Spatie\FlareClient\Spans\Span;
use Spatie\LaravelFlare\FlareServiceProvider;
use Spatie\LaravelIgnition\FlareMiddleware\AddQueries;
use function Livewire\invade;

if (! interface_exists(ContextProvider::class)) {
    require_once __DIR__ . '/../Fixtures/FlareContextProvider.php';
}

function flareReport(array $context): object
{
    return new class($context)
    {
        public ?string $message = null;

        public ?Throwable $throwable = null;

        public array $userProvidedContext = [];

        public function __construct(private array $context) {}

        public function allContext(): array
        {
            return array_replace_recursive($this->context, $this->userProvidedContext);
        }

        public function getMessage(): ?string
        {
            return $this->message;
        }

        public function getThrowable(): ?Throwable
        {
            return $this->throwable;
        }

        public function message(string $message): static
        {
            $this->message = $message;

            return $this;
        }

        public function throwable(Throwable $throwable): static
        {
            $this->throwable = $throwable;

            return $this;
        }

        public function useContext(ContextProvider $provider): static
        {
            $this->context = $provider->toArray();

            return $this;
        }

        public function userProvidedContext(array $userProvidedContext): static
        {
            $this->userProvidedContext = $userProvidedContext;

            return $this;
        }
    };
}

function censoredFlareContext(array $context): array
{
    return (new CensorPersonalData())
        ->handle(flareReport($context), fn (object $report) => $report)
        ->allContext();
}

test('flare reports carry no request data, cookies, session or user attributes', function (): void {
    Route::get('contacts', fn () => null);

    $context = censoredFlareContext([
        'request' => ['url' => 'https://flux.test/contacts?search=Mustermann', 'method' => 'POST'],
        'request_data' => [
            'queryString' => ['search' => 'Mustermann'],
            'body' => ['components' => [['snapshot' => '{"street":"Musterweg 1"}']]],
            'files' => ['avatar' => ['name' => 'max-mustermann.jpg']],
        ],
        'headers' => ['user-agent' => 'test', 'referer' => 'https://flux.test/contacts?search=Mustermann'],
        'cookies' => ['flux_session' => 'secret'],
        'session' => ['_token' => 'secret'],
        'user' => ['id' => 7, 'name' => 'Max Mustermann', 'email' => 'max@example.com'],
        'route' => [
            'route' => 'contacts.id',
            'routeParameters' => [
                'id' => 5,
                'contact' => ['name' => 'Max'],
                'address' => (new Address())->forceFill(['firstname' => 'Max', 'lastname' => 'Mustermann']),
            ],
        ],
    ]);

    expect(data_get($context, 'request.url'))->toBe('https://flux.test/contacts')
        ->and(data_get($context, 'request.method'))->toBe('POST')
        ->and(data_get($context, 'request_data'))->toBe(['queryString' => [], 'body' => [], 'files' => []])
        ->and(data_get($context, 'headers.referer'))->toBe('https://flux.test/contacts')
        ->and(data_get($context, 'headers.user-agent'))->toBe('test')
        ->and(data_get($context, 'cookies'))->toBe(CensorPersonalData::CENSORED)
        ->and(data_get($context, 'session'))->toBe(CensorPersonalData::CENSORED)
        ->and(data_get($context, 'user'))->toBe([
            'id' => 7,
            'name' => CensorPersonalData::CENSORED,
            'email' => CensorPersonalData::CENSORED,
        ])
        ->and(data_get($context, 'route.routeParameters'))->toBe([
            'id' => 5,
            'contact' => CensorPersonalData::CENSORED,
            'address' => CensorPersonalData::CENSORED,
        ])
        ->and(json_encode($context))->not->toContain('Mustermann')
        ->and(json_encode($context))->not->toContain('Musterweg');
});

test('flare reports keep the request data as arrays and drop every collected value', function (): void {
    $context = censoredFlareContext([
        'request' => ['url' => 'https://flux.test/livewire/update?max@example.com', 'method' => 'POST'],
        'request_data' => [
            'queryString' => ['max@example.com' => ''],
            'body' => ['components' => [['snapshot' => '{"street":"Musterweg 1"}']], 'lastname' => 'Mustermann'],
            'files' => ['avatar' => ['pathname' => '/tmp/max-mustermann.jpg']],
        ],
        'cookies' => ['flux_session' => 'cookiesecret'],
        'session' => ['_token' => 'sessionsecret'],
        'livewire' => [
            [
                'component_class' => 'App\\Livewire\\Contact',
                'data' => ['street' => 'Musterweg 1'],
                'memo' => ['path' => 'contacts/Mustermann'],
                'updates' => ['form.email' => 'max@example.com'],
                'calls' => [['method' => 'save', 'params' => ['Mustermann']]],
            ],
        ],
    ]);

    expect(data_get($context, 'request_data.queryString'))->toBeArray()
        ->and(data_get($context, 'request_data.body'))->toBeArray()
        ->and(data_get($context, 'request_data.files'))->toBeArray()
        ->and(data_get($context, 'livewire'))->toBe([
            [
                'component_class' => 'App\\Livewire\\Contact',
                'data' => [],
                'memo' => [],
                'updates' => [],
                'calls' => [],
            ],
        ])
        ->and(json_encode($context))->not->toContain('max@example.com')
        ->and(json_encode($context))->not->toContain('Mustermann')
        ->and(json_encode($context))->not->toContain('mustermann')
        ->and(json_encode($context))->not->toContain('Musterweg')
        ->and(json_encode($context))->not->toContain('cookiesecret')
        ->and(json_encode($context))->not->toContain('sessionsecret');
});

test('flare reports carry no credentials, client ips, dynamic urls or recorded values', function (): void {
    Route::get('flare-contacts/{contact}', fn () => null);

    $context = censoredFlareContext([
        'request' => [
            'url' => 'https://flux.test/flare-contacts/Mustermann?search=Mustermann',
            'ip' => '203.0.113.7',
            'method' => 'GET',
        ],
        'request_data' => ['queryString' => [], 'body' => [], 'files' => []],
        'headers' => [
            'user-agent' => 'test',
            'referer' => 'https://flux.test/flare-contacts/Mustermann',
            'authorization' => 'Bearer tokensecret',
            'cookie' => 'flux_session=cookiesecret',
            'x-csrf-token' => 'csrfsecret',
            'x-forwarded-for' => '203.0.113.8',
            'x-real-ip' => '203.0.113.9',
        ],
        'route' => ['routeParameters' => ['id' => 5, 'contact' => 'Mustermann']],
        'logs' => [['message' => 'Saved max@example.com', 'context' => ['street' => 'Musterweg 1']]],
        'dumps' => [['html_dump' => 'Mustermann']],
        'laravel_context' => ['email' => 'max@example.com'],
        'exception' => ['raw_sql' => 'select * from addresses', 'email' => 'max@example.com'],
    ]);

    expect(data_get($context, 'request.url'))->toBe('https://flux.test/flare-contacts/{contact}')
        ->and(data_get($context, 'request.ip'))->toBeNull()
        ->and(data_get($context, 'headers.referer'))->toBe('https://flux.test/flare-contacts/{contact}')
        ->and(data_get($context, 'headers.user-agent'))->toBe('test')
        ->and(data_get($context, 'headers.authorization'))->toBe(CensorPersonalData::CENSORED)
        ->and(data_get($context, 'route.routeParameters'))->toBe(['id' => 5, 'contact' => CensorPersonalData::CENSORED])
        ->and(data_get($context, 'logs'))->toBe([])
        ->and(data_get($context, 'dumps'))->toBe([])
        ->and(data_get($context, 'laravel_context'))->toBe([])
        ->and(data_get($context, 'exception'))->toBe(['raw_sql' => 'select * from addresses'])
        ->and(json_encode($context))->not->toContain('Mustermann')
        ->and(json_encode($context))->not->toContain('max@example.com')
        ->and(json_encode($context))->not->toContain('Musterweg')
        ->and(json_encode($context))->not->toContain('tokensecret')
        ->and(json_encode($context))->not->toContain('cookiesecret')
        ->and(json_encode($context))->not->toContain('csrfsecret')
        ->and(json_encode($context))->not->toContain('203.0.113.7')
        ->and(json_encode($context))->not->toContain('203.0.113.8')
        ->and(json_encode($context))->not->toContain('203.0.113.9');
});

test('flare console reports carry no command arguments or recorded values', function (): void {
    $context = censoredFlareContext([
        'arguments' => ['artisan', 'flux:sync', 'max@example.com', '--name=Mustermann'],
        'env' => ['php_version' => PHP_VERSION],
        'git' => ['hash' => 'abc123'],
        'queries' => [['sql' => 'select * from addresses', 'bindings' => null]],
        'logs' => [['message' => 'Saved max@example.com', 'context' => ['street' => 'Musterweg 1']]],
        'dumps' => [['html_dump' => 'Mustermann']],
        'laravel_context' => ['email' => 'max@example.com'],
        'context' => ['street' => 'Musterweg 1'],
        'exception' => ['raw_sql' => 'select * from addresses', 'email' => 'max@example.com'],
        'view' => ['view' => 'mails.order', 'data' => ['street' => 'Musterweg 1']],
    ]);

    expect(data_get($context, 'arguments'))->toBe(['artisan', 'flux:sync'])
        ->and(data_get($context, 'env.php_version'))->toBe(PHP_VERSION)
        ->and(data_get($context, 'git.hash'))->toBe('abc123')
        ->and(data_get($context, 'queries.0.sql'))->toBe('select * from addresses')
        ->and(data_get($context, 'logs'))->toBe([])
        ->and(data_get($context, 'dumps'))->toBe([])
        ->and(data_get($context, 'laravel_context'))->toBe([])
        ->and(data_get($context, 'context'))->toBe([])
        ->and(data_get($context, 'exception'))->toBe(['raw_sql' => 'select * from addresses'])
        ->and(data_get($context, 'view'))->toBe(['view' => 'mails.order', 'data' => []])
        ->and($context)->not->toHaveKey('request_data')
        ->and($context)->not->toHaveKey('cookies')
        ->and($context)->not->toHaveKey('session')
        ->and(json_encode($context))->not->toContain('max@example.com')
        ->and(json_encode($context))->not->toContain('Mustermann')
        ->and(json_encode($context))->not->toContain('Musterweg');
});

test('flare console reports with an option before the command keep only the script', function (): void {
    expect(data_get(censoredFlareContext(['arguments' => ['artisan', '--env', 'max@example.com', 'flux:sync']]), 'arguments'))
        ->toBe(['artisan']);
});

test('flare console reports of other scripts keep only the script', function (): void {
    $context = censoredFlareContext(['arguments' => ['bin/import', 'max@example.com', '--name=Mustermann']]);

    expect(data_get($context, 'arguments'))->toBe(['bin/import']);
});

test('flare queue reports keep the job metadata and drop its payload', function (): void {
    $context = censoredFlareContext([
        'arguments' => ['artisan', 'queue:work'],
        'job' => [
            'uuid' => 'c1f0c9b4',
            'maxTries' => 3,
            'timeout' => 60,
            'tags' => ['contact:max@example.com'],
            'illuminate:log:context' => ['data' => ['email' => 'max@example.com']],
            'data' => [
                'email' => 'max@example.com',
                'address' => ['street' => 'Musterweg 1'],
                'chained' => [['name' => 'App\\Jobs\\Notify', 'data' => ['lastname' => 'Mustermann']]],
            ],
            'pushedAt' => '2026-11-04T10:00:00+00:00',
            'name' => 'App\\Jobs\\SyncContacts',
            'connection' => 'redis',
            'queue' => 'default',
        ],
    ]);

    expect(data_get($context, 'job'))->toBe([
        'uuid' => 'c1f0c9b4',
        'maxTries' => 3,
        'timeout' => 60,
        'pushedAt' => '2026-11-04T10:00:00+00:00',
        'name' => 'App\\Jobs\\SyncContacts',
        'connection' => 'redis',
        'queue' => 'default',
    ])
        ->and(json_encode($context))->not->toContain('max@example.com')
        ->and(json_encode($context))->not->toContain('Mustermann')
        ->and(json_encode($context))->not->toContain('Musterweg');
});

test('flare reports carry no query bindings in the message of a query exception', function (): void {
    $exception = new QueryException(
        'mysql',
        'insert into addresses (email) values (?)',
        ['max@example.com'],
        new PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'max@example.com'")
    );

    $report = flareReport([])
        ->throwable($exception)
        ->message($exception->getMessage());

    $message = (new CensorPersonalData())->handle($report, fn (object $report) => $report)->getMessage();

    expect($message)->not->toContain('max@example.com')
        ->and($message)->toContain('insert into addresses (email) values (?)')
        ->and($message)->toContain('Duplicate entry');
});

test('flare config drops query bindings and stack frame arguments', function (): void {
    config([
        'flare.flare_middleware' => [
            AddQueries::class => ['maximum_number_of_collected_queries' => 200, 'report_query_bindings' => true],
        ],
        'ignition.with_stack_frame_arguments' => true,
    ]);

    invade(new FluxServiceProvider($this->app))->censorFlareReports();

    expect(config('flare.flare_middleware.' . AddQueries::class . '.report_query_bindings'))->toBeFalse()
        ->and(config('flare.flare_middleware.' . CensorPersonalData::class))->toBe([])
        ->and(config('ignition.with_stack_frame_arguments'))->toBeFalse();
});

test('flare config stays untouched without laravel-ignition', function (): void {
    config(['flare' => null, 'ignition' => null]);

    invade(new FluxServiceProvider($this->app))->censorFlareReports();

    expect(config('flare'))->toBeNull()->and(config('ignition'))->toBeNull();
});

function sendThroughFlareSender(array $payload): array
{
    $recorder = new class() implements Sender
    {
        public array $payload = [];

        public function post(string $endpoint, string $apiToken, array $payload, mixed $type, bool $test, Closure $callback): void
        {
            $this->payload = $payload;
        }
    };

    app()->singleton(Sender::class, fn () => $recorder);

    app(Sender::class)->post('https://flare.test', 'token', $payload, 'errors', false, fn () => null);

    return $recorder->payload;
}

test('laravel-flare reports leave without request data, user attributes or query bindings', function (): void {
    Route::get('contacts', fn () => null);

    $payload = sendThroughFlareSender([
        'exceptionClass' => RuntimeException::class,
        'message' => 'Something broke',
        'stacktrace' => [['file' => 'a.php', 'arguments' => [['value' => 'max@example.com']]]],
        'attributes' => [
            'url.full' => 'https://flux.test/contacts?search=Mustermann',
            'url.query' => 'search=Mustermann',
            'flare.entry_point.value' => 'https://flux.test/contacts?search=Mustermann',
            'client.address' => '203.0.113.7',
            'http.request.body.contents' => ['components' => [['snapshot' => '{"email":"max@example.com"}']]],
            'http.request.files' => ['avatar' => 'max-mustermann.jpg'],
            'http.request.cookies' => ['flux_session' => 'secret'],
            'http.request.session' => ['password_hash_web' => 'secret'],
            'http.request.headers' => [
                'user-agent' => 'test',
                'referer' => 'https://flux.test/contacts?search=Mustermann',
                'x-forwarded-for' => '203.0.113.7',
                'x-real-ip' => '203.0.113.7',
            ],
            'user.id' => 7,
            'user.email' => 'max@example.com',
            'user.full_name' => 'Max Mustermann',
            'user.attributes' => ['iban' => 'DE00'],
            'livewire.components' => [['data' => ['street' => 'Musterweg 1']]],
            'view.data' => ['name' => 'Max'],
            'laravel.route.parameters' => ['contact' => ['name' => 'Max']],
            'context.laravel' => ['user' => 'user:7'],
            'context.custom' => ['email' => 'max@example.com'],
            'context.exception' => ['email' => 'max@example.com'],
        ],
        'events' => [
            ['type' => 'php_query', 'attributes' => ['db.statement' => 'select ?', 'db.sql.bindings' => ['max@example.com']]],
            ['type' => 'php_cache', 'attributes' => ['cache.key' => 'session-id', 'cache.store' => 'redis']],
            ['type' => 'php_log', 'attributes' => ['log.message' => 'Sync failed', 'log.context' => ['email' => 'max@example.com']]],
            ['type' => 'php_job', 'attributes' => ['laravel.job.properties' => ['email' => 'max@example.com']]],
        ],
    ]);

    expect($payload['message'])->toBe('Something broke')
        ->and(data_get($payload, 'stacktrace.0.file'))->toBe('a.php')
        ->and(data_get($payload, 'stacktrace.0.arguments'))->toBeNull()
        ->and(data_get($payload, 'attributes.url\.full'))->toBeNull();

    $attributes = $payload['attributes'];

    expect($attributes['url.full'])->toBe('https://flux.test/contacts')
        ->and($attributes['flare.entry_point.value'])->toBe('https://flux.test/contacts')
        ->and($attributes['user.id'])->toBe(7)
        ->and($attributes['http.request.headers']['user-agent'])->toBe('test')
        ->and($attributes['http.request.headers']['referer'])->toBe('https://flux.test/contacts')
        ->and($payload['events'][0]['attributes']['db.statement'])->toBe('select ?')
        ->and($payload['events'][1]['attributes']['cache.store'])->toBe('redis')
        ->and($payload['events'][2]['attributes']['log.message'])->toBe('Sync failed');

    foreach ([
        'url.query', 'client.address', 'http.request.body.contents', 'http.request.files', 'http.request.cookies',
        'http.request.session', 'user.email', 'user.full_name', 'user.attributes', 'livewire.components', 'view.data',
        'laravel.route.parameters', 'context.laravel', 'context.custom', 'context.exception',
    ] as $name) {
        expect($attributes[$name])->toBe(CensoringSender::CENSORED);
    }

    expect($attributes['http.request.headers']['x-forwarded-for'])->toBe(CensoringSender::CENSORED)
        ->and($attributes['http.request.headers']['x-real-ip'])->toBe(CensoringSender::CENSORED)
        ->and($payload['events'][0]['attributes']['db.sql.bindings'])->toBe(CensoringSender::CENSORED)
        ->and($payload['events'][1]['attributes']['cache.key'])->toBe(CensoringSender::CENSORED)
        ->and($payload['events'][2]['attributes']['log.context'])->toBe(CensoringSender::CENSORED)
        ->and($payload['events'][3]['attributes']['laravel.job.properties'])->toBe(CensoringSender::CENSORED)
        ->and(json_encode($payload))->not->toContain('max@example.com')
        ->and(json_encode($payload))->not->toContain('Mustermann')
        ->and(json_encode($payload))->not->toContain('secret')
        ->and(json_encode($payload))->not->toContain('203.0.113.7')
        ->and(json_encode($payload))->not->toContain('DE00');
});

test('laravel-flare urls carry the route pattern instead of the dynamic path segments', function (): void {
    Route::get('flare-contacts/{contact}/orders/{order?}', fn () => null);
    Route::post('flare-media/{medium}', fn () => null);

    $payload = sendThroughFlareSender([
        'attributes' => [
            'url.full' => 'https://flux.test:8443/flare-contacts/max@example.com/orders/7?search=Mustermann',
            'url.path' => '/flare-contacts/max@example.com/orders/7',
            'flare.entry_point.value' => 'https://flux.test/flare-media/max@example.com',
            'http.request.headers' => ['referer' => 'https://flux.test/flare-contacts/max@example.com/orders'],
        ],
        'events' => [
            ['attributes' => ['url.full' => 'https://flux.test/unknown/max@example.com', 'url.path' => '/unknown/max@example.com']],
            ['attributes' => ['http.request.headers' => ['referer' => 'http://[max@example.com']]],
            ['attributes' => ['flare.entry_point.value' => 'App\\Jobs\\SyncContacts']],
            ['attributes' => ['flare.entry_point.value' => 'artisan flux:sync']],
        ],
        'resourceSpans' => [[
            'scopeSpans' => [[
                'spans' => [[
                    'name' => 'Request - https://flux.test/flare-contacts/max@example.com/orders',
                    'attributes' => [
                        ['key' => 'url.path', 'value' => ['stringValue' => '/flare-contacts/max@example.com/orders']],
                    ],
                ], [
                    'name' => 'Http Request - api.example.com',
                ]],
            ]],
        ]],
    ]);

    $spans = data_get($payload, 'resourceSpans.0.scopeSpans.0.spans');

    expect($payload['attributes']['url.full'])->toBe('https://flux.test:8443/flare-contacts/{contact}/orders/{order?}')
        ->and($payload['attributes']['url.path'])->toBe('/flare-contacts/{contact}/orders/{order?}')
        ->and($payload['attributes']['flare.entry_point.value'])->toBe('https://flux.test/flare-media/{medium}')
        ->and($payload['attributes']['http.request.headers']['referer'])->toBe('https://flux.test/flare-contacts/{contact}/orders/{order?}')
        ->and($payload['events'][0]['attributes']['url.full'])->toBe('https://flux.test/' . CensoringSender::CENSORED)
        ->and($payload['events'][0]['attributes']['url.path'])->toBe('/' . CensoringSender::CENSORED)
        ->and($payload['events'][1]['attributes']['http.request.headers']['referer'])->toBe(CensoringSender::CENSORED)
        ->and($payload['events'][2]['attributes']['flare.entry_point.value'])->toBe('App\\Jobs\\SyncContacts')
        ->and($payload['events'][3]['attributes']['flare.entry_point.value'])->toBe('artisan flux:sync')
        ->and($spans[0]['name'])->toBe('Request - https://flux.test/flare-contacts/{contact}/orders/{order?}')
        ->and(data_get($spans[0], 'attributes.0.value.stringValue'))->toBe('/flare-contacts/{contact}/orders/{order?}')
        ->and($spans[1]['name'])->toBe('Http Request - api.example.com')
        ->and(json_encode($payload))->not->toContain('max@example.com')
        ->and(json_encode($payload))->not->toContain('Mustermann');
});

test('laravel-flare reports keep only the error class of a failed query', function (): void {
    $payload = sendThroughFlareSender([
        'exceptionClass' => QueryException::class,
        'message' => "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'max@example.com' for key (Connection: mysql, SQL: insert into addresses (email) values (max@example.com))",
        'previous' => [
            ['exceptionClass' => PDOException::class, 'message' => "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'max@example.com' for key"],
        ],
        'attributes' => [],
    ]);

    expect($payload['message'])->toBe('SQLSTATE[23000]: Integrity constraint violation: 1062')
        ->and($payload['previous'][0]['message'])->toBe('SQLSTATE[23000]: Integrity constraint violation: 1062');
});

test('laravel-flare traces and logs leave without personal data in the OpenTelemetry format', function (): void {
    Route::get('contacts', fn () => null);

    $payload = sendThroughFlareSender([
        'resourceSpans' => [[
            'resource' => ['attributes' => [['key' => 'service.name', 'value' => ['stringValue' => 'Flux']]]],
            'scopeSpans' => [[
                'spans' => [[
                    'attributes' => [
                        ['key' => 'url.full', 'value' => ['stringValue' => 'https://flux.test/contacts?search=Mustermann']],
                        ['key' => 'user.id', 'value' => ['intValue' => 7]],
                        ['key' => 'user.email', 'value' => ['stringValue' => 'max@example.com']],
                        ['key' => 'db.sql.bindings', 'value' => ['arrayValue' => ['values' => [['stringValue' => 'max@example.com']]]]],
                        ['key' => 'http.request.body.contents', 'value' => ['kvlistValue' => ['values' => [
                            ['key' => 'email', 'value' => ['stringValue' => 'max@example.com']],
                        ]]]],
                        ['key' => 'http.request.headers', 'value' => ['kvlistValue' => ['values' => [
                            ['key' => 'user-agent', 'value' => ['stringValue' => 'test']],
                            ['key' => 'referer', 'value' => ['stringValue' => 'https://flux.test/contacts?search=Mustermann']],
                            ['key' => 'x-forwarded-for', 'value' => ['stringValue' => '203.0.113.7']],
                        ]]]],
                    ],
                    'events' => [[
                        'attributes' => [
                            ['key' => 'exception.message', 'value' => ['stringValue' => "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'max@example.com'"]],
                        ],
                    ]],
                ]],
            ]],
        ]],
    ]);

    $span = data_get($payload, 'resourceSpans.0.scopeSpans.0.spans.0');
    $attributes = collect($span['attributes'])->pluck('value', 'key');
    $headers = collect(data_get($attributes['http.request.headers'], 'kvlistValue.values'))->pluck('value', 'key');

    expect(data_get($payload, 'resourceSpans.0.resource.attributes.0.value.stringValue'))->toBe('Flux')
        ->and($attributes['url.full'])->toBe(['stringValue' => 'https://flux.test/contacts'])
        ->and($attributes['user.id'])->toBe(['intValue' => 7])
        ->and($attributes['user.email'])->toBe(['stringValue' => CensoringSender::CENSORED])
        ->and($attributes['db.sql.bindings'])->toBe(['stringValue' => CensoringSender::CENSORED])
        ->and($attributes['http.request.body.contents'])->toBe(['stringValue' => CensoringSender::CENSORED])
        ->and($headers['user-agent'])->toBe(['stringValue' => 'test'])
        ->and($headers['referer'])->toBe(['stringValue' => 'https://flux.test/contacts'])
        ->and($headers['x-forwarded-for'])->toBe(['stringValue' => CensoringSender::CENSORED])
        ->and(data_get($span, 'events.0.attributes.0.value.stringValue'))->toBe('SQLSTATE[23000]: Integrity constraint violation: 1062')
        ->and(json_encode($payload))->not->toContain('max@example.com')
        ->and(json_encode($payload))->not->toContain('Mustermann')
        ->and(json_encode($payload))->not->toContain('203.0.113.7');
});

test('the real laravel-flare client hands reports, traces and logs to the sender censored', function (): void {
    config([
        'flare.key' => 'test-key',
        'flare.sender' => ['class' => RecordingFlareSender::class, 'config' => []],
    ]);
    RecordingFlareSender::$payloads = [];

    $_ENV['FLARE_FAKE_WEB_REQUEST'] = 'true';
    $this->app->instance('config_loaded_from_cache', false);
    $this->app->register(new FlareServiceProvider($this->app));

    Route::post('flare-contacts/{contact}', function (): void {
        invade(app())->isRunningInConsole = false;
        session(['street' => 'Musterweg 1']);
        DB::select('select ? as email', ['max@example.com']);

        app(Flare::class)->report(new RuntimeException('Something broke'));
        app(Api::class)->trace([
            new Span('trace', 'span', null, 'Request - ' . request()->fullUrl(), 1, 2, [
                'url.full' => request()->fullUrl(),
                'user.email' => 'max@example.com',
                'db.sql.bindings' => ['max@example.com'],
                'dump.html' => '<pre>max@example.com</pre>',
                'filesystem.path' => 'media/Mustermann.pdf',
                'filesystem.url' => 'https://flux.test/media/Mustermann.pdf',
                'glow.context' => ['email' => 'max@example.com'],
                'laravel.url' => 'https://flux.test/media/Mustermann.pdf',
                'notification.to' => 'max@example.com',
                'url.fragment' => 'Mustermann',
            ]),
        ]);
        app(Flare::class)->log()->record('Saved', context: ['email' => 'max@example.com']);
        app(Flare::class)->log()->flush();
        app(Api::class)->sendQueue();
    })->middleware('web');

    $this->actingAs($this->user)
        ->withUnencryptedCookie('private', 'Mustermann')
        ->post('flare-contacts/max@example.com?search=Mustermann', ['street' => 'Musterweg 1'])
        ->assertOk();

    unset($_ENV['FLARE_FAKE_WEB_REQUEST']);

    $payloads = RecordingFlareSender::$payloads;

    expect($payloads)->toHaveCount(3);

    $attributes = $payloads[0]['attributes'];

    expect($attributes['http.request.body.contents'])->toBe(CensoringSender::CENSORED)
        ->and($attributes['http.request.cookies'])->toBe(CensoringSender::CENSORED)
        ->and($attributes['http.request.session'])->toBe(CensoringSender::CENSORED)
        ->and($attributes['url.query'])->toBe(CensoringSender::CENSORED)
        ->and($attributes['url.full'])->toEndWith('/flare-contacts/{contact}')
        ->and($attributes['user.id'])->toBe($this->user->getKey())
        ->and($attributes['user.email'])->toBe(CensoringSender::CENSORED)
        ->and(collect($payloads[0]['stacktrace'])->pluck('arguments')->filter())->toBeEmpty();

    data_forget($payloads, '0.stacktrace');
    $sent = json_encode($payloads);

    expect($sent)->not->toContain('max@example.com')
        ->and($sent)->not->toContain($this->user->email)
        ->and($sent)->not->toContain('Mustermann')
        ->and($sent)->not->toContain('Musterweg');
});

test('laravel-flare command lines leave without arguments', function (): void {
    $payload = sendThroughFlareSender([
        'attributes' => [
            'flare.entry_point.value' => 'artisan flux:sync max@example.com --name=Mustermann',
            'process.command_args' => ['artisan', 'flux:sync', 'max@example.com', '--name=Mustermann'],
        ],
        'events' => [
            ['attributes' => ['flare.entry_point.value' => 'bin/import max@example.com']],
            ['attributes' => ['flare.entry_point.value' => 'artisan --env max@example.com flux:sync']],
            ['attributes' => ['laravel.job.tags' => ['contact:max@example.com']]],
            ['attributes' => ['laravel.job.chain.jobs' => [['laravel.job.properties' => ['email' => 'max@example.com']]]]],
        ],
        'resourceSpans' => [[
            'resource' => ['attributes' => [
                ['key' => 'process.command_args', 'value' => ['arrayValue' => ['values' => [['stringValue' => 'max@example.com']]]]],
            ]],
            'scopeSpans' => [[
                'spans' => [[
                    'attributes' => [
                        ['key' => 'flare.entry_point.value', 'value' => ['stringValue' => 'artisan flux:sync max@example.com']],
                    ],
                ]],
            ]],
        ]],
    ]);

    $spanAttribute = data_get($payload, 'resourceSpans.0.scopeSpans.0.spans.0.attributes.0.value');

    expect($payload['attributes']['flare.entry_point.value'])->toBe('artisan flux:sync')
        ->and($payload['attributes']['process.command_args'])->toBe(CensoringSender::CENSORED)
        ->and($payload['events'][0]['attributes']['flare.entry_point.value'])->toBe('bin/import')
        ->and($payload['events'][1]['attributes']['flare.entry_point.value'])->toBe('artisan')
        ->and($payload['events'][2]['attributes']['laravel.job.tags'])->toBe(CensoringSender::CENSORED)
        ->and($spanAttribute)->toBe(['stringValue' => 'artisan flux:sync'])
        ->and(json_encode($payload))->not->toContain('max@example.com')
        ->and(json_encode($payload))->not->toContain('Mustermann');
});

function realFlareClientInConsole(array $argv): void
{
    config([
        'flare.key' => 'test-key',
        'flare.sender' => ['class' => RecordingFlareSender::class, 'config' => []],
        'flare.sampler' => ['class' => AlwaysSampler::class, 'config' => []],
        'queue.default' => 'sync',
    ]);
    RecordingFlareSender::$payloads = [];

    $_SERVER['argv'] = $argv;

    app()->instance('config_loaded_from_cache', false);
    app()->register(new FlareServiceProvider(app()));
}

test('the real laravel-flare client hands console reports to the sender censored', function (): void {
    $argv = $_SERVER['argv'];
    realFlareClientInConsole(['artisan', 'flare-test:sync', 'max@example.com', '--name=Mustermann']);

    Artisan::command('flare-test:sync {email} {--name=}', function (): void {
        app(Flare::class)->report(new RuntimeException('Command broke'));
        app(Flare::class)->log()->record('Saved', context: ['email' => $this->argument('email')]);
        app(Flare::class)->log()->flush();
        app(Api::class)->sendQueue();
    });

    Artisan::call('flare-test:sync', ['email' => 'max@example.com', '--name' => 'Mustermann']);

    $_SERVER['argv'] = $argv;

    $payloads = RecordingFlareSender::$payloads;

    expect($payloads)->toHaveCount(2)
        ->and($payloads[0]['message'])->toBe('Command broke')
        ->and($payloads[0]['attributes']['process.command_args'])->toBe(CensoringSender::CENSORED)
        ->and($payloads[0]['attributes']['flare.entry_point.value'])->toBe('artisan flare-test:sync');

    data_forget($payloads, '0.stacktrace');
    $sent = json_encode($payloads);

    expect($sent)->not->toContain('max@example.com')
        ->and($sent)->not->toContain('Mustermann');
});

test('the real laravel-flare client hands queue reports to the sender censored', function (): void {
    $argv = $_SERVER['argv'];
    realFlareClientInConsole(['artisan', 'queue:work', 'redis', '--name=Mustermann']);

    try {
        dispatch(new FlareFailingJob('max@example.com'));
    } catch (RuntimeException $exception) {
        app(Flare::class)->report($exception);
    }

    app(Api::class)->sendQueue();

    $_SERVER['argv'] = $argv;

    $payloads = RecordingFlareSender::$payloads;
    $reportIndex = collect($payloads)->search(fn (array $payload): bool => data_get($payload, 'message') === 'Job broke');
    $report = $payloads[$reportIndex];
    $jobSpan = $report['events'][0];

    expect($report['attributes']['process.command_args'])->toBe(CensoringSender::CENSORED)
        ->and($report['attributes']['flare.entry_point.value'])->toBe(FlareFailingJob::class)
        ->and($jobSpan['attributes']['laravel.job.class'])->toBe(FlareFailingJob::class)
        ->and($jobSpan['attributes']['laravel.job.properties'])->toBe(CensoringSender::CENSORED);

    data_forget($payloads, $reportIndex . '.stacktrace');
    $sent = json_encode($payloads);

    expect($sent)->not->toContain('max@example.com')
        ->and($sent)->not->toContain('Mustermann');
});
