<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Http\Requests\CreateCountryRequest;
use FluxErp\Http\Requests\UpdateCountryRequest;
use FluxErp\Models\Country;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Services\CountryService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class PriceLists extends Component
{
    use Actions;

    public ?array $selectedPriceList = [];

    public array $priceLists = [];

    public bool $editModal = false;

    public function getRules(): mixed
    {
        $priceListRequest = ($this->selectedPriceList['id'] ?? false)
            ? new UpdateCountryRequest()
            : new CreateCountryRequest();

        return Arr::prependKeysWith($priceListRequest->getRules($this->selectedPriceList), 'selectedPriceList.');
    }

    public function boot(): void
    {
        $this->priceLists = PriceList::query()->get()->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.price-lists');
    }

    public function showEditModal(int|null $priceListId = null): void
    {
        $this->selectedPriceList = PriceList::query()->whereKey($priceListId)->first()?->toArray() ?: [];

        $this->editModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! user_can('api.priceLists.post')) {
            return;
        }

        $validated = $this->validate();

        $priceList = PriceList::query()->whereKey($this->selectedPriceList['id'] ?? false)->firstOrNew();

        $function = $priceList->exists ? 'update' : 'create';

        $response = (new CountryService())->{$function}($validated['selectedCountry']);

        if (($response['status'] ?? false) === 200 || $response instanceof Model) {
            $this->notification()->success('Successfully saved');
            $this->editModal = false;
        }
        $this->boot();
    }

    public function delete(): void
    {
        if (! user_can('api.countries.{id}.delete')) {
            return;
        }

        $collection = collect($this->priceLists);
        Country::query()->whereKey($this->selectedPriceList['id'])->first()->delete();
        $this->priceLists = $collection->whereNotIn('id', [$this->selectedPriceList['id']])->toArray();
    }
}
