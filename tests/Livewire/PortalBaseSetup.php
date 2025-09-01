<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class PortalBaseSetup extends TestCase
{
    protected Address $address;

    protected Contact $contact;

    protected Model $dbClient;

    protected Language $defaultLanguage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->dbClient = Client::factory()->create(['is_default' => true]);
        $this->defaultLanguage = Language::query()
            ->where('language_code', config('app.locale'))
            ->first()
            ?? Language::factory()->create(['language_code' => config('app.locale')]);

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);
        $this->address = Address::factory()->create([
            'contact_id' => $this->contact->id,
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $this->defaultLanguage->id,
            'can_login' => true,
            'is_active' => true,
        ]);

        $this->actingAs($this->address, 'address');
    }
}
