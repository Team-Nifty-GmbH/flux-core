<?php

use FluxErp\Livewire\Contact\Projects;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    Livewire::test(Projects::class, ['contactId' => $contact->id])
        ->assertOk();
});
