<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Http\Requests\CreateCurrencyRequest;
use FluxErp\Http\Requests\UpdateCurrencyRequest;
use FluxErp\Livewire\DataTables\CurrencyList;
use FluxErp\Models\Currency;
use FluxErp\Services\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Currencies extends CurrencyList
{
    use Actions;

    protected string $view = 'flux::livewire.settings.currencies';

    public array $selectedCurrency = [];

    public bool $editModal = false;

    public function getRules(): array
    {
        $currencyRequest = ($this->selectedCurrency['id'] ?? false)
            ? new UpdateCurrencyRequest()
            : new CreateCurrencyRequest();

        return Arr::prependKeysWith($currencyRequest->getRules($this->selectedCurrency), 'selectedCurrency.');
    }

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

    public function showEditModal(int $currencyId = null): void
    {
        if (! $currencyId) {
            $this->selectedCurrency = [];
        } else {
            $this->selectedCurrency = Currency::query()->whereKey($currencyId)->first()->toArray();
        }

        $this->editModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! user_can('api.currencies.post')) {
            return;
        }

        $validated = $this->validate();

        $currency = Currency::query()->whereKey($this->selectedCurrency['id'] ?? false)->firstOrNew();

        $function = $currency->exists ? 'update' : 'create';

        $response = (new CurrencyService())->{$function}($validated['selectedCurrency']);

        if (($response['status'] ?? false) === 200 || $response instanceof Model) {
            $this->notification()->success(__('Successfully saved'));
            $this->editModal = false;
        }
        $this->loadData();
    }

    public function delete(): void
    {
        if (! user_can('api.currencies.{id}.delete')) {
            return;
        }

        Currency::query()
            ->whereKey($this->selectedCurrency['id'])
            ->first()
            ->delete();

        $this->loadData();
    }
}
