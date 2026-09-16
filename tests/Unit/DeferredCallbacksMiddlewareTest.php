<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks;

test('the package makes sure deferred callbacks are invoked', function (): void {
    expect(app(Kernel::class)->hasMiddleware(InvokeDeferredCallbacks::class))->toBeTrue();
});

test('an application without the middleware gets it from the package', function (): void {
    $kernel = app(Kernel::class);

    (new ReflectionProperty($kernel, 'middleware'))->setValue(
        $kernel,
        array_values(
            array_filter(
                (new ReflectionProperty($kernel, 'middleware'))->getValue($kernel),
                fn (string $middleware): bool => $middleware !== InvokeDeferredCallbacks::class
            )
        )
    );

    expect($kernel->hasMiddleware(InvokeDeferredCallbacks::class))->toBeFalse();

    app()->register(FluxErp\FluxServiceProvider::class, force: true);

    expect($kernel->hasMiddleware(InvokeDeferredCallbacks::class))->toBeTrue();
});
