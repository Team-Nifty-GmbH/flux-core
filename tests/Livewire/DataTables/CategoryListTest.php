<?php

use FluxErp\Livewire\DataTables\CategoryList;
use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CategoryList::class)
        ->assertOk();
});

test('lists children below their root category', function (): void {
    $root = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $child = Category::factory()->create([
        'model_type' => morph_alias(Product::class),
        'parent_id' => $root->getKey(),
    ]);

    $rows = Livewire::test(CategoryList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    $rows = array_values(
        array_filter($rows, fn (array $row): bool => in_array($row['id'], [$root->getKey(), $child->getKey()]))
    );

    expect(array_column($rows, 'id'))->toBe([$root->getKey(), $child->getKey()])
        ->and(array_column($rows, 'depth'))->toBe([0, 1]);
});
