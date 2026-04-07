<?php

use FluxErp\Rules\ValidStateRule;
use FluxErp\States\PaymentRun\PaymentRunState;
use Illuminate\Support\Facades\Validator;

test('invalid state fails', function (): void {
    $passes = Validator::make(
        ['state' => 'completely-invalid-state'],
        ['state' => ValidStateRule::make(PaymentRunState::class)]
    )->passes();

    expect($passes)->toBeFalse();
});
