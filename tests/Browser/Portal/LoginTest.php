<?php

namespace FluxErp\Tests\Browser\Portal;

use Laravel\Dusk\Browser;

class LoginTest extends PortalDuskTestCase
{
    public function test_login_successful(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser
                ->visit($this->baseUrl())
                ->assertSee(__('For more transparency, quality and speed in all service processes'))
                ->type('email', $this->user->email)
                ->type('password', $this->password)
                ->press('Login')
                ->waitForReload()
                ->assertRouteIs('portal.dashboard')
                ->assertSee('Return to website');

            $this->openMenu();

            $browser->waitForText($this->user->name)
                ->assertSee($this->user->name);
        });
    }

    public function test_login_wrong_credentials(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser
                ->visit($this->baseUrl())
                ->assertSee('For more transparency, quality and speed in all service processes')
                ->type('email', 'user@usertest.de')
                ->type('password', 'testpassword')
                ->press('Login')
                ->waitForText('Login failed')
                ->assertSee('Login failed');
        });
    }
}
