<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Country\CreateCountry;
use FluxErp\Actions\Country\DeleteCountry;
use FluxErp\Actions\Country\UpdateCountry;
use FluxErp\Livewire\DataTables\CountryList;
use FluxErp\Models\Country;
use FluxErp\Services\CountryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
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
        $countryAction = ($this->selectedCountry['id'] ?? false)
            ? UpdateCountry::make([])
            : CreateCountry::make([]);

        return Arr::prependKeysWith($countryAction->getRules(), 'selectedCountry.');
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

    public function showEditModal(?int $countryId = null): void
    {
        $this->selectedCountry = app(Country::class)->query()->whereKey($countryId)->first()?->toArray() ?: [
            'language_id' => null,
            'currency_id' => null,
            'is_active' => true,
        ];

        $this->editModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! resolve_static(CreateCountry::class, 'canPerformAction', [false])) {
            return;
        }

        $function = ($this->selectedCountry['id'] ?? false) ? 'update' : 'create';

        try {
            $response = app(CountryService::class)->{$function}($this->selectedCountry);
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        if (($response['status'] ?? false) === 200 || $response instanceof Model) {
            $this->notification()->success(__('Successfully saved'));
            $this->editModal = false;
        }
        $this->loadData();
    }

    public function delete(): void
    {
        try {
            DeleteCountry::make(['id' => $this->selectedCountry['id']])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }
}
