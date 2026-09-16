<?php

use FluxErp\Http\Middleware\TrackVisits;
use FluxErp\Traits\Action\BroadcastsActionEvents;
use FluxErp\Traits\Model\BroadcastsEvents;

test('every file calling defer imports it from Illuminate', function (string $class): void {
    $file = (new ReflectionClass($class))->getFileName();
    $source = file_get_contents($file);

    expect($source)->toContain('defer(')
        ->and($source)->toContain('use function Illuminate\Support\defer;');
})->with([
    BroadcastsEvents::class,
    BroadcastsActionEvents::class,
    TrackVisits::class,
]);
