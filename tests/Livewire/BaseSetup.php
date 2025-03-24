<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class BaseSetup extends TestCase
{
    protected Address $address;

    protected Contact $contact;

    protected Model $dbClient;

    protected Language $defaultLanguage;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->dbClient = Client::factory()->create(['is_default' => true]);
        $this->defaultLanguage = Language::query()
            ->where('language_code', config('app.locale'))
            ->first()
            ?? Language::factory()->create(['language_code' => config('app.locale')]);

        if (str_starts_with(get_called_class(), 'FluxErp\\Tests\\Livewire\\Portal\\')) {
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
        } else {
            $this->user = new User();
            $this->user->language_id = $this->defaultLanguage->id;
            $this->user->email = faker()->email();
            $this->user->firstname = 'firstname';
            $this->user->lastname = 'lastname';
            $this->user->password = 'password';
            $this->user->save();

            $this->actingAs($this->user, 'web');
        }
    }
}
