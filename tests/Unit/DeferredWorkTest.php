<?php

use FluxErp\Http\Middleware\TrackVisits;
use FluxErp\Models\LeadState;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;

test('a visit is written after the response instead of through a queue job', function (): void {
    Queue::fake();

    $collection = app(DeferredCallbackCollection::class);
    $collection->forget('*');

    $request = Request::create('/dashboard', 'GET');
    $response = app(TrackVisits::class)->handle($request, fn (): Response => new Response());

    expect($response->getStatusCode())->toBe(200)
        ->and(Activity::query()->where('event', 'visit')->exists())->toBeFalse();

    $collection->invoke();

    $activity = Activity::query()->where('event', 'visit')->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('/dashboard')
        ->and(data_get($activity->properties, 'ip'))->not->toBeNull();

    Queue::assertNothingPushed();
});

test('a broadcast still goes out when the request ends in an error', function (): void {
    $collection = app(DeferredCallbackCollection::class);
    $collection->forget('*');

    LeadState::factory()->create();

    $callbacks = (new ReflectionProperty($collection, 'callbacks'))->getValue($collection);

    expect($callbacks)->not->toBeEmpty()
        ->and(collect($callbacks)->every(fn ($callback): bool => $callback->always))->toBeTrue();
});
