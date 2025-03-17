<?php

namespace FluxErp\Livewire\DataTables\Settings;

use FluxErp\Actions\DiscountGroup\CreateDiscountGroup;
use FluxErp\Actions\DiscountGroup\DeleteDiscountGroup;
use FluxErp\Actions\DiscountGroup\UpdateDiscountGroup;
use FluxErp\Livewire\DataTables\DiscountGroupList as BaseDiscountGroupList;
use FluxErp\Models\DiscountGroup;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class DiscountGroupList extends BaseDiscountGroupList
{
    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->icon('plus')
                ->color('indigo')
                ->attributes([
                    'x-on:click' => 'editItem(null)',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->attributes([
                    'x-on:click' => 'editItem(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->attributes([
                    'x-on:click' => 'deleteItem(record.id)',
                    'wire:loading.attr' => 'disabled',
                ]),
        ];
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
}
