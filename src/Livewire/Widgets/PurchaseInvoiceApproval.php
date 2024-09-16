<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\States\Task\TaskState;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PurchaseInvoiceApproval extends Component
{
    use Widgetable;

    public function render(): View|Factory
    {
        return view(
            'flux::livewire.widgets.purchase-invoice-approval',
            [
                'invoices' => resolve_static(\FluxErp\Models\Order::class, 'query')
                    ->where('approval_user_id', auth()->id())
                    ->where('is_confirmed', false)
                    ->with([
                        'contact.media',
                        'contact.invoiceAddress',
                        'currency:id,symbol',
                    ])
                    ->orderBy('created_at', 'desc')
                    ->get(['id', 'contact_id', 'currency_id', 'total_gross_price', 'created_at']),
            ]
        );
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }
}
