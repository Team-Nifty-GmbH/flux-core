<?php

use FluxErp\Livewire\Contact\Addresses;
use FluxErp\Livewire\Forms\AddressForm;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->id,
        'is_delivery_address' => true,
        'is_invoice_address' => true,
        'is_main_address' => true,
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
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('address.street', $street)
        ->assertSet('edit', false);

    $this->assertDatabaseHas('addresses', ['id' => $this->addressForm->id, 'street' => $street]);
});

test('renders successfully', function (): void {
    Livewire::test(Addresses::class)
        ->assertOk();
});

test('replicate address', function (): void {
    $component = Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->call('replicate')
        ->assertOk()
        ->assertHasNoErrors()
        ->set('address.lastname', $lastname = Str::uuid())
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($component->get('address.id'))->toBeGreaterThan($this->addressForm->id);
    $this->assertDatabaseHas('addresses', ['lastname' => $lastname]);
});

test('switch tabs', function (): void {
    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->cycleTabs();
});

test('invalid salutation blocks saving', function (): void {
    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->set('address.salutation', 'Firma')
        ->set('address.street', $street = Str::uuid())
        ->set('edit', true)
        ->call('save')
        ->assertOk()
        ->assertHasErrors('address.salutation')
        ->assertSet('edit', true);

    $this->assertDatabaseMissing('addresses', ['id' => $this->addressForm->id, 'street' => $street]);
});

test('selecting another address resets validation errors', function (): void {
    $otherAddress = Address::factory()->create([
        'contact_id' => $this->contactForm->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->set('address.salutation', 'Firma')
        ->set('edit', true)
        ->call('save')
        ->assertHasErrors('address.salutation')
        ->call('select', $otherAddress->id)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('address.id', $otherAddress->id);
});

test('can move an address to another contact', function (): void {
    $secondary = Address::factory()->create(['contact_id' => $this->contactForm->id]);
    $target = Contact::factory()->create();

    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->set('addressId', $secondary->id)
        ->set('moveToContactId', $target->id)
        ->call('move')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('addresses', ['id' => $secondary->id, 'contact_id' => $target->id]);
});

test('moving an address to its own contact reports the error instead of throwing', function (): void {
    $secondary = Address::factory()->create(['contact_id' => $this->contactForm->id]);

    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->set('addressId', $secondary->id)
        ->set('moveToContactId', $this->contactForm->id)
        ->call('move')
        ->assertOk();

    $this->assertDatabaseHas('addresses', ['id' => $secondary->id, 'contact_id' => $this->contactForm->id]);
});

test('can lift an address into a new contact', function (): void {
    $secondary = Address::factory()->create([
        'contact_id' => $this->contactForm->id,
        'is_main_address' => false,
    ]);

    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->set('addressId', $secondary->id)
        ->call('detach')
        ->assertOk()
        ->assertHasNoErrors();

    expect($secondary->refresh()->contact_id)->not->toBe($this->contactForm->id)
        ->and($secondary->is_main_address)->toBeTrue();
});

test('goes back to the contacts when the last address is gone', function (): void {
    $address = Address::query()->whereKey($this->addressForm->id)->first();

    $component = Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm]);

    $address->forceDelete();

    $component
        ->call('addressDeleted', ['model' => ['id' => $this->addressForm->id]])
        ->assertOk()
        ->assertHasNoErrors()
        ->assertRedirect(route('contacts.contacts'));
});

test('goes back to the contacts when the only address is deleted from the pane', function (): void {
    Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
        ->set('addressId', $this->addressForm->id)
        ->call('delete')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertRedirect(route('contacts.contacts'));
});
