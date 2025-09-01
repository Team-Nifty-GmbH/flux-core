<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Portal\Auth\ResetPassword;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('can reset password', function (): void {
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

    expect($updatedAddress->email)->toEqual($baseAddress->email);
    expect($updatedAddress->email_primary)->toEqual($baseAddress->email_primary);
    $this->assertNotEquals($baseAddress->password, $updatedAddress->password);
});

test('renders successfully', function (): void {
    Livewire::test(ResetPassword::class)
        ->assertStatus(200);
});
