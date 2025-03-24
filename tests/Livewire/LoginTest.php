<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Auth\Login;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LoginTest extends TestCase
{
    public function test_redirect_to_dashboard_as_authenticated_user(): void
    {
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
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(Login::class)
            ->assertStatus(200)
            ->assertNoRedirect();
    }
}
