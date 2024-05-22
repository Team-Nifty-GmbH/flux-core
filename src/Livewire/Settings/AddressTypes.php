<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\AddressType\CreateAddressType;
use FluxErp\Actions\AddressType\DeleteAddressType;
use FluxErp\Actions\AddressType\UpdateAddressType;
use FluxErp\Livewire\DataTables\AddressTypeList;
use FluxErp\Livewire\Forms\AddressTypeForm;
use FluxErp\Models\AddressType;
use FluxErp\Models\Client;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class AddressTypes extends AddressTypeList
{
    use Actions;

    public AddressTypeForm $addressType;

    protected ?string $includeBefore = 'flux::livewire.settings.address-types';

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->when(resolve_static(CreateAddressType::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdateAddressType::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->icon('trash')
                ->when(resolve_static(DeleteAddressType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Address Types')]),
                ]),
        ];
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'clients' => app(Client::class)->query()
                    ->get(['id', 'name'])
                    ->toArray(),
            ]);
    }

    public function edit(AddressType $addressType): void
    {
        $this->addressType->reset();
        $this->addressType->fill($addressType);

        $this->js(<<<'JS'
            $openModal('edit-address-type');
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
}
