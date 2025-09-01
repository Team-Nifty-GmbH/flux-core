<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
test('login as authenticated user', function (): void {
    $this->actingAs($this->user, 'web')->get('/login')
        ->assertStatus(302)
        ->assertRedirect();
});

test('login no path', function (): void {
    $this->get('/')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('login page', function (): void {
    $this->get('/login')
        ->assertStatus(200);
});
