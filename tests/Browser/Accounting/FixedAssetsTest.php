<?php

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;

test('picking a purchase order and position prefills the new fixed asset', function (): void {
    $contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => 'Vehicle Supplier ' . uniqid(),
        'is_main_address' => true,
    ]);
    $assetAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Asset,
        'name' => 'Vehicles ' . uniqid(),
    ]);
    $order = Order::factory()->create([
        'order_type_id' => OrderType::factory()
            ->create([
                'order_type_enum' => OrderTypeEnum::Purchase,
                'is_active' => true,
                'name' => 'Purchase ' . uniqid(),
            ])
            ->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'invoice_date' => '2026-03-20',
    ]);
    $orderPosition = OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_id' => $assetAccount->getKey(),
        'name' => 'Delivery van ' . uniqid(),
        'total_net_price' => -36000,
    ]);

    $page = visit(route('accounting.fixed-assets'))
        ->assertNoSmoke();

    waitForCondition($page, '() => window.Livewire !== undefined', 10000);

    $page->click('New');

    waitForElement($page, 'div[x-data*="fixedAsset.order_id"] button[x-ref="button"]', 15000);

    $page->click($this->tsSelect('fixedAsset.order_id'))
        ->click($this->tsSelectOption($order->refresh()->getLabel()));

    waitForCondition($page, <<<JS
        () => Livewire.all().some((component) => component.\$wire.fixedAsset?.order_id === {$order->getKey()})
    JS, 10000);

    $page->click('#fixed-asset-order-position button[x-ref="button"]')
        ->click($this->tsSelectOption($orderPosition->name));

    waitForCondition($page, <<<JS
        () => document.querySelector('div[x-data*="fixedAsset.ledger_account_id"] button[x-ref="button"]')
                ?.innerText.includes('{$assetAccount->name}')
            && document.querySelector('div[x-data*="fixedAsset.contact_id"] button[x-ref="button"]')
                ?.innerText.includes('{$address->company}')
    JS, 10000);

    $page->assertNoJavascriptErrors();
});
