<?php

use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\DeleteMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Models\MailAccount;

test('create mail account', function (): void {
    $account = CreateMailAccount::make(['name' => 'Support Inbox'])
        ->validate()->execute();

    expect($account)->toBeInstanceOf(MailAccount::class)
        ->name->toBe('Support Inbox');
});

test('create mail account requires name', function (): void {
    CreateMailAccount::assertValidationErrors([], 'name');
});

test('update mail account', function (): void {
    $account = MailAccount::factory()->create();

    $updated = UpdateMailAccount::make([
        'id' => $account->getKey(),
        'name' => 'Sales Inbox',
    ])->validate()->execute();

    expect($updated->name)->toBe('Sales Inbox');
});

test('delete mail account', function (): void {
    $account = MailAccount::factory()->create();

    expect(DeleteMailAccount::make(['id' => $account->getKey()])
        ->validate()->execute())->toBeTrue();
});
