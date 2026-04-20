<?php

use FluxErp\Http\Middleware\Localization;
use FluxErp\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

test('sets locale from content-language header', function (): void {
    $request = Request::create('/test', 'GET');
    $request->headers->set('content-language', 'fr');

    app(Localization::class)->handle($request, function (): void {});

    expect(app()->getLocale())->toBe('fr');
    expect(Carbon::getLocale())->toBe('fr');
});

test('sets locale from accept-language header', function (): void {
    Language::factory()->create(['language_code' => 'es']);

    auth()->logout();

    $request = Request::create('/test', 'GET');
    $request->headers->set('Accept-Language', 'es');

    app(Localization::class)->handle($request, function (): void {});

    expect(app()->getLocale())->toBe('es');
    expect(Carbon::getLocale())->toBe('es');
});

test('matches base language from accept-language header', function (): void {
    Language::factory()->create(['language_code' => 'de']);

    auth()->logout();

    $request = Request::create('/test', 'GET');
    $request->headers->set('Accept-Language', 'de-DE,de;q=0.9,en;q=0.5');

    app(Localization::class)->handle($request, function (): void {});

    expect(app()->getLocale())->toBe('de');
});

test('prefers content-language over accept-language header', function (): void {
    Language::factory()->create(['language_code' => 'es']);

    auth()->logout();

    $request = Request::create('/test', 'GET');
    $request->headers->set('content-language', 'fr');
    $request->headers->set('Accept-Language', 'es');

    app(Localization::class)->handle($request, function (): void {});

    expect(app()->getLocale())->toBe('fr');
});

test('prefers user language over accept-language header', function (): void {
    $language = Language::factory()->create(['language_code' => 'it']);
    Language::factory()->create(['language_code' => 'es']);

    $this->user->update(['language_id' => $language->getKey()]);
    $this->user->load('language');

    $request = Request::create('/test', 'GET');
    $request->headers->set('Accept-Language', 'es');

    app(Localization::class)->handle($request, function (): void {});

    expect(app()->getLocale())->toBe('it');
});

test('falls back to default language', function (): void {
    $this->defaultLanguage->update(['language_code' => 'pt']);
    Cache::memo()->forget('default_' . morph_alias(Language::class));

    auth()->logout();

    $request = Request::create('/test', 'GET');
    $request->headers->remove('accept-language');

    app(Localization::class)->handle($request, function (): void {});

    expect(app()->getLocale())->toBe('pt');
});

test('caches language codes as array not collection', function (): void {
    Language::factory()->create(['language_code' => 'ja']);

    auth()->logout();
    Cache::forget('available_language_codes');
    Cache::memo()->flush();

    $request = Request::create('/test', 'GET');
    $request->headers->set('Accept-Language', 'ja');
    app(Localization::class)->handle($request, function (): void {});

    $cached = Cache::get('available_language_codes');

    expect($cached)->toBeArray()
        ->and($cached)->toContain('ja');
});

test('passes request to next middleware', function (): void {
    $called = false;
    $request = Request::create('/test', 'GET');

    $response = app(Localization::class)->handle($request, function () use (&$called) {
        $called = true;

        return response('ok');
    });

    expect($called)->toBeTrue();
    expect($response->getContent())->toBe('ok');
});
