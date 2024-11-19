<?php

namespace Tests\Feature\Livewire;

use FluxErp\Livewire\Auth\Login;
use FluxErp\Livewire\InstallWizard;
use FluxErp\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Livewire\Livewire;

class InstallWizardTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Set the configuration value
        Config::set('flux.install_done', false);
    }

    public function test_renders_successfully()
    {
        Livewire::test(InstallWizard::class)
            ->assertStatus(200);
    }

    public function test_forbidden_when_done()
    {
        Config::set('flux.install_done', true);

        Livewire::test(InstallWizard::class)
            ->assertStatus(403);
    }

    public function test_install_wizard()
    {
        // Set the configuration value
        Config::set('flux.install_done', false);

        $component = Livewire::test(InstallWizard::class)
            ->assertStatus(200)
            ->call('testDatabaseConnection')
            ->assertHasNoErrors()
            ->assertSet('databaseConnectionSuccessful', true)
            ->assertSet('requestRefresh', true)
            ->call('reload')
            ->assertSet('requestRefresh', false)
            ->call('start')
            ->assertDispatched('batch-id')
            ->call('continue')
            ->assertSet('step', 1);

        $component->assertSee('Language')
            ->set('languageForm.name', Str::uuid())
            ->set('languageForm.language_code', Str::uuid())
            ->call('continue')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('step', 2);

        $component->assertSee('Currency')
            ->set('currencyForm.name', Str::uuid())
            ->set('currencyForm.iso', Str::uuid())
            ->set('currencyForm.symbol', Str::uuid())
            ->call('continue')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('step', 3);

        $component->assertSee('Client')
            ->set('clientForm.name', Str::uuid())
            ->set('clientForm.client_code', Str::uuid())
            ->set('clientForm.ceo', Str::uuid())
            ->set('clientForm.street', Str::uuid())
            ->set('clientForm.city', Str::uuid())
            ->set('clientForm.postcode', Str::uuid())
            ->set('clientForm.phone', Str::uuid())
            ->set('clientForm.email', Str::uuid() . '@example.com')
            ->set('clientForm.website', Str::uuid())
            ->call('continue')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('step', 4);

        $component->assertSee('Vat Rates')
            ->set('vatRateForm.name', $vatRateName = Str::uuid())
            ->set('vatRateForm.rate_percentage_frontend', $vatRatePercentage = 19)
            ->call('addVatRate')
            ->assertHasNoErrors()
            ->assertSet('vatRates.0.name', $vatRateName)
            ->assertSet('vatRates.0.rate_percentage', bcdiv($vatRatePercentage, 100))
            ->call('continue')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('step', 5);

        $component->assertSee('Payment Type')
            ->set('paymentTypeForm.name', Str::uuid())
            ->set('paymentTypeForm.payment_target', 1)
            ->set('paymentTypeForm.payment_reminder_days_1', 2)
            ->set('paymentTypeForm.payment_reminder_days_2', 3)
            ->set('paymentTypeForm.payment_reminder_days_3', 4)
            ->call('continue')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('step', 6);

        $component->assertSee('User')
            ->set('userForm.email', Str::uuid() . '@example.com')
            ->set('userForm.firstname', Str::uuid())
            ->set('userForm.lastname', Str::uuid())
            ->set('userForm.user_code', Str::uuid())
            ->set('userForm.password', 'Password123!')
            ->set('userForm.password_confirmation', 'Password123!')
            ->call('continue')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('step', 7);

        $component->assertSee('Installation Complete!')
            ->call('continue')
            ->assertHasNoErrors()
            ->assertRedirect(Login::class);

        $this->assertDatabaseHas('users', [
            'email' => $component->get('userForm.email'),
            'firstname' => $component->get('userForm.firstname'),
            'lastname' => $component->get('userForm.lastname'),
            'user_code' => $component->get('userForm.user_code'),
        ]);
        $this->assertDatabaseHas('payment_types', [
            'name' => $component->get('paymentTypeForm.name'),
            'payment_target' => $component->get('paymentTypeForm.payment_target'),
            'payment_reminder_days_1' => $component->get('paymentTypeForm.payment_reminder_days_1'),
            'payment_reminder_days_2' => $component->get('paymentTypeForm.payment_reminder_days_2'),
            'payment_reminder_days_3' => $component->get('paymentTypeForm.payment_reminder_days_3'),
        ]);
        $this->assertDatabaseHas('vat_rates', [
            'name' => $component->get('vatRateForm.name'),
            'rate_percentage' => $component->get('vatRateForm.rate_percentage'),
        ]);
        $this->assertDatabaseHas('clients', [
            'name' => $component->get('clientForm.name'),
            'client_code' => $component->get('clientForm.client_code'),
            'ceo' => $component->get('clientForm.ceo'),
            'street' => $component->get('clientForm.street'),
            'city' => $component->get('clientForm.city'),
            'postcode' => $component->get('clientForm.postcode'),
            'phone' => $component->get('clientForm.phone'),
            'email' => $component->get('clientForm.email'),
            'website' => $component->get('clientForm.website'),
        ]);
        $this->assertDatabaseHas('currencies', [
            'name' => $component->get('currencyForm.name'),
            'iso' => $component->get('currencyForm.iso'),
            'symbol' => $component->get('currencyForm.symbol'),
        ]);
        $this->assertDatabaseHas('languages', [
            'name' => $component->get('languageForm.name'),
            'language_code' => $component->get('languageForm.language_code'),
        ]);
        $this->assertDataBaseHas('price_lists', [
            'name' => 'Default',
            'price_list_code' => 'default',
            'is_net' => true,
            'is_default' => true,
        ]);
        $this->assertDataBaseHas('warehouses', [
            'name' => 'Default',
            'is_default' => true,
        ]);
    }
}
