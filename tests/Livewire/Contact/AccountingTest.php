<?php

use FluxErp\Livewire\Contact\Accounting;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Accounting::class)
        ->assertOk();
});

test('switch tabs', function (): void {
    $client = Client::factory()->create([
        'is_default' => true,
    ]);
    $this->contact = Contact::factory()->create([
        'client_id' => $client->id,
    ]);

    $form = new ContactForm(Livewire::new(Accounting::class), 'contact');
    $form->fill($this->contact);

    Livewire::test(Accounting::class, ['contact' => $form])->cycleTabs();
});
