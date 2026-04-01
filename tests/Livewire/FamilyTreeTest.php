<?php

use FluxErp\Livewire\FamilyTree;
use FluxErp\Models\Category;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $category = app(Category::class)->create([
        'name' => 'Test Category',
        'model_type' => Category::class,
    ]);

    Livewire::test(FamilyTree::class, [
        'lazy' => false,
        'modelType' => Category::class,
        'modelId' => $category->getKey(),
    ])
        ->assertOk();
});
