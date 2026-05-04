<?php

use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\RuleEngine\Conditions\CartAmountCondition;
use FluxErp\RuleEngine\Conditions\CartHasCategoryCondition;
use FluxErp\RuleEngine\Conditions\CartHasProductCondition;
use FluxErp\RuleEngine\Conditions\CartLineItemCountCondition;
use FluxErp\RuleEngine\Scopes\OrderScope;

function makeTestOrder(): Order
{
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create();

    return Order::factory()->create([
        'tenant_id' => app(FluxErp\Models\Tenant::class)::default()?->getKey()
            ?? FluxErp\Models\Tenant::factory()->create(['is_default' => true])->getKey(),
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'price_list_id' => PriceList::default()?->getKey()
            ?? PriceList::factory()->create(['is_default' => true])->getKey(),
        'payment_type_id' => PaymentType::default()?->getKey(),
        'currency_id' => Currency::default()?->getKey()
            ?? Currency::factory()->create(['is_default' => true])->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => false,
    ]);
}

function makeOrderScope(array $positions): OrderScope
{
    $order = makeTestOrder();
    $positionCollection = collect(array_map(fn (array $pos) => (object) $pos, $positions));

    return new OrderScope(order: $order, positions: $positionCollection);
}

// --- CartAmountCondition ---

test('cart amount condition matches when total is above threshold', function (): void {
    $condition = new CartAmountCondition();
    $condition->amount = 100.0;
    $condition->operator = '>=';

    $scope = makeOrderScope([
        ['total_net_price' => 60.0, 'product_id' => 1],
        ['total_net_price' => 50.0, 'product_id' => 2],
    ]);

    expect($condition->match($scope))->toBeTrue();
});

test('cart amount condition does not match when total is below threshold', function (): void {
    $condition = new CartAmountCondition();
    $condition->amount = 200.0;
    $condition->operator = '>=';

    $scope = makeOrderScope([
        ['total_net_price' => 60.0, 'product_id' => 1],
        ['total_net_price' => 50.0, 'product_id' => 2],
    ]);

    expect($condition->match($scope))->toBeFalse();
});

test('cart amount condition matches exact amount', function (): void {
    $condition = new CartAmountCondition();
    $condition->amount = 110.0;
    $condition->operator = '=';

    $scope = makeOrderScope([
        ['total_net_price' => 60.0, 'product_id' => 1],
        ['total_net_price' => 50.0, 'product_id' => 2],
    ]);

    expect($condition->match($scope))->toBeTrue();
});

// --- CartLineItemCountCondition ---

test('cart line item count condition matches when count is above threshold', function (): void {
    $condition = new CartLineItemCountCondition();
    $condition->count = 2;
    $condition->operator = '>=';

    $scope = makeOrderScope([
        ['total_net_price' => 60.0, 'product_id' => 1],
        ['total_net_price' => 50.0, 'product_id' => 2],
        ['total_net_price' => 30.0, 'product_id' => 3],
    ]);

    expect($condition->match($scope))->toBeTrue();
});

test('cart line item count condition does not match when count is below threshold', function (): void {
    $condition = new CartLineItemCountCondition();
    $condition->count = 5;
    $condition->operator = '>=';

    $scope = makeOrderScope([
        ['total_net_price' => 60.0, 'product_id' => 1],
    ]);

    expect($condition->match($scope))->toBeFalse();
});

// --- CartHasProductCondition ---

test('cart has product condition matches when product is in cart', function (): void {
    $condition = new CartHasProductCondition();
    $condition->product_ids = [1, 2, 3];
    $condition->operator = 'in';

    $scope = makeOrderScope([
        ['total_net_price' => 60.0, 'product_id' => 2],
    ]);

    expect($condition->match($scope))->toBeTrue();
});

test('cart has product condition does not match when product is not in cart', function (): void {
    $condition = new CartHasProductCondition();
    $condition->product_ids = [99, 100];
    $condition->operator = 'in';

    $scope = makeOrderScope([
        ['total_net_price' => 60.0, 'product_id' => 1],
    ]);

    expect($condition->match($scope))->toBeFalse();
});

test('cart has product condition not_in matches when product is not in cart', function (): void {
    $condition = new CartHasProductCondition();
    $condition->product_ids = [99, 100];
    $condition->operator = 'not_in';

    $scope = makeOrderScope([
        ['total_net_price' => 60.0, 'product_id' => 1],
    ]);

    expect($condition->match($scope))->toBeTrue();
});

// --- CartHasCategoryCondition ---

test('cart has category condition matches when product in cart belongs to category', function (): void {
    $product = Product::factory()->create();
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $product->categories()->attach($category->getKey());

    $condition = new CartHasCategoryCondition();
    $condition->category_ids = [$category->getKey()];
    $condition->operator = 'in';

    $order = makeTestOrder();
    $positions = collect([(object) ['total_net_price' => 50.0, 'product_id' => $product->getKey()]]);
    $scope = new OrderScope(order: $order, positions: $positions);

    expect($condition->match($scope))->toBeTrue();
});

test('cart has category condition does not match when product in cart does not belong to category', function (): void {
    $product = Product::factory()->create();

    $condition = new CartHasCategoryCondition();
    $condition->category_ids = [999999];
    $condition->operator = 'in';

    $order = makeTestOrder();
    $positions = collect([(object) ['total_net_price' => 50.0, 'product_id' => $product->getKey()]]);
    $scope = new OrderScope(order: $order, positions: $positions);

    expect($condition->match($scope))->toBeFalse();
});
