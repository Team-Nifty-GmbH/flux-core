<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Actions\Product\ProductBundleProduct\CreateProductBundleProduct;
use FluxErp\Livewire\DataTables\ProductBundleProductList;
use FluxErp\Livewire\Forms\ProductBundleProductForm;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Models\Pivots\ProductBundleProduct;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class BundleList extends ProductBundleProductList
{
    protected string $view = 'flux::livewire.product.bundle-list';

    #[Modelable]
    public ProductForm $product;

    public ProductBundleProductForm $productBundleProductForm;

    public function mount(): void
    {
        $this->filters = [
            [
                'product_id',
                '=',
                $this->product->id,
            ],
        ];

        parent::mount();
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Add'))
                ->icon('plus')
                ->attributes([
                    'x-on:click' => <<<'JS'
                        $openModal('edit-bundle-product-modal')
                    JS,
                ])
                ->when(fn () => CreateProductBundleProduct::canPerformAction(false)),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Edit'))
                ->icon('pencil')
                ->wireClick('edit(record.id)')
                ->when(fn () => true),
            DataTableButton::make()
                ->color('negative')
                ->label(__('Delete'))
                ->icon('trash')
                ->attributes([
                    'wire:confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Bundle Product')]),
                    'wire:click' => 'delete(record.id)',
                ])
                ->when(fn () => true),
        ];
    }

    public function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'product_id',
                'bundle_product_id',
            ]
        );
    }

    public function edit(ProductBundleProduct $productBundleProduct): void
    {
        $this->productBundleProductForm->reset();
        $this->productBundleProductForm->fill($productBundleProduct);

        $this->js(<<<'JS'
            $openModal('edit-bundle-product-modal');
        JS);
    }

    public function save(): bool
    {
        $this->productBundleProductForm->product_id = $this->product->id;

        try {
            $this->productBundleProductForm->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->productBundleProductForm->reset();
        $this->loadData();

        if ($this->product->is_bundle === false) {
            $this->product->is_bundle = true;
            $this->js(<<<'JS'
                Livewire.navigate(window.location.href);
            JS);
        }

        return true;
    }

    public function delete(int $id): void
    {
        $this->productBundleProductForm->reset();
        $this->productBundleProductForm->id = $id;

        try {
            $this->productBundleProductForm->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        if (\FluxErp\Models\Product::query()->whereKey($this->product->id)->first()->bundleProducts()->count() === 0) {
            $this->product->is_bundle = false;
            $this->js(<<<'JS'
                Livewire.navigate(window.location.href);
            JS);
        } else {
            $this->loadData();
        }
    }
}
