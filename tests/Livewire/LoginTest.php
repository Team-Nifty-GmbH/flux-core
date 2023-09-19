<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Auth\Login;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class LoginTest extends TestCase
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Login::class)
            ->assertStatus(200)
            ->assertNoRedirect();
    }

    public function test_redirect_to_dashboard_as_authenticated_user()
    {
        $this->withoutVite();

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

    public function test_redirect_to_login_page_as_unauthenticated_user()
    {
        Livewire::test(Dashboard::class)
            ->assertRedirect(Login::class);
    }
}
