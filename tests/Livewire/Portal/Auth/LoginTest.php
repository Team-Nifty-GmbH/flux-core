<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Portal\Auth\Login;
use FluxErp\Mail\MagicLoginLink;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function (): void {
    app('auth')->logout();
});

test('login link', function (): void {
    Mail::fake();

    Livewire::test(Login::class)
        ->set('email', $this->address->email)
        ->set('password')
        ->call('login')
        ->assertNoRedirect()
        ->assertDispatched('tallstackui:toast');

    $this->assertGuest();

    Mail::assertSent(MagicLoginLink::class);
});

test('login successful', function (): void {
    Livewire::test(Login::class)
        ->set('email', $this->address->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('portal.dashboard'));

    $this->assertAuthenticatedAs($this->address);
});

test('login wrong password', function (): void {
    Livewire::test(Login::class)
        ->set('email', 'noexistingmail@example.com')
        ->set('password', 'wrongpassword')
        ->call('login')
        ->assertNoRedirect()
        ->assertDispatched('tallstackui:toast');

    $this->assertGuest();
});

test('renders successfully', function (): void {
    Livewire::test(Login::class)
        ->assertStatus(200);
});
