<?php

use FluxErp\FluxServiceProvider;
use FluxErp\Models\Address;
use FluxErp\Support\Flare\CensorPersonalData;
use Illuminate\Database\QueryException;
use Spatie\FlareClient\Context\ContextProvider;
use Spatie\FlareClient\Report;
use Spatie\LaravelIgnition\FlareMiddleware\AddQueries;
use function Livewire\invade;

function flareReport(array $context): Report
{
    return (new Report())->useContext(new readonly class($context) implements ContextProvider
    {
        public function __construct(private array $context) {}

        public function toArray(): array
        {
            return $this->context;
        }
    });
}

function censoredFlareContext(array $context): array
{
    return (new CensorPersonalData())
        ->handle(flareReport($context), fn (Report $report) => $report)
        ->allContext();
}

test('flare reports carry no request data, cookies, session or user attributes', function (): void {
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
        ->and(data_get($context, 'request_data.queryString'))->toBe(CensorPersonalData::CENSORED)
        ->and(data_get($context, 'request_data.body'))->toBe(CensorPersonalData::CENSORED)
        ->and(data_get($context, 'request_data.files'))->toBe(CensorPersonalData::CENSORED)
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

test('flare reports without a request stay untouched', function (): void {
    $context = ['env' => ['php_version' => PHP_VERSION], 'user' => ['id' => 7, 'name' => 'Max Mustermann']];

    expect(censoredFlareContext($context))->toBe($context);
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

    $message = (new CensorPersonalData())->handle($report, fn (Report $report) => $report)->getMessage();

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
