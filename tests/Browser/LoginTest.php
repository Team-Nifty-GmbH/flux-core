<?php

namespace FluxErp\Tests\Browser;

use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Dusk\Browser;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations, DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $language = Language::query()->where('language_code', config('app.locale'))->first();
        if (! $language) {
            $language = Language::factory()->create(['language_code' => config('app.locale')]);
        }

        $this->user = User::factory()->create([
            'language_id' => $language->id,
        ]);
    }

    public function test_login(): void
    {
        $user = $this->user;

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->clickAndWaitForReload('@login-button')
                ->assertRouteIs('dashboard');
        });
    }
}
