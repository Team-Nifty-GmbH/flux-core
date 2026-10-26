<?php

use FluxErp\Actions\Address\DeleteAddress;
use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Models\Activity;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

test('create contact with defaults', function (): void {
    $contact = CreateContact::make([])
        ->validate()->execute();

    expect($contact)
        ->toBeInstanceOf(Contact::class)
        ->customer_number->not->toBeNull()
        ->payment_type_id->not->toBeNull()
        ->price_list_id->not->toBeNull();
});

test('create contact with main address', function (): void {
    $contact = CreateContact::make([
        'main_address' => [
            'company' => 'Test Corp',
            'firstname' => 'John',
            'lastname' => 'Doe',
        ],
    ])->validate()->execute();

    expect($contact->mainAddress)
        ->not->toBeNull()
        ->company->toBe('Test Corp');
});

test('create contact with tenant attachment', function (): void {
    $contact = CreateContact::make([
        'tenants' => [$this->dbTenant->getKey()],
    ])->validate()->execute();

    expect($contact->tenants)->toHaveCount(1);
});

test('create contact validates payment_type for tenant', function (): void {
    $otherTenant = FluxErp\Models\Tenant::factory()->create();
    $paymentType = PaymentType::factory()
        ->hasAttached($otherTenant, relationship: 'tenants')
        ->create();

    CreateContact::assertValidationErrors([
        'payment_type_id' => $paymentType->getKey(),
        'tenants' => [$this->dbTenant->getKey()],
    ], 'payment_type_id');
});

test('update contact', function (): void {
    $contact = Contact::factory()->create();

    $updated = UpdateContact::make([
        'id' => $contact->getKey(),
        'customer_number' => 'K-99999',
    ])->validate()->execute();

    expect($updated->customer_number)->toBe('K-99999');
});

test('update contact rejects duplicate customer_number', function (): void {
    $contact1 = Contact::factory()->create(['customer_number' => 'K-11111']);
    $contact2 = Contact::factory()->create();

    UpdateContact::assertValidationErrors([
        'id' => $contact2->getKey(),
        'customer_number' => 'K-11111',
    ], 'customer_number');
});

test('delete contact', function (): void {
    $contact = Contact::factory()->create();

    $result = DeleteContact::make(['id' => $contact->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
    expect(Contact::query()->whereKey($contact->getKey())->exists())->toBeFalse();
});

test('delete contact deletes it exactly once', function (): void {
    $contact = Contact::factory()->create();
    Address::factory()->create(['contact_id' => $contact->getKey()]);

    $deletedEvents = 0;
    Event::listen('eloquent.deleted: ' . $contact::class, function () use (&$deletedEvents): void {
        $deletedEvents++;
    });

    $softDeleteQueries = 0;
    DB::listen(function (QueryExecuted $query) use (&$softDeleteQueries): void {
        if (str_starts_with($query->sql, 'update `contacts` set `deleted_at`')) {
            $softDeleteQueries++;
        }
    });

    DeleteContact::make(['id' => $contact->getKey()])
        ->validate()->execute();

    expect($deletedEvents)->toBe(1)
        ->and($softDeleteQueries)->toBe(1)
        ->and(
            Activity::query()
                ->where('subject_type', $contact->getMorphClass())
                ->where('subject_id', $contact->getKey())
                ->where('event', 'deleted')
                ->count()
        )->toBe(1)
        ->and(Contact::onlyTrashed()->whereKey($contact->getKey())->exists())->toBeTrue()
        ->and(Address::query()->where('contact_id', $contact->getKey())->exists())->toBeFalse();
});

test('deleting the last address deletes its contact once', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);

    $deletedEvents = 0;
    Event::listen('eloquent.deleted: ' . $contact::class, function () use (&$deletedEvents): void {
        $deletedEvents++;
    });

    DeleteAddress::make(['id' => $address->getKey()])
        ->validate()->execute();

    expect($deletedEvents)->toBe(1)
        ->and(
            Activity::query()
                ->where('subject_type', $contact->getMorphClass())
                ->where('subject_id', $contact->getKey())
                ->where('event', 'deleted')
                ->count()
        )->toBe(1)
        ->and(Contact::onlyTrashed()->whereKey($contact->getKey())->exists())->toBeTrue();
});

test('a default payment type the rules reject is not put on a new contact', function (): void {
    PaymentType::query()->update(['is_default' => false]);
    PaymentType::factory()->create([
        'is_default' => true,
        'is_active' => true,
        'is_sales' => false,
    ]);

    $contact = CreateContact::make([])
        ->validate()
        ->execute();

    expect($contact)->toBeInstanceOf(Contact::class)
        ->and($contact->payment_type_id)->toBeNull();
});

test('create contact creates an inline discount for the contact', function (): void {
    $contact = CreateContact::make([
        'discounts' => [
            ['discount' => 0.1, 'is_percentage' => true, 'sort_number' => 0],
        ],
    ])
        ->validate()
        ->execute();

    $discount = $contact->discounts()->sole();

    expect($discount)
        ->discount->toEqual(0.1)
        ->model_type->toBe(morph_alias(Contact::class))
        ->model_id->toBe($contact->getKey());
});

test('update contact keeps a new inline discount', function (): void {
    $contact = Contact::factory()->create();

    $action = UpdateContact::make([
        'id' => $contact->getKey(),
        'discounts' => [
            ['discount' => 0.1, 'is_percentage' => true, 'sort_number' => 0],
        ],
    ])
        ->validate();

    expect($action->getData('discounts'))
        ->toBe([['sort_number' => 0, 'is_percentage' => true, 'discount' => 0.1]]);
});

test('update contact rejects an inline discount without its required fields', function (): void {
    $contact = Contact::factory()->create();

    UpdateContact::assertValidationErrors([
        'id' => $contact->getKey(),
        'discounts' => [
            ['discount' => 0.1],
        ],
    ], ['discounts.0.is_percentage', 'discounts.0.sort_number']);
});

test('update contact keeps a new inline discount sent with a null id', function (): void {
    $contact = Contact::factory()->create();

    $action = UpdateContact::make([
        'id' => $contact->getKey(),
        'discounts' => [
            ['id' => null, 'discount' => 0.1, 'is_percentage' => true, 'sort_number' => 0],
        ],
    ])
        ->validate();

    expect($action->getData('discounts'))
        ->toBe([['id' => null, 'sort_number' => 0, 'is_percentage' => true, 'discount' => 0.1]]);
});
