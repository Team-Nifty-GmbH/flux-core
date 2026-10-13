<?php

use FluxErp\Livewire\Product\MediaGrid;
use FluxErp\Models\Product;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MediaGrid::class)
        ->assertOk();
});

test('second upload only stores the new files', function (): void {
    $product = Product::factory()->create();

    // The product page fills the modelable product form, a subclass does it here
    $component = new class() extends MediaGrid
    {
        public function mount(?int $productId = null): void
        {
            parent::mount();

            $this->product->id = $productId;
        }
    };

    Livewire::test($component, ['productId' => $product->getKey()])
        ->upload('uploads', [UploadedFile::fake()->image('first.jpg')], true)
        ->assertHasNoErrors()
        ->upload('uploads', [UploadedFile::fake()->image('second.jpg')], true)
        ->assertHasNoErrors();

    expect($product->media()->where('collection_name', 'images')->pluck('file_name')->sort()->values()->all())
        ->toBe(['first.jpg', 'second.jpg']);
});
