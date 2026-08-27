<?php

use FluxErp\Livewire\DataTables\ContactList;
use FluxErp\Models\Contact;
use FluxErp\Models\User;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ContactList::class)
        ->assertOk();
});

test('assign to agent sets the agent on the selected contacts', function (): void {
    $contact = Contact::factory()->create();
    $agent = User::factory()->create(['is_active' => true]);

    Livewire::test(ContactList::class)
        ->set('selected', [$contact->getKey()])
        ->set('agentId', $agent->getKey())
        ->call('assignToAgent')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($contact->refresh()->agent_id)->toEqual($agent->getKey());
});
