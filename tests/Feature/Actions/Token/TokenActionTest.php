<?php

use FluxErp\Actions\Token\CreateToken;
use FluxErp\Actions\Token\DeleteToken;

test('create token', function (): void {
    $token = CreateToken::make([
        'name' => 'API Token',
        'abilities' => ['*'],
    ])->validate()->execute();

    expect($token)
        ->name->toBe('API Token')
        ->plain_text_token->not->toBeNull();
});

test('create token requires name', function (): void {
    CreateToken::assertValidationErrors([], 'name');
});

test('delete token', function (): void {
    $created = CreateToken::make([
        'name' => 'to-delete',
        'abilities' => ['*'],
    ])->validate()->execute();

    expect(DeleteToken::make(['id' => $created->getKey()])
        ->validate()->execute())->toBeTrue();
});
