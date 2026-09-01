<?php

use FluxErp\Http\Middleware\SanitizeSocketId;
use Illuminate\Http\Request;

test('removes the literal "undefined" socket id header', function (): void {
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-Socket-ID', 'undefined');

    app(SanitizeSocketId::class)->handle($request, function (Request $request): void {
        expect($request->headers->has('X-Socket-ID'))->toBeFalse();
    });
});

test('removes any malformed socket id header', function (string $value): void {
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-Socket-ID', $value);

    app(SanitizeSocketId::class)->handle($request, function (Request $request): void {
        expect($request->headers->has('X-Socket-ID'))->toBeFalse();
    });
})->with(['undefined', 'null', '', 'abc', '123', '123.']);

test('keeps a valid socket id header', function (): void {
    $request = Request::create('/test', 'GET');
    $request->headers->set('X-Socket-ID', '12345.67890');

    app(SanitizeSocketId::class)->handle($request, function (Request $request): void {
        expect($request->headers->get('X-Socket-ID'))->toBe('12345.67890');
    });
});

test('passes request to next middleware', function (): void {
    $called = false;
    $request = Request::create('/test', 'GET');

    $response = app(SanitizeSocketId::class)->handle($request, function () use (&$called) {
        $called = true;

        return response('ok');
    });

    expect($called)->toBeTrue();
    expect($response->getContent())->toBe('ok');
});
