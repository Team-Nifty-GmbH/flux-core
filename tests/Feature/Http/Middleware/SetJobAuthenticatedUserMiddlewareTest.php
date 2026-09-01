<?php

use FluxErp\Http\Middleware\SetJobAuthenticatedUserMiddleware;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

test('job runs unauthenticated when job context has no user', function (): void {
    Auth::setUser($this->user);
    Context::forget('user');

    $authInsideJob = false;
    app(SetJobAuthenticatedUserMiddleware::class)
        ->handle(new stdClass(), function (object $job) use (&$authInsideJob): object {
            $authInsideJob = auth()->check();

            return $job;
        });

    expect($authInsideJob)->toBeFalse();
});

test('job runs as the user from job context', function (): void {
    $other = User::factory()->create();

    Context::add('user', $other->getMorphClass() . ':' . $other->getKey());

    $authIdInsideJob = null;
    app(SetJobAuthenticatedUserMiddleware::class)
        ->handle(new stdClass(), function (object $job) use (&$authIdInsideJob): object {
            $authIdInsideJob = auth()->id();

            return $job;
        });

    expect($authIdInsideJob)->toBe($other->getKey());
});

test('restores the previous authenticated user after the job', function (): void {
    $other = User::factory()->create();

    Auth::setUser($this->user);
    Context::add('user', $other->getMorphClass() . ':' . $other->getKey());

    app(SetJobAuthenticatedUserMiddleware::class)
        ->handle(new stdClass(), fn (object $job) => $job);

    expect(auth()->id())->toBe($this->user->getKey());
});

test('stays unauthenticated after the job when nobody was authenticated before', function (): void {
    $other = User::factory()->create();

    auth()->forgetUser();
    Context::add('user', $other->getMorphClass() . ':' . $other->getKey());

    app(SetJobAuthenticatedUserMiddleware::class)
        ->handle(new stdClass(), fn (object $job) => $job);

    expect(auth()->user())->toBeNull();
});
