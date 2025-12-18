<?php

test('dashboard no user', function (): void {
    $this->actingAsGuest();

    $this->get('/')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('dashboard page', function (): void {
    $this->actingAs($this->user, 'web')->get('/')
        ->assertOk();
});
