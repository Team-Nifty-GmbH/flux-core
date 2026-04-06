<?php

use FluxErp\Actions\DeviceToken\CreateDeviceToken;
use FluxErp\Actions\DeviceToken\DeleteDeviceToken;
use FluxErp\Actions\DeviceToken\UpdateDeviceToken;

test('create device token', function (): void {
    $token = CreateDeviceToken::make([
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
        'device_id' => 'device-123',
        'token' => 'fcm-token-abc',
        'platform' => 'android',
    ])->validate()->execute();

    expect($token)
        ->device_id->toBe('device-123')
        ->platform->value->toBe('android');
});

test('create device token requires authenticatable device_id token platform', function (): void {
    CreateDeviceToken::assertValidationErrors([], ['authenticatable_type', 'authenticatable_id', 'device_id', 'token', 'platform']);
});

test('update device token', function (): void {
    $token = CreateDeviceToken::make([
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
        'device_id' => 'device-456',
        'token' => 'old-token',
        'platform' => 'ios',
    ])->validate()->execute();

    $updated = UpdateDeviceToken::make([
        'id' => $token->getKey(),
        'token' => 'new-token',
    ])->validate()->execute();

    expect($updated->token)->toBe('new-token');
});

test('delete device token', function (): void {
    $token = CreateDeviceToken::make([
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
        'device_id' => 'device-789',
        'token' => 'temp-token',
        'platform' => 'web',
    ])->validate()->execute();

    expect(DeleteDeviceToken::make(['id' => $token->getKey()])
        ->validate()->execute())->toBeTrue();
});
