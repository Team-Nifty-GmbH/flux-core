<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Client\CreateClient;
use FluxErp\Actions\Client\UpdateClient;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\DataTables\ClientList;
use FluxErp\Livewire\Forms\ClientForm;
use FluxErp\Livewire\Forms\MediaUploadForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Client;
use FluxErp\Models\Country;
use FluxErp\Models\Scopes\UserClientScope;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Clients extends ClientList
{
    use Actions, WithFileUploads, WithTabs;

    public ClientForm $client;

    public MediaUploadForm $logo;

    public MediaUploadForm $logoSmall;

    public string $tab = 'general';

    protected string $view = 'flux::livewire.settings.clients';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'wire:click' => 'show()',
                ])
                ->when(resolve_static(CreateClient::class, 'canPerformAction', [false])),
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
                    'wire:click' => 'show(record.id)',
                ])
                ->when(resolve_static(UpdateClient::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Customer portal'))
                ->color('indigo')
                ->icon('user')
                ->attributes([
                    'wire:click' => 'showCustomerPortal(record.id)',
                ])
                ->when(resolve_static(UpdateClient::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function delete(): bool
    {
        try {
            $this->client->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('general')
                ->text(__('General')),
            TabButton::make('settings.client.logos')
                ->text(__('Logos')),
            TabButton::make('settings.client.terms-and-conditions')
                ->text(__('Terms and Conditions')),
            TabButton::make('settings.client.sepa')
                ->text(__('SEPA')),
        ];
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->client->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->logo->model_type = morph_alias(Client::class);
        $this->logo->model_id = $this->client->id;
        $this->logo->collection_name = 'logo';

        $this->logoSmall->model_type = app(Client::class)->getMorphClass();
        $this->logoSmall->model_id = $this->client->id;
        $this->logoSmall->collection_name = 'logo_small';

        if ($this->logo->stagedFiles || $this->logo->id) {
            try {
                $this->logo->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        if ($this->logoSmall->stagedFiles || $this->logoSmall->id) {
            try {
                $this->logoSmall->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->notification()->success(__(':model saved', ['model' => __('Client')]))->send();

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function show(?Client $record = null): void
    {
        $this->client->reset();
        $record->load('bankConnections:id');
        $client = $record->toArray();
        $client['bank_connections'] = array_column($client['bank_connections'], 'id');
        $client['opening_hours'] = $client['opening_hours'] ?? [];

        $this->client->fill($client);

        $this->logo->fill($record->getMedia('logo')->first() ?? []);
        $this->logoSmall->fill($record->getMedia('logo_small')->first() ?? []);

        $this->js(<<<'JS'
            $modalOpen('edit-client');
        JS);
    }

    public function showCustomerPortal(Client $record): void
    {
        $this->redirect(route('settings.customer-portal', ['client' => $record->id]), true);
    }

    public function updatingTab(): void
    {
        $this->forceRender();
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope(UserClientScope::class);
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'bankConnections' => resolve_static(BankConnection::class, 'query')
                    ->where('is_active', true)
                    ->select(['bank_connections.id', 'name'])
                    ->get()
                    ->toArray(),
                'countries' => resolve_static(Country::class, 'query')
                    ->orderBy('iso_alpha2', 'ASC')
                    ->select(['id', 'name', 'iso_alpha2'])
                    ->get()
                    ->toArray(),
            ]
        );
    }
}
