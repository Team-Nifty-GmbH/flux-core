<?php

namespace FluxErp\Tests\Browser;

use FluxErp\Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;

class LoginTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function login(): void
    {
        $this->createLoginUser();
    }

    public function test_login(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('email', $this->user->email)
                ->type('password', $this->password)
                ->clickAndWaitForReload('@login-button')
                ->assertRouteIs('dashboard');
        });
    }

    public function test_login_wrong_credentials(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('email', 'test@test.de')
                ->type('password', 'password')
                ->click('@login-button')
                ->waitForText('Login failed')
                ->assertSee('Login failed');
        });
    }
}
