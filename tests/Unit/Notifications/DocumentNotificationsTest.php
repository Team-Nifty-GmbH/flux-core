<?php

use FluxErp\Notifications\DocumentsReady;
use FluxErp\Notifications\ExportReady;

test('documents ready notification has toast with count', function (): void {
    $notification = new DocumentsReady(3, '/tmp/test.pdf');

    $array = $notification->toArray($this->user);

    expect($array)->toBeArray()->not->toBeEmpty();
});

test('documents ready notification with single document', function (): void {
    $notification = new DocumentsReady(1);

    $array = $notification->toArray($this->user);

    expect($array)->toBeArray();
});

test('export ready notification has toast', function (): void {
    $notification = new ExportReady('/tmp/export.xlsx', 'Order');

    $array = $notification->toArray($this->user);

    expect($array)->toBeArray()->not->toBeEmpty();
});
