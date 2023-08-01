<?php

namespace FluxErp\Http\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use FluxErp\Http\Livewire\DataTables\OrderList;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class OutstandingInvoices extends Component implements UserWidget
{
    public float $sum = 0;

    public function mount(): void
    {
        $this->calculateSum();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.outstanding-invoices',
            [
                'currency' => Currency::query()
                    ->where('is_default', true)
                    ->first()
                    ?->toArray() ?: []
            ]
        );
    }

    public function viewOrders(): void
    {
        $filters = [
            'userFilters' => [
                [
                    [
                        'column' => 'is_locked',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'column' => 'invoice_number',
                        'operator' => 'is not null',
                    ],
                    [
                        'column' => 'total_gross_price',
                        'operator' => '>',
                        'value' => 0,
                    ],
                    [
                        'column' => 'payment_state',
                        'operator' => '!=',
                        'value' => 'paid',
                    ],
                ],
            ],
        ];

        Session::put(config('tall-datatables.cache_key') . '.filter:' . OrderList::class, $filters);

        $this->redirect(route('orders'));
    }

    public function calculateSum(): void
    {
        $sum = bcsub(Order::query()->sum('total_gross_price'), Transaction::query()->sum('amount'));
        $this->sum = bcround($sum, 2);
    }

    public static function getLabel(): string
    {
        return __('Outstanding Invoices');
    }
}
