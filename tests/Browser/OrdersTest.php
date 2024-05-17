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
                ->waitForRoute(route: 'orders.orders', seconds: 30)
                ->assertRouteIs('orders.orders')
                ->waitForText(text: 'Order Type -> Name', seconds: 30);
        });
    }

    // test for deleting order
    public function test_user_can_delete_open_order() {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');

            $this->openMenu();
            $browser->script("Alpine.\$data(document.getElementById('main-navigation')).toggleMenu('orders');");

            $browser->pause(600)
                ->click('nav [href="/orders/list"]')
                ->waitForRoute(route: 'orders.orders', seconds: 30)
                ->waitForText(text: 'Payment State', seconds: 30);

        });
    }
}
