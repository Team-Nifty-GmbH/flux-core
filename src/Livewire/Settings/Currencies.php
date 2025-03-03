<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Currency\DeleteCurrency;
use FluxErp\Livewire\DataTables\CurrencyList;
use FluxErp\Livewire\Forms\CurrencyForm;
use FluxErp\Models\Currency;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Currencies extends CurrencyList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.currencies';

    public CurrencyForm $selectedCurrency;

    public bool $editModal = false;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.showEditModal()',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->color('indigo')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.showEditModal(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteCurrency::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Currency')]),
                ]),
        ];
    }

    public function showEditModal(Currency $currency): void
    {
        $this->selectedCurrency->reset();
        $this->selectedCurrency->fill($currency);

        $this->editModal = true;
        $this->resetErrorBag();
    }

    public function save(): bool
    {
        try {
            $this->selectedCurrency->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(Currency $currency): bool
    {
        $this->selectedCurrency->reset();
        $this->selectedCurrency->fill($currency);

        try {
            $this->selectedCurrency->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
