<?php

uses(FluxErp\Tests\DuskTestCase::class);
use Laravel\Dusk\Browser;

uses(Illuminate\Foundation\Testing\DatabaseTruncation::class);

function login(): void
{
    $this->createLoginUser();
}

test('login', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/login')
            ->type('email', $this->user->email)
            ->type('password', $this->password)
            ->clickAndWaitForReload('@login-button')
            ->assertRouteIs('dashboard');
    });
});

test('login wrong credentials', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/login')
            ->type('email', 'test@test.de')
            ->type('password', 'password')
            ->click('@login-button')
            ->waitForText('Login failed')
            ->assertSee('Login failed');
    });
});
