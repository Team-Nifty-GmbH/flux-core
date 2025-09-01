<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Contact\Addresses;
use FluxErp\Livewire\Forms\AddressForm;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
    $address = Address::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $contact->id,
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);

    $this->contactForm = new ContactForm(Livewire::new(Addresses::class), 'contact');
    $this->contactForm->fill($contact);

    $this->addressForm = new AddressForm(Livewire::new(Addresses::class), 'address');
    $this->addressForm->fill($address);
});

test('can save address', function (): void {
    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->set('address.street', $street = Str::uuid())
        ->set('edit', true)
        ->call('save')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('address.street', $street)
        ->assertSet('edit', false);

    $this->assertDatabaseHas('addresses', ['id' => $this->addressForm->id, 'street' => $street]);
});

test('can update password', function (): void {
    Address::query()
        ->whereKey($this->addressForm->id)
        ->update([
            'can_login' => 1,
            'password' => Hash::make('!password123'),
        ]);

    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->assertSet('address.password', null)
        ->set('address.password', $password = Hash::make(Str::random()))
        ->set('edit', true)
        ->call('save')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('address.password', $password)
        ->assertSet('edit', false);

    $this->assertDatabaseHas(
        'addresses',
        [
            'id' => $this->addressForm->id,
            'password' => $password,
        ]
    );
});

test('renders successfully', function (): void {
    Livewire::test(Addresses::class)
        ->assertStatus(200);
});

test('replicate address', function (): void {
    $originalAddress = Address::query()
        ->whereKey($this->addressForm->id)
        ->first();
    $originalAddress->givePermissionTo(Permission::findOrCreate(Str::random(), 'address'));
    $this->addressForm->fill($originalAddress->fresh());

    $component = Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->assertNotSet('address.permissions', null)
        ->assertCount('address.permissions', 1)
        ->call('replicate')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->set('address.lastname', $lastname = Str::uuid())
        ->call('save')
        ->assertStatus(200)
        ->assertHasNoErrors();

    expect($component->get('address.id'))->toBeGreaterThan($this->addressForm->id);
    $this->assertDatabaseHas('addresses', ['lastname' => $lastname]);

    $dbAddress = Address::query()
        ->whereKey($component->get('address.id'))
        ->with('permissions')
        ->first();

    expect($dbAddress->permissions)->toBeEmpty();
    $this->assertDatabaseMissing(
        'meta',
        [
            'model_id' => $dbAddress->id,
            'model_type' => $dbAddress->getMorphClass(),
            'key' => 'permissions',
        ]
    );
});

test('switch tabs', function (): void {
    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->cycleTabs();
});
