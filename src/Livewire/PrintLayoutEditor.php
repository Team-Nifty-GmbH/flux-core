<?php

namespace FluxErp\Livewire;

use Illuminate\View\View;
use Illuminate\Support\Fluent;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PrintLayoutEditor extends Component
{
    public array $client = [];

    public array $order = [];

    public string $subject = 'Print Layout Editor';

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
    }

    public function getClientFluentProperty()
    {
        return new Fluent($this->client);
    }

    public function getOrderFluentProperty()
    {
        return new Fluent($this->order);
    }

    public function orderPrint():View
    {
        return view('livewire.printing.order.order');
    }

    #[Layout('flux::layouts.print-layout-editor',[
        'subject' => 'Layout Editor',
    ])]
    public function render(): View
    {
        return view('flux::livewire.a4-page');
    }

}
