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

test('posting to login is locked after too many failed attempts', function (): void {
    $this->actingAsGuest();

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['email' => $this->user->email, 'password' => 'wrongpassword']);
    }

    $this->post('/login', ['email' => $this->user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('web');
});
