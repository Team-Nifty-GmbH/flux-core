<?php

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
        // Sail/Docker sets PLAYWRIGHT_BROWSERS_PATH=0 which means "local to project" for
        // npm install, but Playwright's run-server interprets 0 as an invalid path prefix.
        // This override works when running tests on the host (outside Docker).
        // Inside Docker, pass the env var via shell: PLAYWRIGHT_BROWSERS_PATH=... vendor/bin/testbench
        $browsersPath = dirname(__DIR__) . '/node_modules/playwright-core/.local-browsers';
        if (is_dir($browsersPath)) {
            putenv('PLAYWRIGHT_BROWSERS_PATH=' . $browsersPath);
            $_ENV['PLAYWRIGHT_BROWSERS_PATH'] = $browsersPath;
            $_SERVER['PLAYWRIGHT_BROWSERS_PATH'] = $browsersPath;
        }

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
