<?php

use FluxErp\Actions\Passkey\GeneratePasskeyAuthenticationOptionsAction;
use Illuminate\Support\Facades\Session;

test('options are persisted with put so they survive aged flash data', function (): void {
    Session::start();

    $options = resolve(GeneratePasskeyAuthenticationOptionsAction::class)->execute();

    // Simulate the session middleware running between the generate and the
    // authenticate request: any flashed data is aged out and forgotten.
    Session::ageFlashData();
    Session::ageFlashData();

    expect(Session::get('passkey-authentication-options'))->toBe($options);
});

test('pull removes the value so a replay returns null', function (): void {
    Session::start();

    resolve(GeneratePasskeyAuthenticationOptionsAction::class)->execute();

    Session::pull('passkey-authentication-options');

    expect(Session::get('passkey-authentication-options'))->toBeNull();
});
