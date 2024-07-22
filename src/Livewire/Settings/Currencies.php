<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\CurrencyList;
use FluxErp\Livewire\Forms\CurrencyForm;
use FluxErp\Models\Currency;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Currencies extends CurrencyList
{
    use Actions;

    protected string $view = 'flux::livewire.settings.currencies';

    public CurrencyForm $selectedCurrency;

    public bool $editModal = false;

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.showEditModal()',
                ]),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->color('primary')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.showEditModal(record.id)',
                ]),
        ];
    }

    public function showEditModal(?int $currencyId = null): void
    {
        if (! $currencyId) {
            $this->selectedCurrency->reset();
        } else {
            $this->selectedCurrency->fill(resolve_static(Currency::class, 'query')->whereKey($currencyId)->first());
        }

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

    public function delete(): bool
    {
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
