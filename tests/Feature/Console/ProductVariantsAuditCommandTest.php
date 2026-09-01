<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;

function makeWarehouse(): Warehouse
{
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => false,
    ]);

    return Warehouse::factory()->create([
        'address_id' => $address->getKey(),
    ]);
}

test('reports parent products referenced in order_positions', function (): void {
    $parent = Product::factory()->create(['product_number' => 'AUDIT-OP-PARENT']);
    Product::factory()->create(['parent_id' => $parent->getKey()]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $priceList = PriceList::factory()->create();
    $currency = Currency::factory()->create();

    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'product_id' => $parent->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $this->artisan('flux:product-variants:audit')
        ->expectsOutputToContain('order_positions')
        ->assertSuccessful();
});

test('reports parent products referenced in stock_postings', function (): void {
    $parent = Product::factory()->create(['product_number' => 'AUDIT-SP-PARENT']);
    Product::factory()->create(['parent_id' => $parent->getKey()]);

    StockPosting::factory()->create([
        'warehouse_id' => makeWarehouse()->getKey(),
        'product_id' => $parent->getKey(),
    ]);

    $this->artisan('flux:product-variants:audit')
        ->expectsOutputToContain('stock_postings')
        ->assertSuccessful();
});

test('reports parent products with is_active_export_to_web_shop set', function (): void {
    $parent = Product::factory()->create([
        'product_number' => 'AUDIT-WEB-PARENT',
        'is_active_export_to_web_shop' => true,
    ]);
    Product::factory()->create(['parent_id' => $parent->getKey()]);

    $this->artisan('flux:product-variants:audit')
        ->expectsOutputToContain('web_shop_active')
        ->assertSuccessful();
});

test('runs cleanly when no problematic data exists', function (): void {
    $this->artisan('flux:product-variants:audit')
        ->expectsOutputToContain('No issues found')
        ->assertSuccessful();
});

test('csv output writes a file to storage/app and reports the path', function (): void {
    $parent = Product::factory()->create();
    Product::factory()->create(['parent_id' => $parent->getKey()]);
    StockPosting::factory()->create([
        'warehouse_id' => makeWarehouse()->getKey(),
        'product_id' => $parent->getKey(),
    ]);

    $this->artisan('flux:product-variants:audit', ['--output' => 'csv'])
        ->expectsOutputToContain('CSV written to')
        ->assertSuccessful();

    $files = glob(storage_path('app/product-variants-audit-*.csv'));
    expect($files)->not->toBeEmpty();

    $latest = collect($files)->sort()->last();
    $contents = file_get_contents($latest);

    expect($contents)
        ->toContain('parent_id,product_number,reference_type,reference_count')
        ->toContain('stock_postings')
        ->toContain((string) $parent->getKey());

    foreach ($files as $file) {
        @unlink($file);
    }
});
