<?php

use FluxErp\Livewire\Settings\Categories;
use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Livewire\Livewire;

function categoryTree(): array
{
    $model = morph_alias(Product::class);

    $area = Category::factory()->create([
        'model_type' => $model,
        'name' => 'Synthese',
    ]);

    return [
        $area,
        Category::factory()->create([
            'model_type' => $model,
            'parent_id' => $area->getKey(),
            'name' => 'Ausgelaufen',
        ]),
        Category::factory()->create([
            'model_type' => $model,
            'parent_id' => $area->getKey(),
            'name' => 'Loesungsmittel leer',
        ]),
    ];
}

function renderedOrderOf(Categories $categories, array $tree): array
{
    $categories->loadData(true);

    $keys = array_map(fn (Category $category): int => $category->getKey(), $tree);

    return array_values(array_filter(
        array_column($categories->data['data'], 'id'),
        fn ($id): bool => in_array($id, $keys)
    ));
}

test('the children of a category come back in their own order', function (): void {
    [$area, $spilled, $solvent] = categoryTree();

    $solvent->moveToPosition($spilled->sort_number);

    expect($area->fresh()->children->pluck('name')->all())
        ->toBe(['Loesungsmittel leer', 'Ausgelaufen']);
});

test('dragging a child to the front puts it before its sibling', function (): void {
    $tree = categoryTree();
    [$area, $spilled, $solvent] = $tree;

    $categories = Livewire::test(Categories::class)->instance();

    expect(renderedOrderOf($categories, $tree))
        ->toBe([$area->getKey(), $spilled->getKey(), $solvent->getKey()]);

    $categories->sortRows($solvent->getKey(), 1);

    expect(renderedOrderOf($categories, $tree))
        ->toBe([$area->getKey(), $solvent->getKey(), $spilled->getKey()]);
});

test('a record that is not on the list leaves the order alone', function (): void {
    $tree = categoryTree();
    [$area, $spilled, $solvent] = $tree;

    $categories = Livewire::test(Categories::class)->instance();
    $categories->loadData(true);

    $categories->sortRows($area->getKey() + 10_000, 0);

    expect(renderedOrderOf($categories, $tree))
        ->toBe([$area->getKey(), $spilled->getKey(), $solvent->getKey()]);
});
