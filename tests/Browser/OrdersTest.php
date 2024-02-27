<?php

namespace FluxErp\Tests\Browser;

use FluxErp\Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;

class OrdersTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_user_can_see_orders()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');

            $this->openMenu();
            $browser->script("Alpine.\$data(document.getElementById('main-navigation')).toggleMenu('orders');");

            $browser->pause(600)
                ->click('nav [href="/orders/list"]')
                ->waitForRoute('orders.orders')
                ->assertRouteIs('orders.orders')
                ->waitForText('Order Type -> Name', 30);
        });
    }
}
