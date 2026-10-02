<?php

namespace FluxErp\Livewire\Widgets\Contact;

use Carbon\CarbonInterface;
use FluxErp\Livewire\Contact\Statistics;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Order;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;

class PaymentBehavior extends ValueBox
{
    use IsTimeFrameAwareWidget;

    public ?int $contactId = null;

    public static function dashboardComponent(): array|string
    {
        return Statistics::class;
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $invoices = resolve_static(Order::class, 'query')
            ->where('contact_id', $this->contactId)
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->when($this->getStart(), fn (Builder $query, CarbonInterface $start) => $query->whereDate('invoice_date', '>=', $start))
            ->when($this->getEnd(), fn (Builder $query, CarbonInterface $end) => $query->whereDate('invoice_date', '<=', $end))
            ->revenue();

        $averageDays = $invoices->clone()
            ->whereState('payment_state', Paid::class)
            ->whereHas('transactions')
            ->with([
                'transactions' => fn (BelongsToMany $query) => $query->select([
                    'transactions.id',
                    'transactions.value_date',
                ]),
            ])
            ->get(['id', 'invoice_date'])
            ->avg(fn (Order $order): int => (int) $order->invoice_date->diffInDays(
                Carbon::parse($order->transactions->max('value_date'))
            ));

        $openInvoices = $invoices->clone()
            ->whereNotState('payment_state', Paid::class)
            ->where('balance', '>', 0);
        $openCount = $openInvoices->clone()->count();
        $overdueCount = $openInvoices->clone()
            ->whereDate('payment_target_date', '<', now())
            ->count();

        $this->sum = is_null($averageDays)
            ? '-'
            : __(':days days', ['days' => Number::format($averageDays, 0)]);
        $this->subValue = e(__(':percent % of open invoices overdue', [
            'percent' => $openCount ? Number::format(bcmul(bcdiv($overdueCount, $openCount, 9), 100, 9), 0) : 0,
        ]));
    }

    protected function icon(): string
    {
        return 'clock';
    }
}
