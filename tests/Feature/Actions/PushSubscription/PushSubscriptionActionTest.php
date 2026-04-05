<?php

use FluxErp\Actions\PushSubscription\UpsertPushSubscription;

test('upsert push subscription', function (): void {
    $sub = UpsertPushSubscription::make([
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'keys' => [
            'p256dh' => 'test-p256dh-key',
            'auth' => 'test-auth-key',
        ],
    ])->validate()->execute();

    expect($sub)->endpoint->toBe('https://fcm.googleapis.com/fcm/send/test-endpoint');
});

test('upsert push subscription requires endpoint and keys', function (): void {
    UpsertPushSubscription::assertValidationErrors([], ['endpoint', 'keys']);
});
