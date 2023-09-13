<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Http\Requests\CreateClientRequest;
use FluxErp\Http\Requests\UpdateClientRequest;
use FluxErp\Models\Country;
use FluxErp\Services\ClientService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class ClientEdit extends Component
{
    use Actions;

    public array $client;

    public array $countries;

    public bool $isNew = true;

    protected $listeners = [
        'show',
        'save',
        'delete',
    ];

    public function getRules(): array
    {
        $rules = $this->isNew ?
            (new CreateClientRequest())->rules() : (new UpdateClientRequest())->getRules($this->client);

        return Arr::prependKeysWith($rules, 'client.');
    }

    public function boot(): void
    {
        $this->client = array_fill_keys(
            array_keys((new CreateClientRequest())->rules()),
            null
        );

        $this->client['is_active'] = true;

        $this->countries = Country::query()
            ->orderBy('iso_alpha2', 'ASC')
            ->select(['id', 'name', 'iso_alpha2'])
            ->get()
            ->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.client-edit');
    }

    public function show(array $client = []): void
    {
        $this->resetErrorBag();
        $this->client = $client ?:
            array_fill_keys(
                array_keys((new CreateClientRequest())->rules()),
                null
            );

        if (is_null($this->client['is_active'] ?? null)) {
            $this->client['is_active'] = true;
        }

        $this->isNew = ! array_key_exists('id', $this->client);
    }

    public function save(): void
    {
        if (($this->isNew && ! user_can('api.clients.{id}.post')) ||
            (! $this->isNew && ! user_can('api.clients.{id}.put'))
        ) {
            $this->notification()->error(
                __('insufficient permissions'),
                __('You have not the rights to modify this record')
            );

            return;
        }

        $validated = $this->validate();

        $clientService = new ClientService();
        $response = $clientService->{$this->isNew ? 'create' : 'update'}($validated['client']);

        if (! $this->isNew && $response['status'] > 299) {
            $this->notification()->error(
                implode(',', array_keys($response['errors'])),
                implode(', ', Arr::dot($response['errors']))
            );

            return;
        }

        $this->notification()->success(__('Client saved successful.'));

        $client = $this->isNew ? $response->toArray() : $response['data']->toArray();

        $this->skipRender();
        $this->dispatch('closeModal', $client);
    }

    public function delete(): void
    {
        if (! user_can('api.clients.{id}.delete')) {
            return;
        }

        (new ClientService())->delete($this->client['id']);

        $this->skipRender();
        $this->dispatch('closeModal', $this->client, true);
    }

    public function addDay(): void
    {
        $this->client['opening_hours'][] = [
            'day' => '',
            'start' => '',
            'end' => '',
        ];
    }

    public function removeDay(int $day): void
    {
        unset($this->client['opening_hours'][$day]);
    }
}
