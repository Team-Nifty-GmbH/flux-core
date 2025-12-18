<?php

use Illuminate\Support\Str;

test('login', function (): void {
    $this->actingAsGuest();

    visit('/')
        ->assertNoSmoke()
        ->assertRoute('login')
        ->type('password', 'password')
        ->type('email', $this->user->email)
        ->assertSee('Login')
        ->click('Login')
        ->assertRoute('dashboard');
});

test('login wrong credentials', function (): void {
    $this->actingAsGuest();

    visit('/')
        ->assertRoute('login')
        ->type('password', Str::uuid()->toString())
        ->type('email', fake()->email())
        ->assertSee('Login')
        ->click('Login')
        ->assertSee('Login failed');
});
