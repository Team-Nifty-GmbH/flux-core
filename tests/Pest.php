<?php

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Tests\BrowserTestCase;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\ArrayablePendingAwaitablePage;
use Pest\Browser\Api\PendingAwaitablePage;

pest()
    ->beforeEach(function (): void {
        $this->dbClient = Client::default() ?? Client::factory()->create([
            'is_default' => true,
        ]);
        $this->defaultLanguage = Language::default() ?? Language::factory()->create([
            'is_default' => true,
        ]);

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);
        $this->address = Address::factory()->create([
            'contact_id' => $this->contact->getKey(),
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'can_login' => true,
            'is_active' => true,
        ]);

        $this->actingAsGuest('web');
        $this->be($this->address, 'address');
    })
    ->group('Livewire/Portal')
    ->in('Livewire/Portal', 'Feature/Web/Portal');

pest()
    ->beforeEach(function (): void {
        /** @var $this FluxErp\Tests\TestCase */
        config([
            'app.debug' => true,
        ]);

        PriceList::default() ?? PriceList::factory()->create([
            'is_default' => true,
        ]);

        $this->dbClient = Client::default() ?? Client::factory()->create([
            'is_default' => true,
        ]);

        $this->defaultLanguage = Language::default() ?? Language::factory()->create([
            'is_default' => true,
        ]);

        VatRate::default() ?? VatRate::factory()->create([
            'is_default' => true,
        ]);

        PaymentType::default() ?? PaymentType::factory()
            ->hasAttached($this->dbClient, relationship: 'clients')
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
    ->in('Livewire');

pest()
    ->extend(FluxErp\Tests\TestCase::class)
    ->in('Unit');

pest()
    ->extend(FluxErp\Tests\TestCase::class)
    ->in('Feature');

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
