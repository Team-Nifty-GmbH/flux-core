<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\VatRate\CreateVatRate;
use FluxErp\Actions\VatRate\DeleteVatRate;
use FluxErp\Actions\VatRate\UpdateVatRate;
use FluxErp\Livewire\DataTables\VatRateList;
use FluxErp\Livewire\Forms\VatRateForm;
use FluxErp\Models\VatRate;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class VatRates extends VatRateList
{
    use Actions;

    public string $view = 'flux::livewire.settings.vat-rates';

    public VatRateForm $vatRate;

    public function mount(): void
    {
        parent::mount();

        $this->headline = __('Vat Rates');
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(CreateVatRate::canPerformAction(false))
                ->attributes(
                    ['wire:click' => 'edit']
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
                ->when(UpdateVatRate::canPerformAction(false))
                ->attributes(
                    ['wire:click' => 'edit(record.id)']
                ),
        ];
    }

    public function edit(VatRate $vatRate): void
    {
        $this->vatRate->reset();
        $this->vatRate->fill($vatRate);

        $this->js(<<<'JS'
            $openModal('edit-vat-rate');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->vatRate->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(): bool
    {
        try {
            DeleteVatRate::make($this->vatRate->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
