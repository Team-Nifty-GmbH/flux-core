<?php

namespace FluxErp\Tests\Livewire\DataTablesWidgets;

use FluxErp\Livewire\Widgets\Birthdays;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class BirthdaysTest extends BaseSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->address = Address::factory()->create([
            'contact_id' => $this->contact->id,
            'client_id' => $this->dbClient->id,
            'date_of_birth' => now()->subYears(30),
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Birthdays::class)
            ->assertStatus(200)
            ->assertCount('items', 1)
            ->assertSet('items.0.label', $this->address->name)
            ->assertSet('items.0.subLabel', $this->address->date_of_birth->isoFormat('L') . ' (30)');
    }
}
