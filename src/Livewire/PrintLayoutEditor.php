<?php

namespace FluxErp\Livewire;

use FluxErp\Livewire\Forms\PrintLayoutForm;
use FluxErp\Models\Client;
use FluxErp\Models\Media;
use FluxErp\Models\PrintLayout;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Fluent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PrintLayoutEditor extends Component
{

    use Actions,WithFileUploads;

    public array $availableClients = [];

    public Client $client;

    public array $model = [];

    public string $subject = '';

    #[Url]
    public string $layoutModel;

    #[Url]
    public string $name;

    public ?int $selectedClientId = null;

    public PrintLayoutForm $form;

    private function arrayToFluent(array $array): Fluent
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = array_is_list($value)
                    ? array_map(fn($item) => is_array($item) ? $this->arrayToFluent($item) : $item, $value)
                    : $this->arrayToFluent($value);
            }
        }
        return new Fluent($array);
    }

    #[Renderless]
    public function clientToJson (): array
    {
        $client = $this->client->toArray();
        $bankConnections = $this->client->bankConnections->map(function ($bankConnection) {;
            return [
                'id' => $bankConnection->id,
            ];
        })->toArray();

        return [
            'client' => $client,
            'bank_connections' => $bankConnections,
        ];
    }

    public function mount(): void
    {
        $this->subject = $this->name;
        $this->availableClients = resolve_static(Client::class, 'query')
            ->orderBy('name')
            ->get(['id','name'])
            ->toArray();

        if($this->availableClients) {
            $this->client = resolve_static(Client::class, 'query')
                ->whereKey(reset($this->availableClients)['id'])
                ->first();

            $this->selectedClientId = $this->client->id;

            $layout = PrintLayout::query()
                ->where('name', 'flux::printing.' . $this->layoutModel . '.' . $this->name)
                ->where('client_id', $this->selectedClientId)
                ->first();

            if($layout) {
                $this->form->fill($layout->toArray());
            } else {
                $this->form->fill([
                    'client_id' => $this->selectedClientId,
                    'name' => 'flux::printing.' . $this->layoutModel . '.' . $this->name,
                    // TODO: need to map the model type to the correct morph alias
                    'model_type' => $this->layoutModel,
                ]);
            }

        }


        // depending on the print layout set the model data
        $this->model = [
            'order_date'=> Carbon::make('2023-10-01'),
            'customer_number' => 'CUST123',
            'order_number' => 'ORD123456',
            'commission' => 5.00,
            'address_invoice' => [
                'company' => 'Example Company',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'addition' => 'Mr.',
                'street' => '456 Another St',
                'zip' => '67890',
                'city' => 'Another City',
            ],
        ];
    }

    public function getModelFluentProperty() : Fluent
    {
        return $this->arrayToFluent($this->model);
    }

    public function selectClient(int $clientId): void
    {
        if($clientId !== $this->selectedClientId) {
                $this->selectedClientId = $clientId;

                $this->client = resolve_static(Client::class, 'query')
                ->whereKey($this->selectedClientId)
                ->first();

            $layout = PrintLayout::query()
                ->where('name', 'flux::printing.' . $this->layoutModel . '.' . $this->name)
                ->where('client_id', $this->selectedClientId)
                ->first();

            if($layout) {
                $this->form->fill($layout->toArray());
            } else {
                $this->form->fill([
                'id'=> null,
                'client_id' => $this->selectedClientId,
                'name' => 'flux::printing.' . $this->layoutModel . '.' . $this->name,
                 // TODO: need to map the model type to the correct morph alias
                'model_type' => $this->layoutModel,
                ]);
            }
        }
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->form->save();
            return true;
        } catch (ValidationException|UnauthorizedException $e)
        {
            // TODO: import the library for exception_to_notifications
            exception_to_notifications($e, $this);
            return false;
        }
    }

    #[Layout('flux::layouts.print-layout-editor')]
    public function render(): View
    {
        return view('flux::livewire.a4-page-editor');
    }

}
