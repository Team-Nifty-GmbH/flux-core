<?php

use FluxErp\Livewire\Settings\Categories;
use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Categories::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Categories::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('category.id', null)
        ->assertSet('category.name', null)
        ->assertSet('category.model_type', null)
        ->assertSet('category.parent_id', null)
        ->assertSet('category.is_active', true);
});

test('edit with model fills form', function (): void {
    $category = Category::factory()->create([
        'model_type' => morph_alias(Product::class),
    ]);

    Livewire::test(Categories::class)
        ->call('edit', $category->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('category.id', $category->getKey())
        ->assertSet('category.name', $category->name)
        ->assertSet('category.model_type', $category->model_type);
});

test('can create category', function (): void {
    $name = Str::uuid()->toString();

    Livewire::test(Categories::class)
        ->call('edit')
        ->set('category.name', $name)
        ->set('category.model_type', morph_alias(Product::class))
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'name' => $name,
        'model_type' => morph_alias(Product::class),
    ]);
});

test('can update category', function (): void {
    $category = Category::factory()->create([
        'model_type' => morph_alias(Product::class),
    ]);

    Livewire::test(Categories::class)
        ->call('edit', $category->getKey())
        ->assertSet('category.id', $category->getKey())
        ->set('category.name', 'Updated Category Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($category->refresh()->name)->toEqual('Updated Category Name');
});

test('can delete category', function (): void {
    $category = Category::factory()->create([
        'model_type' => morph_alias(Product::class),
    ]);

    $categoryId = $category->getKey();

    Livewire::test(Categories::class)
        ->call('delete', $categoryId)
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('categories', [
        'id' => $categoryId,
    ]);
});

test('create category validation fails without required fields', function (): void {
    Livewire::test(Categories::class)
        ->call('edit')
        ->set('category.name', null)
        ->set('category.model_type', null)
        ->call('save')
        ->assertHasErrors();
});

test('edit resets form when switching from existing to new', function (): void {
    $category = Category::factory()->create([
        'model_type' => morph_alias(Product::class),
    ]);

    Livewire::test(Categories::class)
        ->call('edit', $category->getKey())
        ->assertSet('category.id', $category->getKey())
        ->assertSet('category.name', $category->name)
        ->call('edit')
        ->assertSet('category.id', null)
        ->assertSet('category.name', null);
});
