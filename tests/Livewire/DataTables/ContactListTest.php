<?php

use FluxErp\Livewire\DataTables\ContactList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ContactList::class)
        ->assertOk();
});

test('maps the department into the main address of a new contact', function (): void {
    $form = Livewire::test(ContactList::class)->instance()->createContactForm;
    $form->company = 'Test Corp';
    $form->department = 'Research';

    expect(data_get($form->toActionData(), 'main_address.department'))->toBe('Research');
});
