<?php

namespace FluxErp\Http\Livewire\DataTables\Settings;

use FluxErp\Actions\DiscountGroup\CreateDiscountGroup;
use FluxErp\Actions\DiscountGroup\DeleteDiscountGroup;
use FluxErp\Actions\DiscountGroup\UpdateDiscountGroup;
use FluxErp\Http\Livewire\DataTables\DiscountGroupList as BaseDiscountGroupList;
use FluxErp\Models\DiscountGroup;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class DiscountGroupList extends BaseDiscountGroupList
{
    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->attributes([
                    'x-on:click' => 'editItem(record.id)',
                ]),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->attributes([
                    'x-on:click' => 'deleteItem(record.id)',
                    'wire:loading.attr' => 'disabled',
                ]),
        ];
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->icon('plus')
                ->color('primary')
                ->attributes([
                    'x-on:click' => 'editItem(null)',
                ]),
        ];
    }

    public function saveItem(array $discountGroup): bool
    {
        $discountGroup['discounts'] = array_map(fn ($discount) => $discount['id'], $discountGroup['discounts']);
        $action = ($discountGroup['id'] ?? false)
            ? UpdateDiscountGroup::make($discountGroup)
            : CreateDiscountGroup::make($discountGroup);

        try {
            $action->checkPermission()->validate()->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function deleteItem(DiscountGroup $discountGroup): void
    {
        $this->skipRender();

        try {
            DeleteDiscountGroup::make($discountGroup->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    public function loadDiscountGroup(DiscountGroup $discountGroup): array
    {
        return $discountGroup?->load('discounts.model')->toArray();
    }
}
