<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Client\CreateClient;
use FluxErp\Actions\Client\UpdateClient;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\DataTables\ClientList;
use FluxErp\Livewire\Forms\ClientForm;
use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Client;
use FluxErp\Models\Country;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\WithFileUploads;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Clients extends ClientList
{
    use Actions, WithFileUploads, WithTabs;

    protected string $view = 'flux::livewire.settings.clients';

    public string $tab = 'general';

    public ClientForm $client;

    public MediaForm $logo;

    public MediaForm $logoSmall;

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'bankConnections' => BankConnection::query()
                    ->where('is_active', true)
                    ->select(['id', 'name'])
                    ->get()
                    ->toArray(),
                'countries' => Country::query()
                    ->orderBy('iso_alpha2', 'ASC')
                    ->select(['id', 'name', 'iso_alpha2'])
                    ->get()
                    ->toArray(),
            ]
        );
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'wire:click' => 'show()',
                ])
                ->when(CreateClient::canPerformAction(false)),
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
                    'wire:click' => 'show(record.id)',
                ])
                ->when(UpdateClient::canPerformAction(false)),
            DataTableButton::make()
                ->label(__('Customer portal'))
                ->color('primary')
                ->icon('user')
                ->attributes([
                    'wire:click' => 'showCustomerPortal(record)',
                ])
                ->when(UpdateClient::canPerformAction(false)),
        ];
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
            $openModal('edit-client');
        JS);
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

        $this->logo->model_type = Client::class;
        $this->logo->model_id = $this->client->id;
        $this->logo->collection_name = 'logo';

        $this->logoSmall->model_type = Client::class;
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

        $this->loadData();

        return true;
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

    public function showCustomerPortal(Client $record): void
    {
        $this->redirect(route('settings.customer-portal', ['client' => $record->id]), true);
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('general')
                ->label(__('General')),
            TabButton::make('settings.client.logos')
                ->label(__('Logos')),
            TabButton::make('settings.client.terms-and-conditions')
                ->label(__('Terms and Conditions')),
            TabButton::make('settings.client.sepa')
                ->label(__('SEPA')),
        ];
    }

    public function updatingTab(): void
    {
        $this->forceRender();
    }
}
