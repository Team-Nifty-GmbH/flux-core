<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Models\Client;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class BaseSetup extends TestCase
{
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
