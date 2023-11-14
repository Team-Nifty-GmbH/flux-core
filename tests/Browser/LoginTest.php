<?php

namespace FluxErp\Tests\Browser;

use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;

class LoginTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $user;

    private string $password;

    protected function setUp(): void
    {
        parent::setUp();
        $language = Language::factory()->create();

        $this->password = '#Password123';

        $this->user = new User();
        $this->user->language_id = $language->id;
        $this->user->email = 'testuser@test.de';
        $this->user->firstname = 'TestUserFirstname';
        $this->user->lastname = 'TestUserLastname';
        $this->user->password = $this->password;
        $this->user->is_active = true;
        $this->user->save();
    }

    public function test_login_wrong_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'test@test.de')
                ->type('password', 'password')
                ->click('@login-button')
                ->waitForText('Login failed')
                ->assertSee('Login failed');
        });
    }

    public function test_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', $this->user->email)
                ->type('password', $this->password)
                ->clickAndWaitForReload('@login-button')
                ->assertRouteIs('dashboard');
        });
    }
}
