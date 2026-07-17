<?php

use FluxErp\Http\Middleware\EnsureTwoFactorSetup;
use FluxErp\Settings\SecuritySettings;
use Illuminate\Http\Request;

test('lets the request through when force is not enabled', function (): void {
    $request = Request::create('/dashboard', 'GET');
    $request->setUserResolver(fn () => $this->user);

    $called = false;
    $response = app(EnsureTwoFactorSetup::class)->handle($request, function () use (&$called) {
        $called = true;

        return new Illuminate\Http\Response('ok');
    });

    expect($called)->toBeTrue();
    expect($response->getContent())->toBe('ok');
});

test('redirects to two-factor setup when per-user force is set without method', function (): void {
    $this->user->update(['force_two_factor' => true]);

    $request = Request::create('/dashboard', 'GET');
    $request->setUserResolver(fn () => $this->user);

    $response = app(EnsureTwoFactorSetup::class)->handle(
        $request,
        fn () => new Illuminate\Http\Response('ok')
    );

    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toBe(route('two-factor.setup'));
});

test('redirects when system-wide force is enabled', function (): void {
    SecuritySettings::fake([
        'force_two_factor' => true,
        'magic_login_links_enabled' => true,
    ]);

    $request = Request::create('/dashboard', 'GET');
    $request->setUserResolver(fn () => $this->user);

    $response = app(EnsureTwoFactorSetup::class)->handle(
        $request,
        fn () => new Illuminate\Http\Response('ok')
    );

    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toBe(route('two-factor.setup'));
});

test('lets the setup route itself through to avoid a redirect loop', function (): void {
    $this->user->update(['force_two_factor' => true]);

    $request = Request::create(route('two-factor.setup'), 'GET');
    $request->setUserResolver(fn () => $this->user);
    $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

    $called = false;
    $response = app(EnsureTwoFactorSetup::class)->handle($request, function () use (&$called) {
        $called = true;

        return new Illuminate\Http\Response('ok');
    });

    expect($called)->toBeTrue();
    expect($response->getContent())->toBe('ok');
});

test('lets logout through so forced users can leave', function (): void {
    $this->user->update(['force_two_factor' => true]);

    $request = Request::create(route('logout'), 'POST');
    $request->setUserResolver(fn () => $this->user);
    $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

    $called = false;
    $response = app(EnsureTwoFactorSetup::class)->handle($request, function () use (&$called) {
        $called = true;

        return new Illuminate\Http\Response('ok');
    });

    expect($called)->toBeTrue();
    expect($response->getContent())->toBe('ok');
});

test('lets the request through once the user has a TOTP method configured', function (): void {
    $this->user->update(['force_two_factor' => true]);
    $this->user->createTwoFactorAuth();
    $this->user->confirmTwoFactorAuth($this->user->makeTwoFactorCode());

    $request = Request::create('/dashboard', 'GET');
    $request->setUserResolver(fn () => $this->user->fresh());

    $called = false;
    $response = app(EnsureTwoFactorSetup::class)->handle($request, function () use (&$called) {
        $called = true;

        return new Illuminate\Http\Response('ok');
    });

    expect($called)->toBeTrue();
    expect($response->getContent())->toBe('ok');
});
