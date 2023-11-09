<?php

namespace FluxErp\Tests\Browser\Portal;

use Laravel\Dusk\Browser;

class LoginTest extends PortalDuskTestCase
{
    public function login(): void
    {
        $this->createLoginUser();
    }

    public function test_login_wrong_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(config('flux.portal_domain') . ':8001/')
                ->assertSee('For more transparency, quality and speed in all service processes')
                ->type('email', 'user@usertest.de')
                ->type('password', 'testpassword')
                ->press('Login')
                ->waitForText('Login failed')
                ->assertSee('Login failed');
        });
    }

    public function test_login_successfull()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(config('flux.portal_domain') . ':8001/')
                ->assertSee(__('For more transparency, quality and speed in all service processes'))
                ->type('email', $this->user->login_name)
                ->type('password', $this->password)
                ->press('Login')
                ->waitForReload()
                ->assertRouteIs('portal.dashboard');

            $this->openMenu();

            $browser->waitForText($this->user->name)
                ->assertSee($this->user->name);
        });
    }
}
