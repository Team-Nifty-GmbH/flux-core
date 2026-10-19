<?php

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\DeleteAddress;
use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
});

test('create address', function (): void {
    $address = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Test GmbH',
        'street' => 'Main Street 1',
        'city' => 'Berlin',
        'zip' => '10115',
    ])->validate()->execute();

    expect($address)
        ->toBeInstanceOf(Address::class)
        ->company->toBe('Test GmbH')
        ->contact_id->toBe($this->contact->getKey());
});

test('first address becomes main address automatically', function (): void {
    $address = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'First Address',
    ])->validate()->execute();

    expect($address)
        ->is_main_address->toBeTrue()
        ->is_invoice_address->toBeTrue()
        ->is_delivery_address->toBeTrue();
});

test('second address does not override main address flags', function (): void {
    Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);

    $second = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Second Address',
    ])->validate()->execute();

    expect($second)
        ->is_main_address->toBeFalse()
        ->is_invoice_address->toBeFalse()
        ->is_delivery_address->toBeFalse();
});

test('create address requires contact_id', function (): void {
    CreateAddress::assertValidationErrors(
        ['company' => 'Test'],
        'contact_id'
    );
});

test('create address strips email angle brackets', function (): void {
    $address = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'email' => 'John Doe <john@example.com>',
    ])->validate()->execute();

    expect($address->email)->toBe('john@example.com');
});

test('update address', function (): void {
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
    ]);

    $updated = UpdateAddress::make([
        'id' => $address->getKey(),
        'company' => 'Updated Corp',
    ])->validate()->execute();

    expect($updated->company)->toBe('Updated Corp');
});

test('create address does not set is_payment_reminder_address automatically', function (): void {
    $address = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Only Address',
    ])->validate()->execute();

    expect($address->is_payment_reminder_address)->toBeFalse();
});

test('setting is_payment_reminder_address unsets it on other addresses', function (): void {
    $first = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'First',
        'is_payment_reminder_address' => true,
    ])->validate()->execute();

    $second = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Second',
        'is_payment_reminder_address' => true,
    ])->validate()->execute();

    $first->refresh();

    expect($second->is_payment_reminder_address)->toBeTrue()
        ->and($first->is_payment_reminder_address)->toBeFalse();
});

test('update address to is_payment_reminder_address unsets others', function (): void {
    $first = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'First',
        'is_payment_reminder_address' => true,
    ])->validate()->execute();

    $second = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Second',
    ])->validate()->execute();

    UpdateAddress::make([
        'id' => $second->getKey(),
        'is_payment_reminder_address' => true,
    ])->validate()->execute();

    $first->refresh();
    $second->refresh();

    expect($second->is_payment_reminder_address)->toBeTrue()
        ->and($first->is_payment_reminder_address)->toBeFalse();
});

test('no address needs is_payment_reminder_address', function (): void {
    $first = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'First',
    ])->validate()->execute();

    $second = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Second',
    ])->validate()->execute();

    expect($first->is_payment_reminder_address)->toBeFalse()
        ->and($second->is_payment_reminder_address)->toBeFalse();
});

test('delete address', function (): void {
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
    ]);

    $result = DeleteAddress::make(['id' => $address->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
});

test('create address attaches address types', function (): void {
    $addressType = FluxErp\Models\AddressType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $address = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Typed',
        'address_types' => [$addressType->getKey()],
    ])
        ->validate()
        ->execute();

    expect($address->addressTypes()->pluck('address_types.id')->all())->toBe([$addressType->getKey()]);
});

test('update address syncs address types without contact_id', function (): void {
    $kept = FluxErp\Models\AddressType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $removed = FluxErp\Models\AddressType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $address = Address::factory()->create(['contact_id' => $this->contact->getKey()]);
    $address->addressTypes()->attach([$kept->getKey(), $removed->getKey()]);

    UpdateAddress::make([
        'id' => $address->getKey(),
        'address_types' => [$kept->getKey()],
    ])
        ->validate()
        ->execute();

    expect($address->addressTypes()->pluck('address_types.id')->all())->toBe([$kept->getKey()]);
});

test('update address with empty address types removes all of them', function (): void {
    $addressType = FluxErp\Models\AddressType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $address = Address::factory()->create(['contact_id' => $this->contact->getKey()]);
    $address->addressTypes()->attach($addressType->getKey());

    UpdateAddress::make([
        'id' => $address->getKey(),
        'address_types' => [],
    ])
        ->validate()
        ->execute();

    expect($address->addressTypes()->exists())->toBeFalse();
});

test('update address without has_formal_salutation keeps the stored value', function (): void {
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'has_formal_salutation' => ! app(FluxErp\Settings\CoreSettings::class)->formal_salutation,
    ]);

    UpdateAddress::make([
        'id' => $address->getKey(),
        'company' => 'Renamed',
    ])
        ->validate()
        ->execute();

    expect($address->refresh()->has_formal_salutation)
        ->toBe(! app(FluxErp\Settings\CoreSettings::class)->formal_salutation);
});

test('update address keeps contact options it may not update', function (): void {
    $address = Address::factory()->create(['contact_id' => $this->contact->getKey()]);
    $option = FluxErp\Actions\ContactOption\CreateContactOption::make([
        'address_id' => $address->getKey(),
        'type' => 'phone',
        'label' => 'Mobile',
        'value' => '+49 123',
    ])
        ->validate()
        ->execute();
    FluxErp\Models\Permission::findOrCreate(
        'action.' . FluxErp\Actions\ContactOption\UpdateContactOption::name()
    );

    UpdateAddress::make([
        'id' => $address->getKey(),
        'contact_options' => [
            ['id' => $option->getKey(), 'type' => 'phone', 'label' => 'Mobile', 'value' => '+49 999'],
        ],
    ])
        ->validate()
        ->execute();

    expect(FluxErp\Models\ContactOption::query()->whereKey($option->getKey())->value('value'))
        ->toBe('+49 123');
});

test('update address does not touch contact options of another address', function (): void {
    $address = Address::factory()->create(['contact_id' => $this->contact->getKey()]);
    $other = Address::factory()->create(['contact_id' => Contact::factory()->create()->getKey()]);
    $foreign = FluxErp\Actions\ContactOption\CreateContactOption::make([
        'address_id' => $other->getKey(),
        'type' => 'phone',
        'label' => 'Foreign',
        'value' => '+49 123',
    ])
        ->validate()
        ->execute();

    UpdateAddress::make([
        'id' => $address->getKey(),
        'contact_options' => [
            ['id' => $foreign->getKey(), 'type' => 'phone', 'label' => 'Hijacked', 'value' => '+49 999'],
        ],
    ])
        ->validate()
        ->execute();

    expect($foreign->refresh())
        ->address_id->toBe($other->getKey())
        ->label->toBe('Foreign')
        ->value->toBe('+49 123');
});
