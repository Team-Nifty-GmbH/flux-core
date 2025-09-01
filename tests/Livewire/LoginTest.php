<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Auth\Login;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use Livewire\Livewire;

test('redirect to dashboard as authenticated user', function (): void {
    $language = Language::query()->where('language_code', config('app.locale'))->first();
    if (! $language) {
        $language = Language::factory()->create(['language_code' => config('app.locale')]);
    }

    $user = User::factory()->create([
        'language_id' => $language->id,
        'email' => faker()->email(),
    ]);

    $this->actingAs($user, 'web');

    Livewire::test(Login::class)
        ->assertRedirect(Dashboard::class);
});

test('renders successfully', function (): void {
    Livewire::test(Login::class)
        ->assertStatus(200)
        ->assertNoRedirect();
});
