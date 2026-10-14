<?php

use FluxErp\Livewire\Contact\Communication as ContactCommunication;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('sending from a record tab keeps a single communication', function (): void {
    config(['mail.default' => 'array']);
    $contact = Contact::factory()->create();

    Livewire::test(ContactCommunication::class, ['modelId' => $contact->getKey()])
        ->set('communication.communication_type_enum', 'mail')
        ->set('communication.to', ['customer@example.com'])
        ->set('communication.subject', 'Invoice 4711')
        ->set('communication.html_body', '<p>Your invoice</p>')
        ->call('send')
        ->assertReturned(true);

    $communication = resolve_static(Communication::class, 'query')->sole();

    expect($communication->subject)->toBe('Invoice 4711')
        ->and($communication->message_id)->not->toBeNull()
        ->and($communication->contacts()->whereKey($contact->getKey())->exists())->toBeTrue();
});
