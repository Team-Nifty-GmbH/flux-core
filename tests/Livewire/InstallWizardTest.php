<?php

use FluxErp\Livewire\Auth\Login;
use FluxErp\Livewire\InstallWizard;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('forbidden when done', function (): void {
    Config::set('flux.install_done', true);

    Livewire::test(InstallWizard::class)
        ->assertForbidden();
});

test('install wizard', function (): void {
    // Set the configuration value
    Config::set('flux.install_done', false);
    Config::set('queue.default', 'sync');

    $component = Livewire::withoutLazyLoading()
        ->test(InstallWizard::class)
        ->assertOk()
        ->call('testDatabaseConnection')
        ->assertHasNoErrors()
        ->assertSet('databaseConnectionSuccessful', true)
        ->assertSet('requestRefresh', true)
        ->call('reload')
        ->assertSet('requestRefresh', false)
        ->call('start')
        ->assertDispatched('batch-id')
        ->assertNotSet('batchId', null)
        ->call('continue')
        ->assertSet('step', 1);

    $this->assertDatabaseHas('job_batches', [
        'id' => $component->get('batchId'),
        'total_jobs' => 8,
        'pending_jobs' => 0,
        'failed_jobs' => 0,
    ]);

    $component->assertSee('Language')
        ->set('languageForm.name', Str::uuid())
        ->set('languageForm.language_code', Str::uuid())
        ->call('continue')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('step', 2);

    $component->assertSee('Currency')
        ->set('currencyForm.name', Str::uuid())
        ->set('currencyForm.iso', Str::uuid())
        ->set('currencyForm.symbol', Str::uuid())
        ->call('continue')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('step', 3);

    $component->assertSee('Tenant')
        ->set('tenantForm.name', Str::uuid())
        ->set('tenantForm.tenant_code', Str::uuid())
        ->set('tenantForm.ceo', Str::uuid())
        ->set('tenantForm.street', Str::uuid())
        ->set('tenantForm.city', Str::uuid())
        ->set('tenantForm.postcode', Str::uuid())
        ->set('tenantForm.phone', Str::uuid())
        ->set('tenantForm.email', Str::uuid() . '@example.com')
        ->set('tenantForm.website', Str::uuid())
        ->call('continue')
        ->assertOk()
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
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('step', 5);

    $component->assertSee('Payment Type')
        ->set('paymentTypeForm.name', Str::uuid())
        ->set('paymentTypeForm.payment_target', 1)
        ->set('paymentTypeForm.payment_reminder_days_1', 2)
        ->set('paymentTypeForm.payment_reminder_days_2', 3)
        ->set('paymentTypeForm.payment_reminder_days_3', 4)
        ->call('continue')
        ->assertOk()
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
        ->assertOk()
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
    $this->assertDatabaseHas('tenants', [
        'name' => $component->get('tenantForm.name'),
        'tenant_code' => $component->get('tenantForm.tenant_code'),
        'ceo' => $component->get('tenantForm.ceo'),
        'street' => $component->get('tenantForm.street'),
        'city' => $component->get('tenantForm.city'),
        'postcode' => $component->get('tenantForm.postcode'),
        'phone' => $component->get('tenantForm.phone'),
        'email' => $component->get('tenantForm.email'),
        'website' => $component->get('tenantForm.website'),
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
});

test('renders successfully', function (): void {
    Config::set('flux.install_done', false);

    Livewire::withoutLazyLoading()
        ->test(InstallWizard::class)
        ->assertOk();
});
