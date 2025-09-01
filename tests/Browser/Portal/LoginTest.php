<?php

uses(FluxErp\Tests\Browser\Portal\PortalDuskTestCase::class);
use Laravel\Dusk\Browser;

test('login successful', function (): void {
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
});

test('login wrong credentials', function (): void {
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
});
