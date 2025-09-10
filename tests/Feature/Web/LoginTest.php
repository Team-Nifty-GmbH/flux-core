<?php

test('login as authenticated user', function (): void {
    $this->actingAs($this->user, 'web')->get('/login')
        ->assertFound()
        ->assertRedirect();
});

test('login no path', function (): void {
    $this->actingAsGuest();
    $this->get('/')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('login page', function (): void {
    $this->actingAsGuest();
    $this->get('/login')
        ->assertOk();
});
