<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\MailAccount;
use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

test('logs old and new attribute changes on update', function (): void {
    $orderType = OrderType::factory()->create(['name' => 'Original Name', 'is_active' => true]);

    $orderType->update(['name' => 'Updated Name']);

    $activity = Activity::query()
        ->where('subject_type', morph_alias(OrderType::class))
        ->where('subject_id', $orderType->getKey())
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->attribute_changes->toArray())->toHaveKeys(['old', 'attributes'])
        ->and($activity->attribute_changes['attributes'])->toHaveKey('name', 'Updated Name')
        ->and($activity->attribute_changes['old'])->toHaveKey('name', 'Original Name');
});

test('encrypted and hashed attributes are never logged', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'password' => 'imap-secret',
        'smtp_password' => 'smtp-secret',
    ]);
    $mailAccount->update([
        'name' => 'Renamed',
        'password' => 'new-imap-secret',
        'smtp_password' => 'new-smtp-secret',
    ]);

    $address = Address::factory()
        ->for(Contact::factory())
        ->create(['password' => 'portal-secret']);

    $activities = Activity::query()
        ->where(fn (Builder $query): Builder => $query
            ->where('subject_type', morph_alias(MailAccount::class))
            ->where('subject_id', $mailAccount->getKey())
        )
        ->orWhere(fn (Builder $query): Builder => $query
            ->where('subject_type', morph_alias(Address::class))
            ->where('subject_id', $address->getKey())
        )
        ->get();

    $logged = $activities
        ->flatMap(fn (Activity $activity): array => array_merge(
            array_keys(data_get($activity->attribute_changes?->toArray(), 'attributes') ?? []),
            array_keys(data_get($activity->attribute_changes?->toArray(), 'old') ?? []),
            array_keys(data_get($activity->properties?->toArray(), 'attributes') ?? []),
            array_keys(data_get($activity->properties?->toArray(), 'old') ?? [])
        ))
        ->unique()
        ->values()
        ->all();

    expect($activities)->toHaveCount(3)
        ->and($logged)->toContain('name')
        ->and($logged)->not->toContain('password', 'smtp_password');
});
