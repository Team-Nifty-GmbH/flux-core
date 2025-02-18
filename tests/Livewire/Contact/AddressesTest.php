<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Addresses;
use FluxErp\Livewire\Forms\AddressForm;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

class AddressesTest extends BaseSetup
{
    private ContactForm $contactForm;

    private AddressForm $addressForm;

    protected function setUp(): void
    {
        parent::setUp();

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
        $address->permissions()->create([
            'name' => Str::random(),
            'guard' => 'address',
        ]);

        $this->contactForm = new ContactForm(Livewire::new(Addresses::class), 'contact');
        $this->contactForm->fill($contact);

        $this->addressForm = new AddressForm(Livewire::new(Addresses::class), 'address');
        $this->addressForm->fill($address);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Addresses::class)
            ->assertStatus(200);
    }

    public function test_switch_tabs()
    {
        $component = Livewire::actingAs($this->user)
            ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm]);

        foreach (Livewire::new(Addresses::class)->getTabs() as $tab) {
            $component
                ->set('tab', $tab->component)
                ->assertStatus(200);

            if ($tab->isLivewireComponent) {
                $component->assertSeeLivewire($tab->component);
            }
        }
    }

    public function test_can_save_address()
    {
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
    }

    public function test_can_update_password()
    {
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
    }

    public function test_replicate_address()
    {
        $component = Livewire::actingAs($this->user)
            ->test(Addresses::class, ['contact' => $this->contactForm, 'address' => $this->addressForm])
            ->assertNotSet('address.permissions', null)
            ->call('replicate')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->set('address.lastname', $lastname = Str::uuid())
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertGreaterThan($this->addressForm->id, $component->get('address.id'));
        $this->assertDatabaseHas('addresses', ['lastname' => $lastname]);

        $dbAddress = Address::query()->whereKey($component->get('address.id'))->with('permissions')->first();

        $this->assertEmpty($dbAddress->permissions);
        $this->assertDatabaseMissing(
            'meta',
            [
                'model_id' => $dbAddress->id,
                'model_type' => $dbAddress->getMorphClass(),
                'key' => 'permissions',
            ]
        );
    }
}
