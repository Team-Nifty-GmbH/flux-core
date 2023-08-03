<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Http\Requests\CreateCurrencyRequest;
use FluxErp\Http\Requests\UpdateCurrencyRequest;
use FluxErp\Models\Currency;
use FluxErp\Services\CurrencyService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class Currencies extends Component
{
    use Actions;

    public array $selectedCurrency = [];

    public array $currencies = [];

    public bool $editModal = false;

    public function getRules(): mixed
    {
        $currencyRequest = ($this->selectedCurrency['id'] ?? false)
            ? new UpdateCurrencyRequest()
            : new CreateCurrencyRequest();

        return Arr::prependKeysWith($currencyRequest->getRules($this->selectedCurrency), 'selectedCurrency.');
    }

    public function boot(): void
    {
        $this->currencies = Currency::all()->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.currencies');
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
            $this->notification()->success('Successfully saved');
            $this->editModal = false;
        }
        $this->boot();
    }

    public function delete(): void
    {
        if (! user_can('api.currencies.{id}.delete')) {
            return;
        }

        $collection = collect($this->currencies);
        Currency::query()->whereKey($this->selectedCurrency['id'])->first()->delete();
        $this->currencies = $collection->whereNotIn('id', [$this->selectedCurrency['id']])->toArray();
    }
}
