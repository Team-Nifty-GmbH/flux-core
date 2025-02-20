<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\ProductPropertyGroup\CreateProductPropertyGroup;
use FluxErp\Actions\ProductPropertyGroup\DeleteProductPropertyGroup;
use FluxErp\Actions\ProductPropertyGroup\UpdateProductPropertyGroup;
use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Livewire\DataTables\ProductPropertyGroupList;
use FluxErp\Livewire\Forms\ProductPropertyGroupForm;
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProductPropertyGroups extends ProductPropertyGroupList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.product-property-groups';

    public ProductPropertyGroupForm $productPropertyGroup;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Add'))
                ->icon('plus')
                ->color('indigo')
                ->wireClick('edit')
                ->when(
                    fn () => resolve_static(CreateProductPropertyGroup::class, 'canPerformAction', [false])
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
                    fn () => resolve_static(UpdateProductPropertyGroup::class, 'canPerformAction', [false])
                ),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->attributes([
                    'wire:flux-confirm.icon.error' => __(
                        'wire:confirm.delete',
                        ['model' => __('Product Property Group')]
                    ),
                    'wire:click' => 'delete(record.id)',
                ])
                ->when(
                    fn () => resolve_static(DeleteProductPropertyGroup::class, 'canPerformAction', [false])
                ),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'propertyTypes' => array_map(
                    fn ($item) => ['name' => $item, 'label' => __(Str::headline($item))],
                    PropertyTypeEnum::values()
                ),
            ]
        );
    }

    #[Renderless]
    public function edit(?ProductPropertyGroup $productPropertyGroup = null): void
    {
        $productPropertyGroup->loadMissing('productProperties:id,product_property_group_id,name,property_type_enum');
        $this->productPropertyGroup->reset();
        $this->productPropertyGroup->fill($productPropertyGroup);

        $this->js(<<<'JS'
            $modalOpen('edit-product-property-group');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->productPropertyGroup->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function delete(ProductPropertyGroup $productPropertyGroup): void
    {
        $this->productPropertyGroup->fill($productPropertyGroup);
        try {
            $this->productPropertyGroup->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }
}
