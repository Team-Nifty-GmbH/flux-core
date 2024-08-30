<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Country\CreateCountry;
use FluxErp\Actions\Country\DeleteCountry;
use FluxErp\Actions\Country\UpdateCountry;
use FluxErp\Livewire\DataTables\CountryList;
use FluxErp\Livewire\Forms\CountryForm;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Countries extends CountryList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.countries';

    public CountryForm $country;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->when(resolve_static(CreateCountry::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdateCountry::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->icon('trash')
                ->when(resolve_static(DeleteCountry::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Country')]),
                ]),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'languages' => resolve_static(Language::class, 'query')
                    ->pluck('name', 'id'),
                'currencies' => resolve_static(Currency::class, 'query')
                    ->pluck('name', 'id'),
            ]
        );
    }

    public function edit(Country $country): void
    {
        $this->country->reset();
        $this->country->fill($country);

        $this->js(<<<'JS'
            $openModal('edit-country');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->country->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(Country $country): bool
    {
        $this->country->reset();
        $this->country->fill($country);

        try {
            $this->country->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
