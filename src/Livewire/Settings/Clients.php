<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\ClientList;
use FluxErp\Models\Client;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Clients extends ClientList
{
    protected string $view = 'flux::livewire.settings.clients';

    public bool $showClientModal = false;

    public bool $showClientLogosModal = false;

    public bool $create = true;

    protected $listeners = [
        'closeModal',
        'closeLogosModal',
    ];

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
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
                    'x-on:click' => '$wire.show(record)',
                ]),
            DataTableButton::make()
                ->label(__('Logos'))
                ->color('primary')
                ->icon('photograph')
                ->attributes([
                    'x-on:click' => '$wire.showLogos(record.id)',
                ]),
            DataTableButton::make()
                ->label(__('Customer portal'))
                ->color('primary')
                ->icon('user')
                ->attributes([
                    'x-on:click' => '$wire.showCustomerPortal(record)',
                ]),
        ];
    }

    public function show(Client $record = null): void
    {
        $this->dispatch('show', $record?->toArray())->to('settings.client-edit');
        $this->create = ! $record->exists;

        $this->showClientLogosModal = false;
        $this->showClientModal = true;
        $this->skipRender();
    }

    public function showCustomerPortal(Client $record): void
    {
        $this->redirect(route('settings.customer-portal', ['client' => $record->id]));
    }

    public function showLogos(int $id): void
    {
        $this->dispatch('show', $id)->to('settings.client-logos');

        $this->showClientModal = false;
        $this->showClientLogosModal = true;
        $this->skipRender();
    }

    public function closeModal(array $client, bool $delete = false): void
    {
        $key = array_search($client['id'], array_column($this->clients, 'id'));

        if (! $delete) {
            if ($key === false) {
                $this->clients[] = $client;
            } else {
                $this->clients[$key] = $client;
            }
        } elseif ($key !== false) {
            unset($this->clients[$key]);
        }

        $this->index = 0;
        $this->showClientModal = false;
        $this->skipRender();
    }

    public function closeLogosModal(): void
    {
        $this->showClientLogosModal = false;
        $this->skipRender();
    }

    public function delete(): void
    {
        $this->dispatch('delete')->to('settings.client-edit');
    }
}
