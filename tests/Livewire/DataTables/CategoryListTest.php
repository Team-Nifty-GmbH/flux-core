<?php

use FluxErp\Livewire\DataTables\CategoryList;
use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Illuminate\Support\Str;
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

test('filtering for a child category returns its family instead of nothing', function (): void {
    $root = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $child = Category::factory()->create([
        'model_type' => morph_alias(Product::class),
        'parent_id' => $root->getKey(),
        'name' => $childName = Str::uuid()->toString(),
    ]);
    $unrelated = Category::factory()->create(['model_type' => morph_alias(Product::class)]);

    $rows = Livewire::test(CategoryList::class)
        ->set('userFilters', [[[
            'column' => 'name',
            'operator' => '=',
            'value' => $childName,
        ]]])
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect(array_column($rows, 'id'))->toBe([$root->getKey(), $child->getKey()])
        ->not->toContain($unrelated->getKey());
});
