<?php

namespace FluxErp\Tests\Browser;

use FluxErp\Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class LoginTest extends DuskTestCase
{
    public function test_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->screenshot('login-page');
        });
    }
}
