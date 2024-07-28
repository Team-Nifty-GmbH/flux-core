<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Accounting;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AccountingTest extends TestCase
{
    private Contact $contact;

    public function test_renders_successfully()
    {
        Livewire::test(Accounting::class)
            ->assertStatus(200);
    }

    public function test_switch_tabs()
    {
        $client = Client::factory()->create([
            'is_default' => true,
        ]);
        $this->contact = Contact::factory()->create([
            'client_id' => $client->id,
        ]);
        $form = new ContactForm(Livewire::new(Accounting::class), 'contact');
        $form->fill($this->contact);
        $component = Livewire::test(Accounting::class, ['contact' => $form]);

        foreach (Livewire::new(Accounting::class)->getTabs() as $tab) {
            $component
                ->set('tab', $tab->component)
                ->assertStatus(200);

            if ($tab->isLivewireComponent) {
                $component->assertSeeLivewire($tab->component);
            }
        }
    }
}
