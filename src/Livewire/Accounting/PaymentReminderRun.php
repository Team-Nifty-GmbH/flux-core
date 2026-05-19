<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\PaymentReminder\BundlePaymentReminders;
use FluxErp\Models\Order;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PaymentReminderRun extends Component
{
    use Actions;

    #[Locked]
    public array $groups = [];

    public array $selected = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function render(): View
    {
        return view('flux::livewire.accounting.payment-reminder-run');
    }

    public function loadData(): void
    {
        $orders = resolve_static(Order::class, 'query')
            ->wherePaymentReminderDue()
            ->with(['contact:id,customer_number', 'orderType:id,order_type_enum'])
            ->get()
            ->filter(fn (Order $order) => ! $order->orderType->order_type_enum->isPurchase()
                && $order->orderType->order_type_enum->multiplier() === 1
            );

        $this->groups = $orders
            ->groupBy(fn (Order $order) => $order->contact_id . '-' . ((int) $order->payment_reminder_current_level + 1))
            ->map(function (Collection $group): array {
                $first = $group->first();
                $address = $first->resolveMailablePaymentReminderAddress();

                return [
                    'key' => $first->contact_id . '-' . ((int) $first->payment_reminder_current_level + 1),
                    'contact_id' => $first->contact_id,
                    'contact_name' => $address?->getLabel() ?? $first->contact?->customer_number,
                    'recipient_email' => $address?->email_primary,
                    'next_level' => (int) $first->payment_reminder_current_level + 1,
                    'order_count' => $group->count(),
                    'total_balance' => $group->sum('balance'),
                    'orders' => $group
                        ->map(fn (Order $order) => [
                            'id' => $order->getKey(),
                            'invoice_number' => $order->invoice_number,
                            'invoice_date' => $order->invoice_date?->toDateString(),
                            'balance' => $order->balance,
                            'next_date' => $order->payment_reminder_next_date?->toDateString(),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function isEmpty(): bool
    {
        return blank($this->groups);
    }

    public function sendGroup(string $key): void
    {
        $group = collect($this->groups)->firstWhere('key', $key);

        if (! $group) {
            return;
        }

        $this->sendBundle(array_column($group['orders'], 'id'));
        $this->loadData();
    }

    public function sendSelected(): void
    {
        if (! $this->selected) {
            return;
        }

        $orderIds = collect($this->groups)
            ->whereIn('key', $this->selected)
            ->flatMap(fn (array $group) => array_column($group['orders'], 'id'))
            ->all();

        $this->sendBundle($orderIds);
        $this->selected = [];
        $this->loadData();
    }

    public function sendAll(): void
    {
        $orderIds = collect($this->groups)
            ->flatMap(fn (array $group) => array_column($group['orders'], 'id'))
            ->all();

        $this->sendBundle($orderIds);
        $this->loadData();
    }

    protected function sendBundle(array $orderIds): void
    {
        if (! $orderIds) {
            return;
        }

        try {
            $result = BundlePaymentReminders::make(['order_ids' => $orderIds])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $sent = (int) data_get($result, 'sent_groups', 0);
        $failed = (int) data_get($result, 'failed_groups', 0);

        if ($sent > 0) {
            $this->toast()
                ->success(__(':count payment reminder(s) sent', ['count' => $sent]))
                ->send();
        }

        if ($failed > 0) {
            $this->toast()
                ->warning(__(':count payment reminder(s) failed', ['count' => $failed]))
                ->send();
        }
    }
}
