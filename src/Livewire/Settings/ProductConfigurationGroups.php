<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\ProductConfigurationGroup\CreateProductConfigurationGroup;
use FluxErp\Actions\ProductConfigurationGroup\DeleteProductConfigurationGroup;
use FluxErp\Actions\ProductConfigurationGroup\UpdateProductConfigurationGroup;
use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Livewire\DataTables\ProductConfigurationGroupList;
use FluxErp\Livewire\Forms\ProductConfigurationGroupForm;
use FluxErp\Livewire\Forms\ProductConfigurationGroupItemForm;
use FluxErp\Livewire\Forms\ProductConfigurationRuleForm;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\ProductConfigurationRule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProductConfigurationGroups extends ProductConfigurationGroupList
{
    public array $items = [];

    public ProductConfigurationGroupForm $productConfigurationGroupForm;

    public ProductConfigurationGroupItemForm $productConfigurationGroupItemForm;

    public ProductConfigurationRuleForm $productConfigurationRuleForm;

    public array $ruleOptions = [
        'groups' => [],
        'items' => [],
    ];

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
    public function deleteRule(int $ruleId): bool
    {
        $this->productConfigurationRuleForm->reset();
        $this->productConfigurationRuleForm->id = $ruleId;

        try {
            $this->productConfigurationRuleForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->productConfigurationRuleForm);

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
    public function editRule(int $itemId, ?int $ruleId = null): void
    {
        $this->productConfigurationRuleForm->reset();

        if ($ruleId) {
            $rule = resolve_static(ProductConfigurationRule::class, 'query')
                ->whereKey($ruleId)
                ->with('targetItems:id')
                ->first();
            $this->productConfigurationRuleForm->fill($rule);
            $this->productConfigurationRuleForm->target_items = $rule->targetItems->modelKeys();
        }

        $this->productConfigurationRuleForm->product_configuration_group_item_id = $itemId;

        $this->ruleOptions = [
            'groups' => resolve_static(ProductConfigurationGroup::class, 'query')
                ->whereKeyNot($this->productConfigurationGroupForm->id)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (ProductConfigurationGroup $group) => [
                    'id' => $group->getKey(),
                    'name' => $group->name,
                ])
                ->all(),
            'items' => resolve_static(ProductConfigurationGroupItem::class, 'query')
                ->where('product_configuration_group_id', '!=', $this->productConfigurationGroupForm->id)
                ->with(['product:id,name', 'productConfigurationGroup:id,name'])
                ->get(['id', 'product_id', 'product_configuration_group_id'])
                ->map(fn (ProductConfigurationGroupItem $item) => [
                    'id' => $item->getKey(),
                    'name' => $item->productConfigurationGroup?->name . ': ' . $item->product?->name,
                ])
                ->all(),
        ];

        $this->modalOpen('edit-product-configuration-rule-modal');
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

    #[Renderless]
    public function saveRule(): bool
    {
        try {
            $this->productConfigurationRuleForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->productConfigurationRuleForm);

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
                'ruleTypes' => ConfigurationRuleTypeEnum::valuesLocalized(),
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
            ->with([
                'product:id,name,product_number',
                'productConfigurationRules.targetGroup:id,name',
                'productConfigurationRules.targetItems.product:id,name',
            ])
            ->orderBy('sort')
            ->get()
            ->map(fn (ProductConfigurationGroupItem $item) => [
                'id' => $item->getKey(),
                'product_name' => $item->product?->name,
                'surcharge' => $item->surcharge,
                'max_amount' => $item->max_amount,
                'sort' => $item->sort,
                'rules' => $item->productConfigurationRules
                    ->map(fn (ProductConfigurationRule $rule) => [
                        'id' => $rule->getKey(),
                        'rule_type' => __(str($rule->rule_type_enum->value)->headline()->toString()),
                        'target' => $rule->targetGroup?->name
                            ?? $rule->targetItems->map(fn ($target) => $target->product?->name)->implode(', '),
                    ])
                    ->all(),
            ])
            ->all();
    }
}
