<?php

use FluxErp\Actions\Record\MergeRecords;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\NotificationSetting;
use FluxErp\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $contact = Contact::factory()->create();

    [$this->main, $this->merge] = collect(['Main GmbH', 'Merged GmbH'])
        ->map(fn (string $company) => Address::factory()->create([
            'contact_id' => $contact->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'is_main_address' => false,
            'company' => $company,
        ]))
        ->all();

    $this->action = fn () => MergeRecords::make([
        'model_type' => morph_alias(Address::class),
        'main_record' => ['id' => $this->main->getKey(), 'columns' => []],
        'merge_records' => [['id' => $this->merge->getKey(), 'columns' => ['company']]],
    ])
        ->validate();

    $this->notificationSetting = fn (Address $address, string $type): NotificationSetting => NotificationSetting::query()
        ->create([
            'notifiable_type' => $address->getMorphClass(),
            'notifiable_id' => $address->getKey(),
            'notification_type' => $type,
            'channel' => 'mail',
        ]);
});

test('a relation that cannot be moved rolls the merge back and names the relation', function (): void {
    Exceptions::fake();

    ($this->notificationSetting)($this->main, 'conflict');
    $moved = ($this->notificationSetting)($this->merge, 'moved first');
    $conflict = ($this->notificationSetting)($this->merge, 'conflict');

    $action = ($this->action)();
    $exception = null;

    try {
        DB::transaction(fn () => $action->execute());
    } catch (ValidationException $exception) {
    }

    expect($exception?->errors())->toHaveKey('notificationSettings')
        ->and($this->main->refresh()->company)->toBe('Main GmbH')
        ->and(Address::query()->whereKey($this->merge->getKey())->exists())->toBeTrue()
        ->and($moved->refresh()->notifiable_id)->toBe($this->merge->getKey())
        ->and($conflict->refresh()->notifiable_id)->toBe($this->merge->getKey());

    Exceptions::assertReported(UniqueConstraintViolationException::class);
});

test('a deadlock stays a query exception so the transaction can retry', function (): void {
    Exceptions::fake();

    ($this->notificationSetting)($this->merge, 'moved');

    Event::listen(
        'eloquent.saving: ' . NotificationSetting::class,
        fn () => throw new QueryException(
            'mysql',
            'update notification_settings',
            [],
            new PDOException('Deadlock found when trying to get lock')
        )
    );

    expect(fn () => ($this->action)()->execute())->toThrow(QueryException::class);

    Exceptions::assertNothingReported();
});

test('a relation this installation has no columns for is not a failure', function (): void {
    Exceptions::fake();
    Log::spy();

    $role = Role::query()->create(['name' => 'merge test', 'guard_name' => 'web']);
    DB::table('model_has_roles')->insert([
        'role_id' => $role->getKey(),
        'model_type' => $this->merge->getMorphClass(),
        'model_id' => $this->merge->getKey(),
    ]);

    $result = ($this->action)()->execute();

    expect($result->company)->toBe('Merged GmbH')
        ->and($result->roles()->whereKey($role->getKey())->exists())->toBeTrue()
        ->and(Address::query()->whereKey($this->merge->getKey())->exists())->toBeFalse();

    Log::shouldNotHaveReceived('warning');
    Exceptions::assertNothingReported();
});
