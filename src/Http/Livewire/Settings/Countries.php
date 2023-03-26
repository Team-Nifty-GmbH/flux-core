<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Http\Requests\CreateCountryRequest;
use FluxErp\Http\Requests\UpdateCountryRequest;
use FluxErp\Models\Country;
use FluxErp\Services\CountryService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class Countries extends Component
{
    use Actions;

    public ?array $selectedCountry = [
        'language_id' => null,
        'currency_id' => null,
    ];

    public array $countries = [];

    public bool $editModal = false;

    public function getRules(): mixed
    {
        $countryRequest = ($this->selectedCountry['id'] ?? false)
            ? new UpdateCountryRequest()
            : new CreateCountryRequest();

        return Arr::prependKeysWith($countryRequest->getRules($this->selectedCountry), 'selectedCountry.');
    }

    public function boot(): void
    {
        $this->countries = Country::query()->with(['currency', 'language'])->get()->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.countries');
    }

    /**
     * @param int|null $countryId
     */
    public function showEditModal(int|null $countryId = null): void
    {
        $this->selectedCountry = Country::query()->whereKey($countryId)->first()?->toArray() ?: [
            'language_id' => null,
            'currency_id' => null,
        ];

        $this->editModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! user_can('api.countries.post')) {
            return;
        }

        $validated = $this->validate();

        $country = Country::query()->whereKey($this->selectedCountry['id'] ?? false)->firstOrNew();

        $function = $country->exists ? 'update' : 'create';

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

        $collection = collect($this->countries);
        Country::query()->whereKey($this->selectedCountry['id'])->first()->delete();
        $this->countries = $collection->whereNotIn('id', [$this->selectedCountry['id']])->toArray();
    }
}
