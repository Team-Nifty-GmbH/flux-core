<?php

use FluxErp\Http\Controllers\Controller;
use FluxErp\Http\Controllers\FilePondChunkController;

test('FilePondChunkController opts out of the permission middleware', function (): void {
    expect(FilePondChunkController::hasPermission())->toBeFalse();
});

test('base controllers require permission by default', function (): void {
    expect(Controller::hasPermission())->toBeTrue();
});
