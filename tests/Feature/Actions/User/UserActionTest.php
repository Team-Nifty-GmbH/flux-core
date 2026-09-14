<?php

use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\DeleteUser;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Models\MailAccount;
use FluxErp\Models\User;

test('create user', function (): void {
    $user = CreateUser::make([
        'email' => 'new@example.com',
        'firstname' => 'New',
        'lastname' => 'User',
        'user_code' => 'NU001',
        'password' => 'Secret123!',
        'language_id' => $this->defaultLanguage->getKey(),
    ])->validate()->execute();

    expect($user)
        ->toBeInstanceOf(User::class)
        ->email->toBe('new@example.com')
        ->is_active->toBeTruthy();
});

test('create user requires email password and user_code', function (): void {
    CreateUser::assertValidationErrors(
        ['firstname' => 'Test'],
        ['email', 'password', 'user_code']
    );
});

test('update user', function (): void {
    $user = User::factory()->create([
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    $updated = UpdateUser::make([
        'id' => $user->getKey(),
        'firstname' => 'Updated',
    ])->validate()->execute();

    expect($updated->firstname)->toBe('Updated');
});

test('update user syncs mail accounts by their id', function (): void {
    $user = User::factory()->create([
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
    $mailAccounts = MailAccount::factory()->count(3)->create();
    $user->mailAccounts()->attach($mailAccounts[0]->getKey());

    UpdateUser::make([
        'id' => $user->getKey(),
        'mail_accounts' => $mailAccounts->modelKeys(),
        'default_mail_account_id' => $mailAccounts[2]->getKey(),
    ])->validate()->execute();

    expect(
        $user->mailAccounts()
            ->pluck('mail_account_user.is_default', 'mail_accounts.id')
            ->map(fn (mixed $isDefault): bool => (bool) $isDefault)
            ->all()
    )->toEqual([
        $mailAccounts[0]->getKey() => false,
        $mailAccounts[1]->getKey() => false,
        $mailAccounts[2]->getKey() => true,
    ]);
});

test('create user attaches mail accounts by their id', function (): void {
    $mailAccounts = MailAccount::factory()->count(2)->create();

    $user = CreateUser::make([
        'email' => 'new@example.com',
        'firstname' => 'New',
        'lastname' => 'User',
        'user_code' => 'NU001',
        'password' => 'Secret123!',
        'language_id' => $this->defaultLanguage->getKey(),
        'mail_accounts' => $mailAccounts->modelKeys(),
        'default_mail_account_id' => $mailAccounts[1]->getKey(),
    ])->validate()->execute();

    expect(
        $user->mailAccounts()
            ->pluck('mail_account_user.is_default', 'mail_accounts.id')
            ->map(fn (mixed $isDefault): bool => (bool) $isDefault)
            ->all()
    )->toEqual([
        $mailAccounts[0]->getKey() => false,
        $mailAccounts[1]->getKey() => true,
    ]);
});

test('update user deactivation deletes tokens', function (): void {
    $user = User::factory()->create([
        'language_id' => $this->defaultLanguage->getKey(),
        'is_active' => true,
    ]);
    $user->createToken('test-token');

    UpdateUser::make([
        'id' => $user->getKey(),
        'is_active' => false,
    ])->validate()->execute();

    expect($user->fresh()->tokens)->toHaveCount(0);
});

test('delete user', function (): void {
    $user = User::factory()->create([
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    $result = DeleteUser::make(['id' => $user->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
});

test('cannot delete self', function (): void {
    DeleteUser::assertValidationErrors([
        'id' => $this->user->getKey(),
    ], 'id');
});
