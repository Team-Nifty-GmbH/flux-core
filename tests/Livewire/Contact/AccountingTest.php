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

    public function test_renders_successfully(): void
    {
        Livewire::test(Accounting::class)
            ->assertStatus(200);
    }

    public function test_switch_tabs(): void
    {
        $client = Client::factory()->create([
            'is_default' => true,
        ]);
        $this->contact = Contact::factory()->create([
            'client_id' => $client->id,
        ]);

        $form = new ContactForm(Livewire::new(Accounting::class), 'contact');
        $form->fill($this->contact);

        Livewire::test(Accounting::class, ['contact' => $form])->cycleTabs();
    }
}
