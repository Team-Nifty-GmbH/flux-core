<?php

namespace FluxErp\Livewire;

use FluxErp\Livewire\Forms\PrintLayoutForm;
use FluxErp\Models\Client;
use FluxErp\Models\PrintLayout;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Fluent;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PrintLayoutEditor extends Component
{

    public array $availableClients = [];

    public Client $client;

    public array $model = [];

    public string $subject = 'Header';

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

    public function mount(): void
    {
        $this->availableClients = resolve_static(Client::class, 'query')
            ->orderBy('name')
            ->get(['id','name'])
            ->toArray();

        if($this->availableClients) {
            $this->client = resolve_static(Client::class, 'query')
                ->whereKey(reset($this->availableClients)['id'])
                ->first();

            $this->selectedClientId = $this->client->id;
        }


        // depending on the print layout set the model data
        $this->model = [
            'order' => [
                'oder_number' => 'ORD123456',
                'invoice_number' => 'INV123456',
                'invoice_date' => Carbon::make('2023-10-01'),
                'total_gross_price' => 100.00,
                'total_paid' => 50.00,
                'balance' => 50.00,
            ],
            'order_date'=> Carbon::make('2023-10-01'),
            'address_invoice' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'addition' => 'Mr.',
                'street' => '456 Another St',
                'zip' => '67890',
                'city' => 'Another City',
            ],
            'orderPositions' => [
                [
                    'name' => 'Product 1',
                    'amount' => 2,
                    'slug_position' => 'product-1',
                    'is_alternative' => true,
                    'product_number' => 'PROD123',
                    'description' => 'Description of Product 1',
                    'is_free_text' => false,
                    'is_bundle_position' => false,
                    'total_base_net_price' => 50.00,
                    'total_base_gross_price' => 60.00,
                    'total_net_price' => 40.00,
                    'total_gross_price' => 48.00,
                ],
            ],
            'discounts' => [],
            'total_net_price' => 80.00,
            'total_gross_price' => 100.00,
            'footer' => '<p>Thank you for your business!</p>',
        ];
    }

    public function getModelFluentProperty()
    {
        return $this->arrayToFluent($this->model);
    }

    public function orderPrint():View
    {
        return view('flux::printing.order.order',
        [
         'model' => $this->getModelFluentProperty(),
         'client' => $this->client,
        ]);
    }

    public function updatedSelectedClientId()
    {
        if($this->selectedClientId !== null && $this->selectedClientId !== $this->client->id) {
            $this->client = resolve_static(Client::class, 'query')
                ->whereKey($this->selectedClientId)
                ->first();
        }
    }

    #[Layout('flux::layouts.print-layout-editor',[
        'subject' => 'Layout Editor',
    ])]
    public function render(): View
    {
        return view('flux::livewire.a4-page-editor');
    }

}
