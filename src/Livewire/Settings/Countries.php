<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Http\Requests\CreateCountryRequest;
use FluxErp\Http\Requests\UpdateCountryRequest;
use FluxErp\Livewire\DataTables\CountryList;
use FluxErp\Models\Country;
use FluxErp\Services\CountryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Countries extends CountryList
{
    use Actions;

    protected string $view = 'flux::livewire.settings.countries';

    public ?array $selectedCountry = [
        'language_id' => null,
        'currency_id' => null,
    ];

    public bool $editModal = false;

    public function getRules(): array
    {
        $countryRequest = ($this->selectedCountry['id'] ?? false)
            ? new UpdateCountryRequest()
            : new CreateCountryRequest();

        return Arr::prependKeysWith($countryRequest->getRules($this->selectedCountry), 'selectedCountry.');
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

    public function showEditModal(int $countryId = null): void
    {
        $this->selectedCountry = Country::query()->whereKey($countryId)->first()?->toArray() ?: [
            'language_id' => null,
            'currency_id' => null,
            'is_active' => true,
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
            $this->notification()->success(__('Successfully saved'));
            $this->editModal = false;
        }
        $this->loadData();
    }

    public function delete(): void
    {
        if (! user_can('api.countries.{id}.delete')) {
            return;
        }

        Country::query()
            ->whereKey($this->selectedCountry['id'])
            ->first()
            ->delete();

        $this->loadData();
    }
}
