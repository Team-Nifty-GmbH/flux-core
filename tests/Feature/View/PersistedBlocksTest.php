<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;

beforeEach(function (): void {
    Artisan::call('view:clear');
});

test('a persisted block renders its content', function (): void {
    $rendered = Blade::render('@persist(\'demo\')<span>held</span>@endpersist');

    expect($rendered)->toContain('x-persist="demo"')
        ->and($rendered)->toContain('held');
});

test('a persisted block the client holds arrives empty', function (): void {
    request()->headers->set('X-Flux-Persisted', 'demo,navigation');

    $rendered = Blade::render('@persist(\'demo\')<span>held</span>@endpersist');

    expect($rendered)->toContain('x-persist="demo"')
        ->and($rendered)->not->toContain('held');
});

test('a persisted block the client does not hold still renders', function (): void {
    request()->headers->set('X-Flux-Persisted', 'navigation');

    $rendered = Blade::render('@persist(\'demo\')<span>held</span>@endpersist');

    expect($rendered)->toContain('held');
});

test('a request without the header renders every block', function (): void {
    $rendered = Blade::render('@persist(\'navigation\')<span>menu</span>@endpersist');

    expect($rendered)->toContain('menu');
});
