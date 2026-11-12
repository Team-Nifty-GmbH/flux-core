<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\ProductConfigurationGroup\CreateProductConfigurationGroup;
use FluxErp\Actions\ProductConfigurationGroup\DeleteProductConfigurationGroup;
use FluxErp\Actions\ProductConfigurationGroup\UpdateProductConfigurationGroup;
use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Livewire\DataTables\ProductConfigurationGroupList;
use FluxErp\Livewire\Forms\ProductConfigurationGroupForm;
use FluxErp\Livewire\Forms\ProductConfigurationGroupItemForm;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProductConfigurationGroups extends ProductConfigurationGroupList
{
    public array $items = [];

    public ProductConfigurationGroupForm $productConfigurationGroupForm;

    public ProductConfigurationGroupItemForm $productConfigurationGroupItemForm;

    protected ?string $includeBefore = 'flux::livewire.settings.product-configuration-groups';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->wireClick('edit()')
                ->when(
                    fn () => resolve_static(CreateProductConfigurationGroup::class, 'canPerformAction', [false])
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
                    fn () => resolve_static(UpdateProductConfigurationGroup::class, 'canPerformAction', [false])
                ),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->attributes([
                    'wire:flux-confirm.type.error' => __(
                        'wire:confirm.delete',
                        ['model' => __('Product Configuration Group')]
                    ),
                    'wire:click' => 'delete(record.id)',
                ])
                ->when(
                    fn () => resolve_static(DeleteProductConfigurationGroup::class, 'canPerformAction', [false])
                ),
        ];
    }

    public function delete(ProductConfigurationGroup $productConfigurationGroup): bool
    {
        $this->productConfigurationGroupForm->reset();
        $this->productConfigurationGroupForm->fill($productConfigurationGroup);

        try {
            $this->productConfigurationGroupForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->productConfigurationGroupForm);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function deleteItem(ProductConfigurationGroupItem $productConfigurationGroupItem): bool
    {
        $this->productConfigurationGroupItemForm->reset();
        $this->productConfigurationGroupItemForm->fill($productConfigurationGroupItem);

        try {
            $this->productConfigurationGroupItemForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->productConfigurationGroupItemForm);

            return false;
        }

        $this->loadItems();

        return true;
    }

    #[Renderless]
    public function edit(?ProductConfigurationGroup $productConfigurationGroup = null): void
    {
        $this->productConfigurationGroupForm->reset();

        if ($productConfigurationGroup?->exists) {
            $this->productConfigurationGroupForm->fill($productConfigurationGroup);
        }

        $this->loadItems();

        $this->modalOpen('edit-product-configuration-group-modal');
    }

    #[Renderless]
    public function editItem(?ProductConfigurationGroupItem $productConfigurationGroupItem = null): void
    {
        $this->productConfigurationGroupItemForm->reset();

        if ($productConfigurationGroupItem?->exists) {
            $this->productConfigurationGroupItemForm->fill($productConfigurationGroupItem);
        }

        $this->productConfigurationGroupItemForm->product_configuration_group_id =
            $this->productConfigurationGroupForm->id;

        $this->modalOpen('edit-product-configuration-group-item-modal');
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->productConfigurationGroupForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->productConfigurationGroupForm);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function saveItem(): bool
    {
        try {
            $this->productConfigurationGroupItemForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->productConfigurationGroupItemForm);

            return false;
        }

        $this->loadItems();

        return true;
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'priceModes' => ConfigurationPriceModeEnum::valuesLocalized(),
                'selectionTypes' => ConfigurationSelectionTypeEnum::valuesLocalized(),
            ]
        );
    }

    protected function loadItems(): void
    {
        if (! $this->productConfigurationGroupForm->id) {
            $this->items = [];

            return;
        }

        $this->items = resolve_static(ProductConfigurationGroupItem::class, 'query')
            ->where('product_configuration_group_id', $this->productConfigurationGroupForm->id)
            ->with('product:id,name,product_number')
            ->orderBy('sort')
            ->get()
            ->map(fn (ProductConfigurationGroupItem $item) => [
                'id' => $item->getKey(),
                'product_name' => $item->product?->name,
                'surcharge' => $item->surcharge,
                'max_amount' => $item->max_amount,
                'sort' => $item->sort,
            ])
            ->all();
    }
}
