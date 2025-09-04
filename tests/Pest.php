<?php

use FluxErp\Models\Client;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Tests\BrowserTestCase;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\ArrayablePendingAwaitablePage;
use Pest\Browser\Api\PendingAwaitablePage;

uses(FluxErp\Tests\Livewire\BaseSetup::class)
    ->in('Livewire');

uses(BrowserTestCase::class)
    ->beforeAll(function (): void {
        BrowserTestCase::installAssets();
    })
    ->beforeEach(function (): void {
        config([
            'app.debug' => true,
        ]);

        PriceList::default() ?? PriceList::factory()->create([
            'is_default' => true,
        ]);

        $client = Client::default() ?? Client::factory()->create([
            'is_default' => true,
        ]);

        $language = Language::default() ?? Language::factory()->create([
            'is_default' => true,
        ]);

        PaymentType::default() ?? PaymentType::factory()
            ->hasAttached($client, relationship: 'clients')
            ->create([
                'is_active' => true,
                'is_default' => true,
                'is_sales' => true,
            ]);

        Currency::default() ?? Currency::factory()->create([
            'is_default' => true,
        ]);

        $this->user = User::factory()->create([
            'is_active' => true,
            'language_id' => $language->getKey(),
        ]);

        $this->actingAs($this->user);

        $this->dbClient = $client;
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
