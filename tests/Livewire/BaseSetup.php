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
    protected User $user;

    protected Model $dbClient;

    protected Address $address;

    protected Contact $contact;

    protected string $defaultLanguageCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->dbClient = Client::factory()->create();
        $language = Language::query()->where('language_code', config('app.locale'))->first();
        if (! $language) {
            $language = Language::factory()->create(['language_code' => config('app.locale')]);
        }

        $this->defaultLanguageCode = $language->language_code;

        if (str_starts_with(get_called_class(), 'FluxErp\\Tests\\Livewire\\Portal\\')) {
            $this->contact = Contact::factory()->create([
                'client_id' => $this->dbClient->id,
            ]);
            $this->address = Address::factory()->create([
                'contact_id' => $this->contact->id,
                'client_id' => $this->dbClient->id,
                'language_id' => $language->id,
            ]);

            $this->actingAs($this->address, 'address');
        } else {
            $this->user = new User();
            $this->user->language_id = $language->id;
            $this->user->email = faker()->email();
            $this->user->firstname = 'firstname';
            $this->user->lastname = 'lastname';
            $this->user->password = 'password';
            $this->user->save();

            $this->actingAs($this->user, 'web');
        }

    }
}
