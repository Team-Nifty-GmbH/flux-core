<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\ProductOptionGroup\CreateProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\DeleteProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\UpdateProductOptionGroup;
use FluxErp\Livewire\Forms\ProductOptionGroupForm;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class ProductOptionGroupList extends DataTable
{
    use Actions;

    protected string $model = ProductOptionGroup::class;

    protected string $view = 'flux::livewire.data-tables.product-option-group-list';

    public ProductOptionGroupForm $productOptionGroupForm;

    public array $enabledCols = [
        'name',
        'product_options.name',
    ];

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Add'))
                ->icon('plus')
                ->color('primary')
                ->wireClick('edit')
                ->when(
                    fn () => resolve_static(CreateProductOptionGroup::class, 'canPerformAction', [false])
                ),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->wireClick('edit(record.id)')
                ->when(
                    fn () => resolve_static(UpdateProductOptionGroup::class, 'canPerformAction', [false])
                ),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->attributes([
                    'wire:confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Product Option Group')]),
                    'wire:click' => 'delete(record.id)',
                ])
                ->when(
                    fn () => resolve_static(DeleteProductOptionGroup::class, 'canPerformAction', [false])
                ),
        ];
    }

    public function edit(?ProductOptionGroup $productOptionGroup = null): void
    {
        $productOptionGroup->loadMissing('productOptions:id,product_option_group_id,name');
        $this->productOptionGroupForm->reset();
        $this->productOptionGroupForm->fill($productOptionGroup);

        $this->js(<<<'JS'
            $openModal('edit-product-option-group');
        JS);
    }

    public function delete(ProductOptionGroup $productOptionGroup): void
    {
        $this->productOptionGroupForm->fill($productOptionGroup);
        try {
            $this->productOptionGroupForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    public function save(): bool
    {
        try {
            $this->productOptionGroupForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
