<?php

use FluxErp\Models\Category;
use FluxErp\Models\Product;

// A select offered its options newest first, so the order a sortable model
// carries was worth nothing the moment it was offered for picking.
test('a sortable model is offered in its own order', function (): void {
    $model = morph_alias(Product::class);

    $first = Category::factory()->create(['model_type' => $model]);
    $second = Category::factory()->create(['model_type' => $model]);

    $second->moveToPosition($first->sort_number);

    $response = $this->post(route('search', Category::class), [
        'where' => [['model_type', '=', $model]],
    ]);

    $response->assertOk();

    expect(array_column($response->json(), 'id'))
        ->toBe([$second->getKey(), $first->getKey()]);
});

test('an explicit order still wins over the order of the model', function (): void {
    $model = morph_alias(Product::class);

    $second = Category::factory()->create(['model_type' => $model, 'name' => 'Zylinder']);
    $first = Category::factory()->create(['model_type' => $model, 'name' => 'Antrieb']);

    $response = $this->post(route('search', Category::class), [
        'where' => [['model_type', '=', $model]],
        'orderBy' => 'name',
    ]);

    $response->assertOk();

    expect(array_column($response->json(), 'id'))
        ->toBe([$first->getKey(), $second->getKey()]);
});
