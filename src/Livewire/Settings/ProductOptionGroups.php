<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\ProductOptionGroup\CreateProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\DeleteProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\UpdateProductOptionGroup;
use FluxErp\Livewire\DataTables\ProductOptionGroupList;
use FluxErp\Livewire\Forms\ProductOptionGroupForm;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProductOptionGroups extends ProductOptionGroupList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.product-option-groups';

    public ProductOptionGroupForm $productOptionGroupForm;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->wireClick('edit')
                ->when(
                    fn () => resolve_static(CreateProductOptionGroup::class, 'canPerformAction', [false])
                ),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->wireClick('edit(record.id)')
                ->when(
                    fn () => resolve_static(UpdateProductOptionGroup::class, 'canPerformAction', [false])
                ),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Product Option Group')]),
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
            $modalOpen('edit-product-option-group-modal');
        JS);
    }

    public function delete(ProductOptionGroup $productOptionGroup): void
    {
        $this->productOptionGroupForm->reset();
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
