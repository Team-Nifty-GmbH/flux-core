<?php

use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;

function productWithOptions(array $optionNames, ?int $parentId): Product
{
    $product = Product::factory()->create([
        'name' => 'Kukicha Superior',
        'parent_id' => $parentId,
    ]);

    foreach ($optionNames as $optionName) {
        $product->productOptions()->attach(
            ProductOption::factory()->create([
                'product_option_group_id' => ProductOptionGroup::factory()->create()->getKey(),
                'name' => $optionName,
            ])
        );
    }

    return $product->refresh();
}

it('labels a product without options by its name alone', function (): void {
    $product = Product::factory()->create(['name' => 'Kukicha Superior']);

    expect($product->getLabel())->toBe('Kukicha Superior');
});

it('tells variants of one product apart by their option values', function (): void {
    $parent = Product::factory()->create(['name' => 'Kukicha Superior']);

    $labels = collect(['100g Tüte', '250g Tüte'])
        ->map(fn (string $option) => productWithOptions([$option], $parent->getKey())->getLabel());

    expect($labels->all())
        ->toBe(['Kukicha Superior (100g Tüte)', 'Kukicha Superior (250g Tüte)']);
});

it('lists every option value a variant carries', function (): void {
    $parent = Product::factory()->create(['name' => 'Kukicha Superior']);

    expect(productWithOptions(['100g Tüte', 'Bio'], $parent->getKey())->getLabel())
        ->toBe('Kukicha Superior (100g Tüte, Bio)');
});

it('leaves the options untouched for a product that is not a variant', function (): void {
    $product = productWithOptions(['100g Tüte'], null);

    $queries = 0;
    DB::listen(function () use (&$queries): void {
        $queries++;
    });

    expect($product->getLabel())->toBe('Kukicha Superior')
        ->and($queries)->toBe(0);
});
