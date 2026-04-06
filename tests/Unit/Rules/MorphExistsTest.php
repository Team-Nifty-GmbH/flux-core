<?php

use FluxErp\Models\Contact;
use FluxErp\Rules\MorphExists;
use Illuminate\Support\Facades\Validator;

test('morph exists passes for existing record', function (): void {
    $contact = Contact::factory()->create();

    $passes = Validator::make(
        [
            'model_type' => morph_alias(Contact::class),
            'model_id' => $contact->getKey(),
        ],
        ['model_id' => app(MorphExists::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('morph exists fails for non-existing record', function (): void {
    $nonExistingId = (Contact::query()->max('id') ?? 0) + 9999;

    $passes = Validator::make(
        [
            'model_type' => morph_alias(Contact::class),
            'model_id' => $nonExistingId,
        ],
        ['model_id' => app(MorphExists::class)]
    )->passes();

    expect($passes)->toBeFalse();
});

test('morph exists fails for invalid morph type', function (): void {
    $passes = Validator::make(
        [
            'model_type' => 'nonexistent-type',
            'model_id' => 1,
        ],
        ['model_id' => app(MorphExists::class)]
    )->passes();

    expect($passes)->toBeFalse();
});
