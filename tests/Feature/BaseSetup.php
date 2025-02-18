<?php

namespace FluxErp\Tests\Feature;

use FluxErp\Models\Client;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class BaseSetup extends TestCase
{
    protected User $user;

    protected Model $dbClient;

    protected string $defaultLanguageCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbClient = Client::factory()->create(['is_default' => true]);
        $language = Language::query()->where('language_code', config('app.locale'))->first();
        if (! $language) {
            $language = Language::factory()->create(['language_code' => config('app.locale')]);
        }

        $this->defaultLanguageCode = $language->language_code;

        $this->user = new User();
        $this->user->language_id = $language->id;
        $this->user->email = 'TestUserThaTWillNeverExist@example.com';
        $this->user->firstname = 'firstname';
        $this->user->lastname = 'lastname';
        $this->user->password = 'password';
        $this->user->save();

        $this->user->clients()->attach($this->dbClient->getKey());
    }
}
