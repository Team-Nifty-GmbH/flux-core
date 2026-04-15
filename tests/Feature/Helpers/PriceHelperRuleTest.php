<?php

use FluxErp\Actions\Price\CreatePrice;
use FluxErp\Actions\RuleCondition\CreateRuleCondition;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\Rule;

beforeEach(function (): void {
    $this->priceList = PriceList::factory()->create(['is_default' => true, 'is_net' => true]);
    $this->product = Product::factory()->create();
});

test('price helper returns unconditional price when no rules match', function (): void {
    CreatePrice::make([
        'price_list_id' => $this->priceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 100,
    ])->validate()->execute();

    $rule = Rule::factory()->create(['is_active' => false, 'priority' => 10]);
    CreatePrice::make([
        'price_list_id' => $this->priceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 80,
        'rule_id' => $rule->getKey(),
    ])->validate()->execute();

    $price = PriceHelper::make($this->product)
        ->setPriceList($this->priceList)
        ->price();

    expect((float) $price->price)->toBe(100.0);
});

test('price helper returns conditional price when rule matches', function (): void {
    CreatePrice::make([
        'price_list_id' => $this->priceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 100,
    ])->validate()->execute();

    $rule = Rule::factory()->create(['is_active' => true, 'priority' => 10]);
    $orContainer = CreateRuleCondition::make([
        'rule_id' => $rule->getKey(),
        'type' => 'or_container',
    ])->validate()->execute();
    $andContainer = CreateRuleCondition::make([
        'rule_id' => $rule->getKey(),
        'parent_id' => $orContainer->getKey(),
        'type' => 'and_container',
    ])->validate()->execute();
    CreateRuleCondition::make([
        'rule_id' => $rule->getKey(),
        'parent_id' => $andContainer->getKey(),
        'type' => 'date_range',
        'value' => [
            'from' => now()->subMonth()->format('Y-m-d'),
            'to' => now()->addMonth()->format('Y-m-d'),
        ],
    ])->validate()->execute();

    CreatePrice::make([
        'price_list_id' => $this->priceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 80,
        'rule_id' => $rule->getKey(),
    ])->validate()->execute();

    $price = PriceHelper::make($this->product)
        ->setPriceList($this->priceList)
        ->price();

    expect((float) $price->price)->toBe(80.0);
});

test('price helper respects rule priority when multiple rules match', function (): void {
    CreatePrice::make([
        'price_list_id' => $this->priceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 100,
    ])->validate()->execute();

    $lowRule = Rule::factory()->create(['is_active' => true, 'priority' => 5]);
    CreatePrice::make([
        'price_list_id' => $this->priceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 90,
        'rule_id' => $lowRule->getKey(),
    ])->validate()->execute();

    $highRule = Rule::factory()->create(['is_active' => true, 'priority' => 10]);
    CreatePrice::make([
        'price_list_id' => $this->priceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 75,
        'rule_id' => $highRule->getKey(),
    ])->validate()->execute();

    $price = PriceHelper::make($this->product)
        ->setPriceList($this->priceList)
        ->price();

    expect((float) $price->price)->toBe(75.0);
});

test('price helper supports quantity for line item quantity condition', function (): void {
    CreatePrice::make([
        'price_list_id' => $this->priceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 100,
    ])->validate()->execute();

    $rule = Rule::factory()->create(['is_active' => true, 'priority' => 10]);
    $orContainer = CreateRuleCondition::make([
        'rule_id' => $rule->getKey(),
        'type' => 'or_container',
    ])->validate()->execute();
    $andContainer = CreateRuleCondition::make([
        'rule_id' => $rule->getKey(),
        'parent_id' => $orContainer->getKey(),
        'type' => 'and_container',
    ])->validate()->execute();
    CreateRuleCondition::make([
        'rule_id' => $rule->getKey(),
        'parent_id' => $andContainer->getKey(),
        'type' => 'line_item_quantity',
        'value' => ['quantity' => 10, 'operator' => '>='],
    ])->validate()->execute();

    CreatePrice::make([
        'price_list_id' => $this->priceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 80,
        'rule_id' => $rule->getKey(),
    ])->validate()->execute();

    // Without sufficient quantity
    $price = PriceHelper::make($this->product)
        ->setPriceList($this->priceList)
        ->setQuantity(5)
        ->price();
    expect((float) $price->price)->toBe(100.0);

    // With sufficient quantity
    $price = PriceHelper::make($this->product)
        ->setPriceList($this->priceList)
        ->setQuantity(15)
        ->price();
    expect((float) $price->price)->toBe(80.0);
});
