<?php

use FluxErp\Models\Category;
use FluxErp\Models\Product;
use FluxErp\RuleEngine\Conditions\LineItemQuantityCondition;
use FluxErp\RuleEngine\Conditions\ProductCategoryCondition;
use FluxErp\RuleEngine\Conditions\ProductCustomFieldCondition;
use FluxErp\RuleEngine\Scopes\PriceScope;

// --- LineItemQuantityCondition ---

test('line item quantity condition matches when quantity meets threshold', function (): void {
    $condition = new LineItemQuantityCondition();
    $condition->quantity = 5;
    $condition->operator = '>=';

    $scope = new PriceScope(quantity: 10);

    expect($condition->match($scope))->toBeTrue();
});

test('line item quantity condition does not match when quantity is below threshold', function (): void {
    $condition = new LineItemQuantityCondition();
    $condition->quantity = 10;
    $condition->operator = '>=';

    $scope = new PriceScope(quantity: 5);

    expect($condition->match($scope))->toBeFalse();
});

test('line item quantity condition returns false when quantity is null', function (): void {
    $condition = new LineItemQuantityCondition();
    $condition->quantity = 1;
    $condition->operator = '>=';

    $scope = new PriceScope(quantity: null);

    expect($condition->match($scope))->toBeFalse();
});

// --- ProductCategoryCondition ---

test('product category condition matches when product is in category', function (): void {
    $product = Product::factory()->create();
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $product->categories()->attach($category->getKey());

    $condition = new ProductCategoryCondition();
    $condition->category_ids = [$category->getKey()];
    $condition->operator = 'in';

    $scope = new PriceScope(product: $product->fresh());

    expect($condition->match($scope))->toBeTrue();
});

test('product category condition does not match when product is not in category', function (): void {
    $product = Product::factory()->create();

    $condition = new ProductCategoryCondition();
    $condition->category_ids = [999999];
    $condition->operator = 'in';

    $scope = new PriceScope(product: $product);

    expect($condition->match($scope))->toBeFalse();
});

test('product category condition not_in matches when product is not in category', function (): void {
    $product = Product::factory()->create();

    $condition = new ProductCategoryCondition();
    $condition->category_ids = [999999];
    $condition->operator = 'not_in';

    $scope = new PriceScope(product: $product);

    expect($condition->match($scope))->toBeTrue();
});

test('product category condition returns false when no product in scope', function (): void {
    $condition = new ProductCategoryCondition();
    $condition->category_ids = [1];
    $condition->operator = 'in';

    $scope = new PriceScope();

    expect($condition->match($scope))->toBeFalse();
});

// --- ProductCustomFieldCondition ---

test('product custom field condition matches when field equals value', function (): void {
    $product = Product::factory()->create(['name' => 'Test Product']);

    $condition = new ProductCustomFieldCondition();
    $condition->field = 'name';
    $condition->operator = '=';
    $condition->value = 'Test Product';

    $scope = new PriceScope(product: $product);

    expect($condition->match($scope))->toBeTrue();
});

test('product custom field condition does not match when field does not equal value', function (): void {
    $product = Product::factory()->create(['name' => 'Test Product']);

    $condition = new ProductCustomFieldCondition();
    $condition->field = 'name';
    $condition->operator = '=';
    $condition->value = 'Other Product';

    $scope = new PriceScope(product: $product);

    expect($condition->match($scope))->toBeFalse();
});

test('product custom field condition returns false when no product in scope', function (): void {
    $condition = new ProductCustomFieldCondition();
    $condition->field = 'name';
    $condition->operator = '=';
    $condition->value = 'Test';

    $scope = new PriceScope();

    expect($condition->match($scope))->toBeFalse();
});

test('product custom field condition returns false when field is null', function (): void {
    $product = Product::factory()->create();

    $condition = new ProductCustomFieldCondition();
    $condition->field = null;
    $condition->operator = '=';
    $condition->value = 'Test';

    $scope = new PriceScope(product: $product);

    expect($condition->match($scope))->toBeFalse();
});
