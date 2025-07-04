<?php

namespace FluxErp\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Fluent;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PrintLayoutEditor extends Component
{
    public array $client = [];

    public array $model = [];

    public array $summary = [];

    public string $subject = 'Header';

    private function arrayToFluent(array $array)
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
        $this->client= [
            'name' => 'Client Name',
            'ceo' => 'CEO Name',
            'street' => '123 Main St',
            'postcode' => '12345',
            'city' => 'City Name',
            'phone' => '+1234567890',
            'vat_id' => 'VAT123456',
            'logo_small' => asset('/pwa-icons/icons-192.png'),
            'postal_address_one_line' => '123 Main St, City Name, 12345',
            'bankConnections' => [
                [
                    'bank_name' => 'Bank Name',
                    'iban' => 'DE89370400440532013000',
                    'bic' => 'COBADEFFXXX',
                ],
                [
                    'bank_name' => 'Another Bank',
                    'iban' => 'DE89370400440532013001',
                    'bic' => 'COBADEFFYYY',]
                ],
            ];

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

        $this->summary = [
            [
                'name' => 'Subtotal',
                'slug_position' => 'slug-subtotal',
                'total_net_price' => 80.00,
            ]
        ];
    }

    public function getClientFluentProperty()
    {
        return $this->arrayToFluent($this->client);
    }

    public function getModelFluentProperty()
    {
        return $this->arrayToFluent($this->model);
    }

    public function getSummaryFluentProperty()
    {
        return $this->arrayToFluent($this->summary);
    }

    public function orderPrint():View
    {
        return view('flux::printing.order.order',
        [
         'model' => $this->getModelFluentProperty(),
         'client' => $this->getClientFluentProperty(),
         'summary' => $this->getSummaryFluentProperty(),
        ]);
    }

    #[Layout('flux::layouts.print-layout-editor',[
        'subject' => 'Layout Editor',
    ])]
    public function render(): View
    {
        return view('flux::livewire.a4-page');
    }

}
