<?php

namespace FluxErp\Tests\Browser;

use FluxErp\Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function test_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'email')
                ->type('password', 'password')
                ->click('@login-button');
        });
    }
}
