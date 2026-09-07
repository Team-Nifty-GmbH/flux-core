<?php

use FluxErp\Actions\Printing;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\View\Printing\Order\SupplierOrder;
use Illuminate\Contracts\Support\Htmlable;

function renderSupplierOrder(Order $order): string
{
    $result = Printing::make([
        'model_type' => $order->getMorphClass(),
        'model_id' => $order->getKey(),
        'view' => 'supplier-order',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute();

    return $result instanceof Htmlable ? $result->toHtml() : (string) $result;
}

beforeEach(function (): void {
    $this->dbTenant->update([
        'name' => 'Team Nifty GmbH',
        'street' => 'Musterweg 1',
        'postcode' => '90402',
        'city' => 'Nürnberg',
    ]);

    $supplier = Contact::factory()->create();

    $this->supplierAddress = Address::factory()->create([
        'company' => 'Lieferant AG',
        'street' => 'Zuliefererstraße 9',
        'zip' => '10115',
        'city' => 'Berlin',
        'contact_id' => $supplier->getKey(),
    ]);

    $supplier->update(['main_address_id' => $this->supplierAddress->getKey()]);

    $currency = Currency::query()->where('iso', 'EUR')->first()
        ?? Currency::factory()->create(['iso' => 'EUR', 'is_default' => true]);

    $language = Language::query()->where('language_code', 'de')->first()
        ?? Language::factory()->create(['language_code' => 'de']);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create(['is_default' => false]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $supplier->getKey(),
        'language_id' => $language->getKey(),
        'order_type_id' => OrderType::factory()->create([
            'print_layouts' => ['supplier-order'],
            'order_type_enum' => OrderTypeEnum::Purchase,
        ])->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create(['is_net' => true])->getKey(),
        'currency_id' => $currency->getKey(),
        'address_invoice_id' => $this->supplierAddress->getKey(),
        'address_delivery_id' => null,
        'is_locked' => false,
        'invoice_number' => null,
        'order_number' => 'TEST-2024-002',
        'system_delivery_date' => now()->addWeek(),
        'shipping_costs_net_price' => 0,
    ]);

    $this->order->orderPositions()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'vat_rate_id' => VatRate::factory()->create(['rate_percentage' => 0.19])->getKey(),
        'product_id' => Product::factory()->create()->getKey(),
        'amount' => 1,
        'name' => 'Test Position',
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 100,
        'total_gross_price' => 119,
        'vat_rate_percentage' => 0.19,
        'vat_price' => 19,
        'slug_position' => '00000001',
        'sort_number' => 0,
    ]);

    $this->order->calculatePrices()->save();
});

test('supplier order is the print view of a purchase', function (): void {
    expect(SupplierOrder::class)->toBe(data_get($this->order->resolvePrintViews(), 'supplier-order'));
});

test('supplier order prints the tenant as delivery address', function (): void {
    $this->withoutVite();

    $html = renderSupplierOrder($this->order);

    expect($html)->toContain(__('Delivery Address'))
        ->and($html)->toContain('Musterweg 1')
        ->and($html)->toContain('90402 Nürnberg');
});

test('supplier order still addresses the supplier', function (): void {
    $this->withoutVite();

    $html = renderSupplierOrder($this->order);

    expect($html)->toContain('Lieferant AG')
        ->and($html)->toContain('Zuliefererstraße 9');
});

test('a sales order does not print a delivery address block', function (): void {
    $this->withoutVite();

    $this->order->update([
        'order_type_id' => OrderType::factory()->create([
            'print_layouts' => ['invoice'],
            'order_type_enum' => OrderTypeEnum::Order,
        ])->getKey(),
        'address_delivery_id' => $this->supplierAddress->getKey(),
        'invoice_number' => 'RE-2024-002',
        'invoice_date' => now()->toDateString(),
    ]);

    $result = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->fresh()->getKey(),
        'view' => 'invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute();

    $html = $result instanceof Htmlable ? $result->toHtml() : (string) $result;

    expect($html)->not->toContain(__('Delivery Address'));
});
