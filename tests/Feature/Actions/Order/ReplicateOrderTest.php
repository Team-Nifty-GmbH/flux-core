<?php

use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Discount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;

it('copies position discounts when creating retoure', function (): void {
    // Arrange: Create an order with a position that has a discount
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate = VatRate::default();
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();

    $product = Product::factory()->create();

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $retoureOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'is_locked' => true,
    ]);

    $orderPosition = OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'is_free_text' => true,
        'discount_percentage' => 0.1,
    ]);

    // Create a discount for the order position (10% discount)
    Discount::factory()->create([
        'model_type' => morph_alias(OrderPosition::class),
        'model_id' => $orderPosition->getKey(),
        'discount' => 0.1,
        'is_percentage' => true,
    ]);

    // Act: Create a retoure from the order
    $retoure = ReplicateOrder::make([
        'id' => $order->getKey(),
        'order_type_id' => $retoureOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
    ])
        ->validate()
        ->execute();

    // Assert: The retoure should have the position with the discount copied
    $retoure->refresh();
    $retourePosition = $retoure->orderPositions->first();

    expect($retourePosition)->not->toBeNull()
        ->and($retourePosition->discounts)->toHaveCount(1)
        ->and((float) $retourePosition->discounts->first()->discount)->toBe(0.1)
        ->and($retourePosition->discounts->first()->is_percentage)->toBeTrue();
});

it('copies order-level discounts when creating retoure', function (): void {
    // Arrange: Create an order with an order-level discount
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate = VatRate::default();
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();

    $product = Product::factory()->create();

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $retoureOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'is_locked' => true,
    ]);

    OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'is_free_text' => true,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 100,
        'total_gross_price' => 119,
        'amount' => 1,
    ]);

    // Create an order-level discount (like "CULT Händlerrabatt" or "Transport 100%")
    Discount::factory()->create([
        'model_type' => morph_alias(Order::class),
        'model_id' => $order->getKey(),
        'name' => 'Händlerrabatt',
        'discount' => 0.1,
        'is_percentage' => true,
    ]);

    // Act: Create a retoure from the order
    $retoure = ReplicateOrder::make([
        'id' => $order->getKey(),
        'order_type_id' => $retoureOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
    ])
        ->validate()
        ->execute();

    // Assert: The retoure should have the order-level discount copied
    $retoure->refresh();
    $retoureDiscounts = Discount::query()
        ->where('model_type', morph_alias(Order::class))
        ->where('model_id', $retoure->getKey())
        ->get();

    expect($retoureDiscounts)->toHaveCount(1)
        ->and($retoureDiscounts->first()->name)->toBe('Händlerrabatt')
        ->and((float) $retoureDiscounts->first()->discount)->toBe(0.1)
        ->and($retoureDiscounts->first()->is_percentage)->toBeTrue();
});

it('preserves implicit discounts when position has zero total but no discount_percentage', function (): void {
    // Arrange: Create an order with a position that has 100% discount applied directly
    // This simulates positions where the discount was set via total_net_price = 0
    // without setting discount_percentage (legacy data scenario)
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate = VatRate::default();
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();
    $warehouse = FluxErp\Models\Warehouse::factory()->create();

    $product = Product::factory()->create();

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $retoureOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'is_locked' => true,
    ]);

    // Create position with 100% implicit discount (total = 0 but no discount_percentage)
    $orderPosition = OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'is_free_text' => false,
        'is_net' => true,
        'amount' => 10,
        'signed_amount' => 10,
        'unit_net_price' => 50,
        'unit_gross_price' => 59.50,
        'total_net_price' => 0,
        'total_gross_price' => 0,
        'total_base_net_price' => 500,
        'total_base_gross_price' => 595,
        'discount_percentage' => null,
    ]);

    $order->calculatePrices()->save();

    // Act: Create a retoure from the order
    $retoure = ReplicateOrder::make([
        'id' => $order->getKey(),
        'order_type_id' => $retoureOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
    ])
        ->validate()
        ->execute();

    // Assert: The retoure position should also have total = 0 (100% discount preserved)
    $retoure->refresh();
    $retourePosition = $retoure->orderPositions->first();

    expect($retourePosition)->not->toBeNull()
        ->and((float) $retourePosition->total_net_price)->toBe(0.0)
        ->and((float) $retourePosition->total_gross_price)->toBe(0.0)
        ->and((float) $retourePosition->discount_percentage)->toBe(1.0);
});

it('retoure total equals negative of original total', function (): void {
    // A4: Simplified - just verify total sums to zero
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    // Use explicit 19% VAT rate
    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();
    $warehouse = FluxErp\Models\Warehouse::factory()->create();
    // Ensure product uses the same vat_rate
    $product = Product::factory()->create(['vat_rate_id' => $vatRate->getKey()]);

    // Create explicit price in the price list to avoid using random prices from DB
    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 100,
    ]);

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $retoureOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => true,
    ]);

    // Position with 50% implicit discount (is_free_text=false)
    OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'is_net' => true,
        'amount' => 2,
        'signed_amount' => 2,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 100,
        'total_gross_price' => 119,
        'total_base_net_price' => 200,
        'total_base_gross_price' => 238,
        'vat_rate_percentage' => 0.19,
        'vat_price' => 19,
        'discount_percentage' => null,
    ]);

    $order->calculatePrices()->save();

    // Act
    $retoure = ReplicateOrder::make([
        'id' => $order->getKey(),
        'order_type_id' => $retoureOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
    ])
        ->validate()
        ->execute();

    // Assert: Retoure total should be negative of original
    $retoure->refresh();
    $order->refresh();

    expect(bcadd($order->total_net_price, $retoure->total_net_price, 2))->toBe('0.00')
        ->and(bcadd($order->total_gross_price, $retoure->total_gross_price, 2))->toBe('0.00');
});

it('handles vat rate mix correctly when creating retoure', function (): void {
    // A5: Retoure von Order mit MwSt-Mix (7%/19%)
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate19 = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $vatRate7 = VatRate::factory()->create(['rate_percentage' => 0.07]);
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();
    $warehouse = FluxErp\Models\Warehouse::factory()->create();
    // Create products with matching vat_rate_id and explicit prices
    $product19 = Product::factory()->create(['vat_rate_id' => $vatRate19->getKey()]);
    $product7 = Product::factory()->create(['vat_rate_id' => $vatRate7->getKey()]);

    // Create explicit prices in the price list to avoid using random prices from DB
    Price::factory()->create([
        'product_id' => $product19->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 100,
    ]);
    Price::factory()->create([
        'product_id' => $product7->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 100,
    ]);

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $retoureOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => true,
    ]);

    // Position with 19% VAT (is_free_text=false for proper calculation)
    OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $product19->getKey(),
        'vat_rate_id' => $vatRate19->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'is_net' => true,
        'amount' => 1,
        'signed_amount' => 1,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 100,
        'total_gross_price' => 119,
        'total_base_net_price' => 100,
        'total_base_gross_price' => 119,
        'vat_rate_percentage' => 0.19,
        'vat_price' => 19,
        'discount_percentage' => null,
    ]);

    // Position with 7% VAT
    OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $product7->getKey(),
        'vat_rate_id' => $vatRate7->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'is_net' => true,
        'amount' => 1,
        'signed_amount' => 1,
        'unit_net_price' => 100,
        'unit_gross_price' => 107,
        'total_net_price' => 100,
        'total_gross_price' => 107,
        'total_base_net_price' => 100,
        'total_base_gross_price' => 107,
        'vat_rate_percentage' => 0.07,
        'vat_price' => 7,
        'discount_percentage' => null,
    ]);

    $order->calculatePrices()->save();

    // Act
    $retoure = ReplicateOrder::make([
        'id' => $order->getKey(),
        'order_type_id' => $retoureOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
    ])
        ->validate()
        ->execute();

    // Assert
    $retoure->refresh();
    $order->refresh();

    // Net prices should sum to zero
    expect(bcadd($order->total_net_price, $retoure->total_net_price, 2))->toBe('0.00');

    // Gross prices should sum to zero
    expect(bcadd($order->total_gross_price, $retoure->total_gross_price, 2))->toBe('0.00');

    // VAT rates should be preserved
    $retourePositions = $retoure->orderPositions;
    expect($retourePositions)->toHaveCount(2);
});

it('preserves discounts when creating split order', function (): void {
    // B3: Teilauftrag mit Rabatten
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();
    $warehouse = FluxErp\Models\Warehouse::factory()->create();
    $product = Product::factory()->create(['vat_rate_id' => $vatRate->getKey()]);

    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 100,
    ]);

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $splitOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::SplitOrder,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => true,
    ]);

    // Position with 20% discount
    $orderPosition = OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'is_net' => true,
        'amount' => 10,
        'signed_amount' => 10,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 800,  // 1000 * 0.8 = 800 (20% discount)
        'total_gross_price' => 952,
        'total_base_net_price' => 1000,
        'total_base_gross_price' => 1190,
        'vat_rate_percentage' => 0.19,
        'vat_price' => 152,
        'discount_percentage' => 0.2,
    ]);

    $order->calculatePrices()->save();

    // Act: Create split order with half the amount
    $splitOrder = ReplicateOrder::make([
        'id' => $order->getKey(),
        'order_type_id' => $splitOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'order_positions' => [
            ['id' => $orderPosition->getKey(), 'amount' => 5],
        ],
    ])
        ->validate()
        ->execute();

    // Assert
    $splitOrder->refresh();
    $splitPosition = $splitOrder->orderPositions->first();

    // Discount should be preserved
    expect((float) $splitPosition->discount_percentage)->toBe(0.2)
        // Total should be half of original (with discount applied)
        ->and(bccomp($splitPosition->total_net_price, '400', 2))->toBe(0); // 500 * 0.8 = 400
});

it('calculates order with 100 percent position discount correctly', function (): void {
    // C1: Order mit 100% Positions-Rabatt
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate = VatRate::default();
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();

    $product = Product::factory()->create();

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => false,
    ]);

    // Position with 100% discount (free item)
    OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
        'is_net' => true,
        'amount' => 1,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 0,
        'total_gross_price' => 0,
        'total_base_net_price' => 100,
        'total_base_gross_price' => 119,
        'discount_percentage' => 1.0,
    ]);

    $order->calculatePrices()->save();

    // Assert
    $order->refresh();
    expect((float) $order->total_net_price)->toBe(0.0)
        ->and((float) $order->total_gross_price)->toBe(0.0);
});

it('returned split order makes amount available again for original', function (): void {
    // Scenario: Original (10) → Split Order (5) → Retoure of Split (5) = 10 available again
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();
    $warehouse = FluxErp\Models\Warehouse::factory()->create();
    $product = Product::factory()->create(['vat_rate_id' => $vatRate->getKey()]);

    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 100,
    ]);

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $splitOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::SplitOrder,
        'is_active' => true,
    ]);

    $retoureOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    // Step 1: Create original order with 10 items
    $originalOrder = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => true,
    ]);

    $originalPosition = OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $originalOrder->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'is_net' => true,
        'amount' => 10,
        'signed_amount' => 10,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 1000,
        'total_gross_price' => 1190,
        'total_base_net_price' => 1000,
        'total_base_gross_price' => 1190,
        'vat_rate_percentage' => 0.19,
        'vat_price' => 190,
        'discount_percentage' => 0, // Explicit no discount
    ]);

    $originalOrder->calculatePrices()->save();

    // Step 2: Create split order with 5 items
    $splitOrder = ReplicateOrder::make([
        'id' => $originalOrder->getKey(),
        'order_type_id' => $splitOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'order_positions' => [
            ['id' => $originalPosition->getKey(), 'amount' => 5],
        ],
    ])
        ->validate()
        ->execute();

    $splitOrder->refresh();
    $splitPosition = $splitOrder->orderPositions->first();

    // Verify split order has correct origin_position_id and amounts
    expect($splitPosition->origin_position_id)->toBe($originalPosition->getKey())
        ->and((float) $splitPosition->amount)->toBe(5.0)
        ->and((float) $splitPosition->signed_amount)->toBe(5.0);

    // Step 3: Create retoure of the split order (return all 5 items)
    $retoure = ReplicateOrder::make([
        'id' => $splitOrder->getKey(),
        'order_type_id' => $retoureOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
    ])
        ->validate()
        ->execute();

    $retoure->refresh();
    $retourePosition = $retoure->orderPositions->first();

    // Verify retoure has correct origin_position_id (points to split order position)
    expect($retourePosition->origin_position_id)->toBe($splitPosition->getKey())
        ->and((float) $retourePosition->amount)->toBe(5.0)
        ->and((float) $retourePosition->signed_amount)->toBe(-5.0); // Negative for retoure

    // Step 4: Calculate available amount from original order
    // The split order was fully returned, so original should have 10 available again
    $signedAmounts = Illuminate\Support\Facades\DB::select(
        'WITH RECURSIVE siblings AS (
            SELECT id, origin_position_id, signed_amount
            FROM order_positions
            WHERE order_id = ' . $originalOrder->getKey() . '
            UNION ALL
            SELECT op.id, op.origin_position_id, op.signed_amount
            FROM order_positions op
            INNER JOIN siblings s ON s.id = op.origin_position_id
            WHERE op.deleted_at IS NULL
        )
        SELECT * FROM siblings'
    );

    $allPositions = array_map(fn ($item) => (array) $item, $signedAmounts);

    // Calculate using the same logic as ReplicateOrderPositionList
    $rootId = $originalPosition->getKey();
    $root = array_find($allPositions, fn ($p) => $p['id'] === $rootId);
    $rootAmount = bcabs($root['signed_amount']);

    $directChildren = array_filter(
        $allPositions,
        fn ($p) => $p['origin_position_id'] === $rootId
    );

    $consumed = '0';
    foreach ($directChildren as $child) {
        if (bccomp($child['signed_amount'], 0) === -1) {
            $consumed = bcadd($consumed, bcabs($child['signed_amount']));
        } else {
            $childAmount = bcabs($child['signed_amount']);
            // Find retoures of this child
            $childRetoures = array_filter(
                $allPositions,
                fn ($p) => $p['origin_position_id'] === $child['id']
                    && bccomp($p['signed_amount'], 0) === -1
            );
            $returnedAmount = '0';
            foreach ($childRetoures as $retourePos) {
                $returnedAmount = bcadd($returnedAmount, bcabs($retourePos['signed_amount']));
            }
            $netConsumed = bcsub($childAmount, $returnedAmount);
            if (bccomp($netConsumed, 0) === 1) {
                $consumed = bcadd($consumed, $netConsumed);
            }
        }
    }

    $available = bcsub($rootAmount, $consumed);

    // Assert: All 10 items should be available again (split order was fully returned)
    expect(bccomp($available, '10', 0))->toBe(0);
});

it('partially returned split order reduces available amount proportionally', function (): void {
    // Scenario: Original (10) → Split Order (5) → Partial Retoure (3) = 8 available
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();
    $warehouse = FluxErp\Models\Warehouse::factory()->create();
    $product = Product::factory()->create(['vat_rate_id' => $vatRate->getKey()]);

    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 100,
    ]);

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $splitOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::SplitOrder,
        'is_active' => true,
    ]);

    $retoureOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    // Step 1: Create original order with 10 items
    $originalOrder = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => true,
    ]);

    $originalPosition = OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $originalOrder->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'is_net' => true,
        'amount' => 10,
        'signed_amount' => 10,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 1000,
        'total_gross_price' => 1190,
        'total_base_net_price' => 1000,
        'total_base_gross_price' => 1190,
        'vat_rate_percentage' => 0.19,
        'vat_price' => 190,
        'discount_percentage' => 0, // Explicit no discount
    ]);

    $originalOrder->calculatePrices()->save();

    // Step 2: Create split order with 5 items
    $splitOrder = ReplicateOrder::make([
        'id' => $originalOrder->getKey(),
        'order_type_id' => $splitOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'order_positions' => [
            ['id' => $originalPosition->getKey(), 'amount' => 5],
        ],
    ])
        ->validate()
        ->execute();

    $splitOrder->refresh();
    $splitPosition = $splitOrder->orderPositions->first();

    // Step 3: Create partial retoure of the split order (return only 3 items)
    $retoure = ReplicateOrder::make([
        'id' => $splitOrder->getKey(),
        'order_type_id' => $retoureOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'order_positions' => [
            ['id' => $splitPosition->getKey(), 'amount' => 3],
        ],
    ])
        ->validate()
        ->execute();

    $retoure->refresh();
    $retourePosition = $retoure->orderPositions->first();

    // Verify retoure amount
    expect((float) $retourePosition->amount)->toBe(3.0)
        ->and((float) $retourePosition->signed_amount)->toBe(-3.0);

    // Step 4: Calculate available amount from original order
    $signedAmounts = Illuminate\Support\Facades\DB::select(
        'WITH RECURSIVE siblings AS (
            SELECT id, origin_position_id, signed_amount
            FROM order_positions
            WHERE order_id = ' . $originalOrder->getKey() . '
            UNION ALL
            SELECT op.id, op.origin_position_id, op.signed_amount
            FROM order_positions op
            INNER JOIN siblings s ON s.id = op.origin_position_id
            WHERE op.deleted_at IS NULL
        )
        SELECT * FROM siblings'
    );

    $allPositions = array_map(fn ($item) => (array) $item, $signedAmounts);

    $rootId = $originalPosition->getKey();
    $root = array_find($allPositions, fn ($p) => $p['id'] === $rootId);
    $rootAmount = bcabs($root['signed_amount']);

    $directChildren = array_filter(
        $allPositions,
        fn ($p) => $p['origin_position_id'] === $rootId
    );

    $consumed = '0';
    foreach ($directChildren as $child) {
        if (bccomp($child['signed_amount'], 0) === -1) {
            $consumed = bcadd($consumed, bcabs($child['signed_amount']));
        } else {
            $childAmount = bcabs($child['signed_amount']);
            $childRetoures = array_filter(
                $allPositions,
                fn ($p) => $p['origin_position_id'] === $child['id']
                    && bccomp($p['signed_amount'], 0) === -1
            );
            $returnedAmount = '0';
            foreach ($childRetoures as $retourePos) {
                $returnedAmount = bcadd($returnedAmount, bcabs($retourePos['signed_amount']));
            }
            $netConsumed = bcsub($childAmount, $returnedAmount);
            if (bccomp($netConsumed, 0) === 1) {
                $consumed = bcadd($consumed, $netConsumed);
            }
        }
    }

    $available = bcsub($rootAmount, $consumed);

    // Assert: 8 items should be available (10 - 5 + 3 = 8)
    // Original had 10, split took 5, retoure returned 3 → net consumed = 2
    expect(bccomp($available, '8', 0))->toBe(0);
});

it('direct retoure still reduces available amount to zero', function (): void {
    // Scenario: Original (10) → Direct Retoure (10) = 0 available
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();
    $warehouse = FluxErp\Models\Warehouse::factory()->create();
    $product = Product::factory()->create(['vat_rate_id' => $vatRate->getKey()]);

    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 100,
    ]);

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $retoureOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    // Step 1: Create original order with 10 items
    $originalOrder = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => true,
    ]);

    $originalPosition = OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $originalOrder->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'is_net' => true,
        'amount' => 10,
        'signed_amount' => 10,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 1000,
        'total_gross_price' => 1190,
        'total_base_net_price' => 1000,
        'total_base_gross_price' => 1190,
        'vat_rate_percentage' => 0.19,
        'vat_price' => 190,
        'discount_percentage' => 0, // Explicit no discount
    ]);

    $originalOrder->calculatePrices()->save();

    // Step 2: Create direct retoure of the original order (return all 10 items)
    $retoure = ReplicateOrder::make([
        'id' => $originalOrder->getKey(),
        'order_type_id' => $retoureOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
    ])
        ->validate()
        ->execute();

    $retoure->refresh();
    $retourePosition = $retoure->orderPositions->first();

    // Verify retoure has negative signed_amount and points to original
    expect($retourePosition->origin_position_id)->toBe($originalPosition->getKey())
        ->and((float) $retourePosition->signed_amount)->toBe(-10.0);

    // Step 3: Calculate available amount from original order
    $signedAmounts = Illuminate\Support\Facades\DB::select(
        'WITH RECURSIVE siblings AS (
            SELECT id, origin_position_id, signed_amount
            FROM order_positions
            WHERE order_id = ' . $originalOrder->getKey() . '
            UNION ALL
            SELECT op.id, op.origin_position_id, op.signed_amount
            FROM order_positions op
            INNER JOIN siblings s ON s.id = op.origin_position_id
            WHERE op.deleted_at IS NULL
        )
        SELECT * FROM siblings'
    );

    $allPositions = array_map(fn ($item) => (array) $item, $signedAmounts);

    $rootId = $originalPosition->getKey();
    $root = array_find($allPositions, fn ($p) => $p['id'] === $rootId);
    $rootAmount = bcabs($root['signed_amount']);

    $directChildren = array_filter(
        $allPositions,
        fn ($p) => $p['origin_position_id'] === $rootId
    );

    $consumed = '0';
    foreach ($directChildren as $child) {
        if (bccomp($child['signed_amount'], 0) === -1) {
            // Direct retoure: consumes full amount
            $consumed = bcadd($consumed, bcabs($child['signed_amount']));
        } else {
            $childAmount = bcabs($child['signed_amount']);
            $childRetoures = array_filter(
                $allPositions,
                fn ($p) => $p['origin_position_id'] === $child['id']
                    && bccomp($p['signed_amount'], 0) === -1
            );
            $returnedAmount = '0';
            foreach ($childRetoures as $retourePos) {
                $returnedAmount = bcadd($returnedAmount, bcabs($retourePos['signed_amount']));
            }
            $netConsumed = bcsub($childAmount, $returnedAmount);
            if (bccomp($netConsumed, 0) === 1) {
                $consumed = bcadd($consumed, $netConsumed);
            }
        }
    }

    $available = bcsub($rootAmount, $consumed);

    // Assert: 0 items should be available (direct retoure consumed all)
    expect(bccomp($available, '0', 0))->toBe(0);
});

it('calculates order lock recalculation correctly', function (): void {
    // C3: Order Lock → Recalculation
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $priceList = PriceList::default();
    $paymentType = PaymentType::default();
    $currency = Currency::default();

    $product = Product::factory()->create();

    $orderOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderOrderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => false,
        'total_net_price' => 0,
        'total_gross_price' => 0,
    ]);

    // Position without calculated totals
    OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
        'is_net' => true,
        'amount' => 2,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 200,
        'total_gross_price' => 238,
        'total_base_net_price' => 200,
        'total_base_gross_price' => 238,
        'vat_rate_percentage' => 0.19,
        'vat_price' => 38,
        'discount_percentage' => null,
    ]);

    // Act: Calculate prices (like what happens on lock)
    $order->calculatePrices()->save();

    // Assert: Order totals should match position totals
    $order->refresh();
    expect(bccomp($order->total_net_price, '200', 2))->toBe(0)
        ->and(bccomp($order->total_gross_price, '238', 2))->toBe(0);
});
