<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Contact\WorkTimes;
use FluxErp\Models\Contact;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(WorkTimes::class, ['contactId' => $this->contact->id])
        ->assertStatus(200);
});
