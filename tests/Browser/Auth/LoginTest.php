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

test('login card follows the dark theme', function (): void {
    $this->actingAsGuest();

    $page = visit('/');
    $page->script("() => localStorage.setItem('dark-theme', 'true')");

    $page->navigate('/')
        ->assertRoute('login')
        ->assertScript(
            "getComputedStyle(document.querySelector('form').closest('.shadow')).backgroundColor",
            'rgb(30, 41, 59)'
        );
});
