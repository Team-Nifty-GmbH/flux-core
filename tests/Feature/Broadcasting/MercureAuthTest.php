<?php

use FluxErp\Support\Broadcasting\FluxMercureBroadcaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Broadcast;
use Symfony\Component\HttpFoundation\Cookie;

beforeEach(function (): void {
    config([
        'broadcasting.default' => 'mercure',
        'broadcasting.connections.mercure' => [
            'driver' => 'mercure',
            'url' => 'http://localhost/.well-known/mercure',
            'public_url' => 'http://localhost/.well-known/mercure',
            'secret' => str_repeat('s', 32),
            'cookie_name' => 'mercureAuthorization',
        ],
    ]);
    Broadcast::purge('mercure');

    $this->allowed = ['first' => true, 'second' => true];
    Broadcast::channel('first', fn (): bool => $this->allowed['first']);
    Broadcast::channel('second', fn (): bool => $this->allowed['second']);
    Broadcast::channel('order.{id}', fn (): bool => true);

    $this->session = new Store('test', new ArraySessionHandler(120));
});

function authorizeMercure(object $test, array $channels): JsonResponse
{
    $request = Request::create('/broadcasting/auth', 'POST', ['channel_names' => $channels]);
    $request->setLaravelSession($test->session);
    $request->setUserResolver(fn () => $test->user);

    return Broadcast::connection('mercure')->auth($request);
}

function mercureTopics(JsonResponse $response): array
{
    $token = Arr::first(
        $response->headers->getCookies(),
        fn (Cookie $cookie): bool => $cookie->getName() === 'mercureAuthorization'
    )->getValue();
    $payload = json_decode(base64_decode(strtr(explode('.', $token)[1], '-_', '+/')), true);
    $topics = [];
    array_walk_recursive(
        $payload,
        function (mixed $value) use (&$topics): void {
            if (is_string($value) && str_contains($value, '/channel/')) {
                $topics[] = $value;
            }
        }
    );

    return $topics;
}

test('the mercure connection uses the flux broadcaster', function (): void {
    expect(Broadcast::connection('mercure'))->toBeInstanceOf(FluxMercureBroadcaster::class);
});

test('a later auth request of the same session keeps the channels of the earlier one', function (): void {
    authorizeMercure($this, ['private-first']);
    $second = authorizeMercure($this, ['private-second']);

    expect(mercureTopics($second))->toContain('https://laravel.alt/echo/channel/private-first')
        ->toContain('https://laravel.alt/echo/channel/private-second')
        ->and($second->getData(true)['channel_names'])->toBe([['name' => 'private-second']]);
});

test('a channel the user may no longer access is dropped', function (): void {
    authorizeMercure($this, ['private-first']);
    $this->allowed['first'] = false;
    $second = authorizeMercure($this, ['private-second']);

    expect(mercureTopics($second))->not->toContain('https://laravel.alt/echo/channel/private-first')
        ->toContain('https://laravel.alt/echo/channel/private-second');
});

test('channels of the same kind share one url pattern', function (): void {
    $response = authorizeMercure($this, ['private-order.1', 'private-order.2', 'private-first']);

    expect(mercureTopics($response))
        ->toContain('https://laravel.alt/echo/channel/private-order.:id(1|2)')
        ->toContain('https://laravel.alt/echo/channel/private-first')
        ->not->toContain('https://laravel.alt/echo/channel/private-order.1');
});

test('more than 100 requested channels are still refused', function (): void {
    authorizeMercure($this, array_map(fn (int $id): string => 'private-order.' . $id, range(1, 101)));
})->throws(Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class);

test('client events are off unless configured', function (): void {
    expect(authorizeMercure($this, ['private-first'])->getData(true)['client_events'])->toBeFalse();
});
