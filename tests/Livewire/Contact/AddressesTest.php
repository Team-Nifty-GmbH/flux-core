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

test('address updated event on the contact channel reloads the selected address', function (): void {
    $component = Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm]);

    Address::query()
        ->whereKey($this->addressForm->id)
        ->update(['street' => $street = Str::uuid()->toString()]);

    $component
        ->dispatch(
            'echo-private:contact.' . $this->contactForm->id . ',.AddressUpdated',
            ['model' => ['id' => $this->addressForm->id]]
        )
        ->assertOk()
        ->assertSet('address.street', $street);
});

test('address deleted event on the contact channel removes the address from the list', function (): void {
    $secondAddress = Address::factory()->create([
        'contact_id' => $this->contactForm->id,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm]);

    expect(collect($component->get('addresses'))->pluck('id'))->toContain($secondAddress->getKey());

    $secondAddress->delete();

    $component
        ->dispatch(
            'echo-private:contact.' . $this->contactForm->id . ',.AddressDeleted',
            ['model' => ['id' => $secondAddress->getKey()]]
        )
        ->assertOk();

    expect(collect($component->get('addresses'))->pluck('id'))->not->toContain($secondAddress->getKey());
});

test('listeners stay stable when the address list changes', function (): void {
    $secondAddress = Address::factory()->create([
        'contact_id' => $this->contactForm->id,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm]);

    $listenersOnMount = $component->instance()->getListeners();

    $secondAddress->delete();

    $component->dispatch(
        'echo-private:contact.' . $this->contactForm->id . ',.AddressDeleted',
        ['model' => ['id' => $secondAddress->getKey()]]
    );

    // client echo subscriptions are frozen at mount - every listener that existed
    // on mount has to stay valid for the whole component lifetime
    expect($component->instance()->getListeners())->toBe($listenersOnMount);
});
