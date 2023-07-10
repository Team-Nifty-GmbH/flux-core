<?php

namespace FluxErp\Http\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use FluxErp\Http\Livewire\DataTables\OrderList;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class OutstandingInvoices extends Component implements UserWidget
{
    public float $sum = 0;

    public function mount()
    {
        $this->calculateSum();
    }

    public function render()
    {
        return view('flux::livewire.widgets.outstanding-invoices',
            [
                'currency' => Currency::query()->where('is_default', true)->first()->toArray()
            ]
        );
    }

    public function viewOrders()
    {
        $filters = [
            'userFilters' => [
                [
                    [
                        'column' => 'is_locked',
                        'operator' => '=',
                        'value' => '1',
                        'relation' => '',
                    ],
                    [
                        'column' => 'invoice_number',
                        'operator' => '!=',
                        'value' => 'null',
                        'relation' => '',
                    ],
                    [
                        'column' => 'total_gross_price',
                        'operator' => '>',
                        'value' => '0',
                        'relation' => '',
                    ],
                    [
                        'column' => 'payment_state',
                        'operator' => '!=',
                        'value' => 'paid',
                        'relation' => '',
                    ],
                ],
            ],
        ];

        Session::put(config('tall-datatables.cache_key') . '.filter:' . OrderList::class, $filters);

        return redirect()->route('orders');
    }

    public function calculateSum()
    {
        $sum = Order::query()->sum('total_gross_price') - Transaction::query()->sum('amount');
        $this->sum = round($sum, 2);
    }

    public static function getLabel(): string
    {
        return __('Outstanding Invoices');
    }
}
