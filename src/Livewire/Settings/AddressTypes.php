<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\AddressType\CreateAddressType;
use FluxErp\Actions\AddressType\DeleteAddressType;
use FluxErp\Actions\AddressType\UpdateAddressType;
use FluxErp\Livewire\DataTables\AddressTypeList;
use FluxErp\Livewire\Forms\AddressTypeForm;
use FluxErp\Models\AddressType;
use FluxErp\Models\Tenant;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class AddressTypes extends AddressTypeList
{
    use Actions;

    public AddressTypeForm $addressType;

    protected ?string $includeBefore = 'flux::livewire.settings.address-types';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->when(resolve_static(CreateAddressType::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateAddressType::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteAddressType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Address Types')]),
                ]),
        ];
    }

    public function delete(AddressType $addressType): bool
    {
        $this->addressType->reset();
        $this->addressType->fill($addressType);

        try {
            $this->addressType->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function edit(AddressType $addressType): void
    {
        $this->addressType->reset();
        $this->addressType->fill($addressType);

        $this->js(<<<'JS'
            $modalOpen('edit-address-type-modal');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->addressType->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'tenants' => resolve_static(Tenant::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
            ]);
    }
}
