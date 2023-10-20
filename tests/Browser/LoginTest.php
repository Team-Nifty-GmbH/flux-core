<?php

namespace FluxErp\Tests\Browser;

use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class LoginTest extends DuskTestCase
{
    public function test_login(): void
    {
        $language = Language::query()->where('language_code', config('app.locale'))->first();
        if (! $language) {
            $language = Language::factory()->create(['language_code' => config('app.locale')]);
        }

        $user = User::factory()->create([
            'language_id' => $language->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->clickAndWaitForReload('@login-button')
                ->assertRouteIs('dashboard');
        });
    }
}
