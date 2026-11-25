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

test('a mail account has at most one sent folder', function (): void {
    $otherAccount = MailAccount::factory()->create();
    [$sent, $outbox] = collect(['Sent', 'Postausgang'])->map(fn (string $name) => CreateMailFolder::make([
        'mail_account_id' => $this->mailAccount->getKey(),
        'name' => $name,
        'slug' => $name,
        'is_sent' => $name === 'Sent',
    ])->validate()->execute());
    $otherSent = CreateMailFolder::make([
        'mail_account_id' => $otherAccount->getKey(),
        'name' => 'Sent',
        'slug' => 'Sent',
        'is_sent' => true,
    ])->validate()->execute();

    UpdateMailFolder::make(['id' => $outbox->getKey(), 'is_sent' => true])->validate()->execute();

    expect($outbox->refresh()->is_sent)->toBeTrue()
        ->and($sent->refresh()->is_sent)->toBeFalse()
        ->and($otherSent->refresh()->is_sent)->toBeTrue();
});
