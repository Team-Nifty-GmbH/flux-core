<?php

use FluxErp\Actions\MailFolder\CreateMailFolder;
use FluxErp\Actions\MailFolder\DeleteMailFolder;
use FluxErp\Actions\MailFolder\UpdateMailFolder;
use FluxErp\Models\MailAccount;

beforeEach(function (): void {
    $this->mailAccount = MailAccount::factory()->create();
});

test('create mail folder', function (): void {
    $folder = CreateMailFolder::make([
        'mail_account_id' => $this->mailAccount->getKey(),
        'name' => 'Inbox',
        'slug' => 'inbox',
    ])->validate()->execute();

    expect($folder)->name->toBe('Inbox');
});

test('create mail folder requires mail_account_id name slug', function (): void {
    CreateMailFolder::assertValidationErrors([], ['mail_account_id', 'name', 'slug']);
});

test('update mail folder', function (): void {
    $folder = CreateMailFolder::make([
        'mail_account_id' => $this->mailAccount->getKey(),
        'name' => 'Original',
        'slug' => 'original',
    ])->validate()->execute();

    $updated = UpdateMailFolder::make([
        'id' => $folder->getKey(),
        'name' => 'Archive',
    ])->validate()->execute();

    expect($updated->name)->toBe('Archive');
});

test('delete mail folder', function (): void {
    $folder = CreateMailFolder::make([
        'mail_account_id' => $this->mailAccount->getKey(),
        'name' => 'Temp',
        'slug' => 'temp',
    ])->validate()->execute();

    expect(DeleteMailFolder::make(['id' => $folder->getKey()])
        ->validate()->execute())->toBeTrue();
});
