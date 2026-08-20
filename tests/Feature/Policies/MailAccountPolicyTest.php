<?php

use FluxErp\Models\MailAccount;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Tests\Fixtures\Models\InstanceMailAccount;

test('an assigned user can view the mail account', function (): void {
    $user = User::factory()->create();
    $mailAccount = MailAccount::factory()->create();
    $mailAccount->users()->attach($user);

    expect($user->can('view', $mailAccount))->toBeTrue();
});

test('an unassigned user cannot view the mail account', function (): void {
    $user = User::factory()->create();
    $firstAccount = MailAccount::factory()->create();
    $secondAccount = MailAccount::factory()->create();
    $secondAccount->users()->attach($user);

    expect($user->can('view', $firstAccount))->toBeFalse();
});

test('an assigned user can view a derived instance mail account', function (): void {
    $user = User::factory()->create();
    $mailAccount = InstanceMailAccount::query()
        ->findOrFail(MailAccount::factory()->create()->getKey());
    $mailAccount->users()->attach($user);

    expect($user->can('view', $mailAccount))->toBeTrue();
});

test('a super admin can view any mail account', function (): void {
    Role::findOrCreate('Super Admin');
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');
    $mailAccount = MailAccount::factory()->create();

    expect($admin->can('view', $mailAccount))->toBeTrue();
});

test('an undefined ability is denied even for an assigned user', function (): void {
    $user = User::factory()->create();
    $mailAccount = MailAccount::factory()->create();
    $mailAccount->users()->attach($user);

    expect($user->can('update', $mailAccount))->toBeFalse();
});
