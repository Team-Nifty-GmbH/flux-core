<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\VatRate\CreateVatRate;
use FluxErp\Actions\VatRate\DeleteVatRate;
use FluxErp\Actions\VatRate\UpdateVatRate;
use FluxErp\Livewire\DataTables\VatRateList;
use FluxErp\Livewire\Forms\VatRateForm;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class VatRates extends VatRateList
{
    use Actions;

    public ?string $includeBefore = 'flux::livewire.settings.vat-rates';

    public VatRateForm $vatRate;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateVatRate::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit',
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
                ->when(resolve_static(UpdateVatRate::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(resolve_static(DeleteVatRate::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Vat Rate')]),
                    'wire:click' => 'delete(record.id)',
                ]),
        ];
    }

    public function delete(VatRate $vatRate): bool
    {
        $this->vatRate->reset();
        $this->vatRate->fill($vatRate);

        try {
            $this->vatRate->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function edit(VatRate $vatRate): void
    {
        $this->vatRate->reset();
        $this->vatRate->fill($vatRate);

        $this->js(<<<'JS'
            $modalOpen('edit-vat-rate-modal');
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
}
