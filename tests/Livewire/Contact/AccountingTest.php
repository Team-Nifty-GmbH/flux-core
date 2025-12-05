<?php

use FluxErp\Livewire\Contact\Accounting;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Contact;
use FluxErp\Models\Tenant;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Accounting::class)
        ->assertOk();
});

test('switch tabs', function (): void {
    $tenant = Tenant::factory()->create([
        'is_default' => true,
    ]);
    $this->contact = Contact::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $form = new ContactForm(Livewire::new(Accounting::class), 'contact');
    $form->fill($this->contact);

    Livewire::test(Accounting::class, ['contact' => $form])->cycleTabs();
});
