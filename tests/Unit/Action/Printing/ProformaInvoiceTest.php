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
use FluxErp\View\Printing\Order\ProformaInvoice;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Number;

function proformaInvoiceTotalValue(string $html): string
{
    $pattern = '/' . preg_quote(__('Total value'), '/') . '<\/td>\s*<td[^>]*>\s*([^<]+?)\s*<\/td>/s';

    expect(preg_match($pattern, $html, $matches))->toBe(1);

    return str_replace("\u{00A0}", ' ', $matches[1]);
}

beforeEach(function (): void {
    $contact = Contact::factory()->create();

    $address = Address::factory()->create([
        'company' => 'Test Company GmbH',
        'contact_id' => $contact->getKey(),
    ]);

    $contact->update(['main_address_id' => $address->getKey()]);

    $priceList = PriceList::factory()->create(['is_net' => true]);

    $currency = Currency::query()->where('iso', 'EUR')->first()
        ?? Currency::factory()->create([
            'iso' => 'EUR',
            'is_default' => true,
        ]);

    $language = Language::query()->where('language_code', 'de')->first()
        ?? Language::factory()->create(['language_code' => 'de']);

    $orderType = OrderType::factory()
        ->create([
            'print_layouts' => ['proforma-invoice'],
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create([
            'is_default' => false,
        ]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $language->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'currency_id' => $currency->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'is_locked' => false,
        'invoice_number' => null,
        'order_number' => 'TEST-2024-001',
        'system_delivery_date' => now()->addWeek(),
        'shipping_costs_net_price' => 0,
    ]);

    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);

    $this->order->orderPositions()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
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

test('proforma invoice is available as print view', function (): void {
    expect(app(Order::class)->getPrintViews())
        ->toHaveKey('proforma-invoice')
        ->and(data_get(app(Order::class)->getPrintViews(), 'proforma-invoice'))
        ->toBe(ProformaInvoice::class);

    expect(array_keys($this->order->resolvePrintViews()))->toContain('proforma-invoice');
});

test('can render proforma invoice html', function (): void {
    $this->withoutVite();

    $result = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'proforma-invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute();

    expect($result)->toBeInstanceOf(Htmlable::class);

    $html = $result->toHtml();

    $this->assertStringContainsString('TEST-2024-001', $html);
    $this->assertStringContainsString('Test Company GmbH', $html);
    $this->assertStringContainsString('Test Position', $html);
});

test('proforma invoice is marked as such and denies input tax deduction', function (): void {
    $this->withoutVite();

    $html = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'proforma-invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    $this->assertStringContainsString(__('Proforma Invoice'), $html);
    $this->assertStringContainsString(
        __('This document is not an invoice and does not entitle to input tax deduction.'),
        $html
    );
});

test('proforma invoice does not show a separate tax amount or rate', function (): void {
    $this->withoutVite();

    $html = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'proforma-invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    $this->assertStringNotContainsString(__('Total Gross'), $html);
    $this->assertStringNotContainsString('19,00', $html);
    $this->assertStringNotContainsString(Number::percentage(19, maxPrecision: 2), $html);
    expect(proformaInvoiceTotalValue($html))->toBe('100,00 €');
});

test('proforma invoice shows the gross total value for a gross price list', function (): void {
    $this->withoutVite();

    $this->order
        ->priceList()
        ->associate(PriceList::factory()->create(['is_net' => false]));
    $this->order->save();

    $html = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'proforma-invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    expect(proformaInvoiceTotalValue($html))->toBe('119,00 €');
});

test('an order level discount shows as a percentage without any amount', function (): void {
    $this->withoutVite();

    $this->order->discounts()->create([
        'discount' => 0.1,
        'is_percentage' => true,
    ]);
    $this->order->calculatePrices()->save();

    $html = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'proforma-invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    $this->assertStringContainsString(__('Discount'), $html);
    $this->assertStringContainsString(Number::percentage(10, maxPrecision: 2), $html);
    $this->assertStringNotContainsString('10,00', $html);
    expect(proformaInvoiceTotalValue($html))->toBe('90,00 €');
});

test('proforma invoice does not show alternative positions', function (): void {
    $this->withoutVite();

    $this->order->orderPositions()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'product_id' => Product::factory()->create()->getKey(),
        'amount' => 1,
        'name' => 'Alternative Position',
        'unit_net_price' => 500,
        'total_net_price' => 500,
        'is_alternative' => true,
        'slug_position' => '00000002',
        'sort_number' => 1,
    ]);

    $html = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'proforma-invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    $this->assertStringNotContainsString('Alternative Position', $html);
    expect(proformaInvoiceTotalValue($html))->toBe('100,00 €');
});

test('proforma invoice shows the expected delivery date', function (): void {
    $this->withoutVite();

    $html = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'proforma-invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    $this->assertStringContainsString(__('Expected Delivery Date'), $html);
    $this->assertStringContainsString(
        $this->order->system_delivery_date->locale('de')->isoFormat('L'),
        $html
    );
});

test('proforma invoice states an undetermined delivery date instead of falling back to today', function (): void {
    $this->withoutVite();

    $this->order->update(['system_delivery_date' => null]);

    $html = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'proforma-invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    $this->assertStringContainsString(__('Not yet determined'), $html);
    $this->assertStringNotContainsString(now()->locale('de')->isoFormat('L'), $html);
});

test('printing a proforma invoice neither assigns an invoice number nor locks the order', function (): void {
    $this->withoutVite();

    $state = $this->order->state;

    Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'proforma-invoice',
        'preview' => false,
        'html' => false,
    ])
        ->validate()
        ->execute();

    $this->order->refresh();

    expect($this->order->invoice_number)->toBeNull()
        ->and($this->order->invoice_date)->toBeNull()
        ->and($this->order->is_locked)->toBeFalse()
        ->and($this->order->state->equals($state))->toBeTrue()
        ->and($this->order->getMedia('proforma-invoice'))->toBeEmpty();
});

test('proforma invoice is not an invoice', function (): void {
    expect(resolve_static(ProformaInvoice::class, 'isInvoice'))->toBeFalse();
});

test('get subject returns the order number', function (): void {
    $view = new ProformaInvoice($this->order);

    expect($view->getSubject())
        ->toContain(__('Proforma Invoice'))
        ->toContain('TEST-2024-001');
});
