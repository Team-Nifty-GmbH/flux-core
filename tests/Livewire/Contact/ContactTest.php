<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Contact;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact as ContactModel;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ContactTest extends TestCase
{
    private ContactModel $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::factory()->create([
            'is_default' => true,
        ]);
        $this->contact = ContactModel::factory()->create([
            'client_id' => $client->id,
        ]);

        Address::factory()->create([
            'client_id' => $client->id,
            'contact_id' => $this->contact->id,
            'is_main_address' => true,
            'is_invoice_address' => true,
            'is_delivery_address' => true,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Contact::class, ['id' => $this->contact->id])
            ->assertStatus(200);
    }

    public function test_switch_tabs()
    {
        $component = Livewire::test(Contact::class, ['id' => $this->contact->id]);

        foreach (Livewire::new(Contact::class)->getTabs() as $tab) {
            $component
                ->set('tab', $tab->component)
                ->assertStatus(200);

            if ($tab->isLivewireComponent) {
                $component->assertSeeLivewire($tab->component);
            }
        }
    }
}
