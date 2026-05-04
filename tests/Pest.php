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
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;

if ($auditLocale = env('TRANSLATION_AUDIT_LOCALE')) {
    require_once __DIR__ . '/Support/TranslationAuditCollector.php';
    FluxErp\Tests\Support\TranslationAuditCollector::boot($auditLocale);

    pest()->beforeEach(function () use ($auditLocale): void {
        app()->setLocale($auditLocale);
        app('translator')->handleMissingKeysUsing(function (string $key): void {
            FluxErp\Tests\Support\TranslationAuditCollector::record($key);
        });
    });
}

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

/**
 * Wait for a DataTable to render at least one row.
 */
function waitForDataTable(PendingAwaitablePage|AwaitableWebpage $page): PendingAwaitablePage|AwaitableWebpage
{
    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('DataTable did not render')), 10000);
            const check = () => {
                if (document.querySelectorAll('tbody tr').length > 0) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    return $page;
}

/**
 * Click a tab whose text matches one of the provided labels.
 */
function clickTab(PendingAwaitablePage|AwaitableWebpage $page, string ...$labels): PendingAwaitablePage|AwaitableWebpage
{
    $labelsJson = json_encode($labels);

    $page->script(<<<JS
        () => {
            const labels = {$labelsJson};
            const tabs = document.querySelectorAll('[wire\\\\:click*="tab"]');
            for (const tab of tabs) {
                for (const label of labels) {
                    if (tab.textContent?.includes(label)) {
                        tab.click();
                        return;
                    }
                }
            }
        }
    JS);

    return $page;
}

/**
 * Click a create/new button on the page.
 */
function clickCreateButton(PendingAwaitablePage|AwaitableWebpage $page): PendingAwaitablePage|AwaitableWebpage
{
    $page->script(<<<'JS'
        () => {
            const btn = Array.from(document.querySelectorAll('button, a'))
                .find(b => {
                    const text = b.textContent?.trim();
                    return text?.includes('Create') || text?.includes('Neu') || text?.includes('New');
                });
            if (btn) btn.click();
        }
    JS);

    return $page;
}

/**
 * Wait for an element to appear in the DOM and be visible.
 */
function waitForElement(PendingAwaitablePage|AwaitableWebpage $page, string $selector, int $timeout = 5000): PendingAwaitablePage|AwaitableWebpage
{
    $page->script(<<<JS
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Element not found: {$selector}')), {$timeout});
            const check = () => {
                const el = document.querySelector('{$selector}');
                if (el && el.offsetParent !== null) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    return $page;
}

/**
 * Wait until a JavaScript condition (an arrow function returning truthy/falsy)
 * resolves to a truthy value. Use this instead of fixed `wait(N)` calls so
 * tests don't sleep longer than necessary and don't flake on slower runs.
 */
function waitForCondition(PendingAwaitablePage|AwaitableWebpage $page, string $condition, int $timeout = 5000): PendingAwaitablePage|AwaitableWebpage
{
    $page->script(<<<JS
        () => new Promise((resolve, reject) => {
            const condition = {$condition};
            const deadline = Date.now() + {$timeout};
            const check = () => {
                try {
                    if (condition()) return resolve();
                } catch (e) { /* keep retrying until deadline */ }
                if (Date.now() > deadline) {
                    return reject(new Error('Condition did not become truthy within {$timeout}ms'));
                }
                setTimeout(check, 100);
            };
            check();
        })
    JS);

    return $page;
}
