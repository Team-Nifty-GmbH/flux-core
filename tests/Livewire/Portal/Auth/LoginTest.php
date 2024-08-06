<?php

namespace FluxErp\Tests\Livewire\Portal\Auth;

use FluxErp\Livewire\Portal\Auth\Login;
use FluxErp\Mail\MagicLoginLink;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

class LoginTest extends BaseSetup
{
    protected function setUp(): void
    {
        // logout user
        parent::setUp();

        app('auth')->logout();
    }

    public function test_renders_successfully()
    {
        Livewire::test(Login::class)
            ->assertStatus(200);
    }

    public function test_login_wrong_password()
    {
        Livewire::test(Login::class)
            ->set('email', 'noexistingmail@example.com')
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertNoRedirect()
            ->assertDispatched('wireui:notification');

        $this->assertGuest();
    }

    public function test_login_successful()
    {
        Livewire::test(Login::class)
            ->set('email', $this->address->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('portal.dashboard'));

        $this->assertAuthenticatedAs($this->address);
    }

    public function test_login_link()
    {
        Mail::fake();

        Livewire::test(Login::class)
            ->set('email', $this->address->email)
            ->set('password')
            ->call('login')
            ->assertNoRedirect()
            ->assertDispatched('wireui:notification');

        $this->assertGuest();

        Mail::assertSent(MagicLoginLink::class);
    }
}
