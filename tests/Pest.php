<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Settings\CoreSettings;
use FluxErp\Tests\BrowserTestCase;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\ArrayablePendingAwaitablePage;
use Pest\Browser\Api\PendingAwaitablePage;

pest()
    ->beforeEach(function (): void {
        $this->dbTenant = Tenant::default() ?? Tenant::factory()->create([
            'is_default' => true,
        ]);
        $this->defaultLanguage = Language::default() ?? Language::factory()->create([
            'is_default' => true,
        ]);

        $this->contact = Contact::factory()->create([
            'tenant_id' => $this->dbTenant->getKey(),
        ]);
        $this->address = Address::factory()->create([
            'contact_id' => $this->contact->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'can_login' => true,
            'is_active' => true,
        ]);

        $this->actingAsGuest('web');
        $this->be($this->address, 'address');
    })
    ->group('portal')
    ->in('Livewire/Portal', 'Feature/Web/Portal');

pest()
    ->beforeEach(function (): void {
        /** @var $this FluxErp\Tests\TestCase */
        config([
            'app.debug' => true,
        ]);

        CoreSettings::fake([
            'install_done' => false,
            'license_key' => null,
            'formal_salutation' => false,
        ]);

        PriceList::default() ?? PriceList::factory()->create([
            'is_default' => true,
        ]);

        $this->dbTenant = Tenant::default() ?? Tenant::factory()->create([
            'is_default' => true,
        ]);

        $this->defaultLanguage = Language::default() ?? Language::factory()->create([
            'is_default' => true,
        ]);

        VatRate::default() ?? VatRate::factory()->create([
            'is_default' => true,
        ]);

        PaymentType::default() ?? PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create([
                'is_active' => true,
                'is_default' => true,
                'is_sales' => true,
            ]);

        Currency::default() ?? Currency::factory()->create([
            'is_default' => true,
        ]);

        $this->user = User::factory()
            ->create([
                'is_active' => true,
                'language_id' => $this->defaultLanguage->getKey(),
            ]);

        if (! auth()->user()) {
            $this->be($this->user, 'web');
        }
    });

pest()
    ->extend(FluxErp\Tests\TestCase::class)
    ->beforeEach(function (): void {
        $this->withoutVite();
    })
    ->in('Livewire', 'Feature', 'Unit');

pest()
    ->extend(BrowserTestCase::class)
    ->beforeAll(function (): void {
        BrowserTestCase::installAssets();
    })
    ->in('Browser');

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/
function visitLivewire(string $component, array $options = []): ArrayablePendingAwaitablePage|PendingAwaitablePage
{
    Route::get($uri = '/livewire-test/' . uniqid(), $component);

    return visit($uri, $options);
}
