<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
test('dashboard no user', function (): void {
    $this->get('/')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('dashboard page', function (): void {
    $this->actingAs($this->user, 'web')->get('/')
        ->assertStatus(200);
});
