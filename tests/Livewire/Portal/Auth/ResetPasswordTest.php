<?php

namespace FluxErp\Tests\Livewire\Portal\Auth;

use FluxErp\Livewire\Portal\Auth\ResetPassword;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Str;
use Livewire\Livewire;

class ResetPasswordTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(ResetPassword::class)
            ->assertStatus(200);
    }

    public function test_can_reset_password()
    {
        /** @var Address $baseAddress */
        $baseAddress = Contact::factory()
            ->has(
                Address::factory()
                    ->for($this->dbClient)
                    ->set('password', bcrypt(Str::random(10)))
            )
            ->for(User::factory()->for($this->defaultLanguage), 'agent')
            ->for(PriceList::factory())
            ->for(PaymentType::factory()->hasAttached($this->dbClient))
            ->for($this->dbClient)
            ->create()
            ->addresses()
            ->first();

        $token = app(PasswordBroker::class)->createToken($baseAddress);

        Livewire::test(ResetPassword::class)
            ->set('email', $baseAddress->email)
            ->set('token', $token)
            ->set('password', $password = Str::random(10) . 'A_1')
            ->set('password_confirmation', $password . '1')
            ->call('resetPassword')
            ->assertHasErrors(['password'])
            ->set('password_confirmation', $password)
            ->call('resetPassword')
            ->assertRedirectToRoute('portal.login')
            ->assertHasNoErrors()
            ->assertSessionHas('flash.success');

        $updatedAddress = clone $baseAddress;
        $updatedAddress->refresh();

        $this->assertEquals($baseAddress->email, $updatedAddress->email);
        $this->assertNotEquals($baseAddress->password, $updatedAddress->password);
    }
}
