<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Addresses;
use FluxErp\Livewire\Forms\AddressForm;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AddressesTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Addresses::class)
            ->assertStatus(200);
    }

    public function test_switch_tabs()
    {
        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);
        $address = Address::factory()->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $contact->id,
            'is_main_address' => true,
            'is_invoice_address' => true,
            'is_delivery_address' => true,
        ]);

        $contactForm = new ContactForm(Livewire::new(Addresses::class), 'contact');
        $contactForm->fill($contact);

        $addressForm = new AddressForm(Livewire::new(Addresses::class), 'address');
        $addressForm->fill($address);

        $component = Livewire::actingAs($this->user)
            ->test(Addresses::class, ['contact' => $contactForm, 'address' => $addressForm]);

        foreach (Livewire::new(Addresses::class)->getTabs() as $tab) {
            $component
                ->set('tab', $tab->component)
                ->assertStatus(200);

            if ($tab->isLivewireComponent) {
                $component->assertSeeLivewire($tab->component);
            }
        }
    }
}
