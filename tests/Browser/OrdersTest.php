<?php

uses(FluxErp\Tests\DuskTestCase::class);
use Laravel\Dusk\Browser;

uses(Illuminate\Foundation\Testing\DatabaseTruncation::class);

test('user can see orders', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/');

        $this->openMenu();
        $browser->script("Alpine.\$data(document.getElementById('main-navigation')).toggleMenu('orders');");

        $browser->pause(600)
            ->click('nav [href="/orders/list"]')
            ->waitForRoute(route: 'orders.orders', seconds: 30)
            ->assertRouteIs('orders.orders')
            ->waitForText(text: 'Order Type -> Name', seconds: 30);
    });
});
