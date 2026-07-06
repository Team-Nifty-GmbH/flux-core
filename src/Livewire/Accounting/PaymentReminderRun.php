<?php

namespace FluxErp\Livewire\Accounting;

use Carbon\Carbon;
use FluxErp\Actions\PaymentReminder\BundlePaymentReminders;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\View\Printing\PaymentReminder\PaymentReminderView;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class PaymentReminderRun extends Component
{
    use Actions;

    #[Locked]
    public array $groups = [];

    /** Selected order ids (selection is per invoice). */
    public array $selectedOrders = [];

    /** Editable recipient email per group, keyed by group key. */
    public array $recipientEmails = [];

    public ?string $filterLevel = null;

    public ?string $search = null;

    public ?int $minOverdueDays = null;

    public string $sort = 'overdue_days_desc';

    #[Locked]
    public ?string $previewSrc = null;

    public bool $showPreview = false;

    public function mount(): void
    {
        $this->loadData();
    }

    public function render(): View
    {
        return view('flux::livewire.accounting.payment-reminder-run');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['filterLevel', 'search', 'minOverdueDays', 'sort'], true)) {
            $this->loadData();
        }
    }

    #[Computed]
    public function isEmpty(): bool
    {
        return blank($this->groups);
    }

    public function loadData(): void
    {
        $today = Carbon::today();

        $orders = resolve_static(Order::class, 'query')
            ->wherePaymentReminderDue()
            ->when(
                filled($this->filterLevel),
                fn (Builder $query) => $query->where(
                    'payment_reminder_current_level',
                    (int) $this->filterLevel - 1
                )
            )
            ->when(
                $this->minOverdueDays > 0,
                fn (Builder $query) => $query->whereDate(
                    'payment_reminder_next_date',
                    '<=',
                    $today->copy()->subDays($this->minOverdueDays)->toDateString()
                )
            )
            ->when(
                filled($this->search),
                fn (Builder $query) => $query->where(
                    fn (Builder $query) => $query
                        ->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas(
                            'contact',
                            fn (Builder $query) => $query
                                ->where('customer_number', 'like', '%' . $this->search . '%')
                        )
                )
            )
            ->with(['contact:id,customer_number'])
            ->get();

        $groups = $orders
            ->groupBy(fn (Order $order) => $order->contact_id . '-' . ((int) $order->payment_reminder_current_level + 1))
            ->map(function (Collection $group) use ($today): array {
                $order = $group->first();
                $address = $order->resolveMailablePaymentReminderAddress();

                $orders = $group
                    ->map(fn (Order $order) => [
                        'id' => $order->getKey(),
                        'invoice_number' => $order->invoice_number,
                        'invoice_date' => $order->invoice_date?->isoFormat('L'),
                        'balance' => $order->balance,
                        'next_date' => $order->payment_reminder_next_date?->isoFormat('L'),
                        'overdue_days' => $order->payment_reminder_next_date
                            ? (int) abs($order->payment_reminder_next_date->startOfDay()->diffInDays($today))
                            : 0,
                    ])
                    ->values()
                    ->all();

                return [
                    'key' => $order->contact_id . '-' . ((int) $order->payment_reminder_current_level + 1),
                    'contact_id' => $order->contact_id,
                    'contact_name' => $address?->getLabel() ?? $order->contact?->customer_number,
                    'recipient_email' => $address?->email_primary,
                    'next_level' => (int) $order->payment_reminder_current_level + 1,
                    'order_count' => $group->count(),
                    'total_balance' => $group->sum('balance'),
                    'max_overdue_days' => collect($orders)->max('overdue_days') ?? 0,
                    'orders' => $orders,
                ];
            })
            ->values();

        $this->groups = $this->sortGroups($groups)->all();

        // Default to everything selected so the user only has to deselect.
        // Ids are kept as strings to match the checkbox `value` attributes.
        $this->selectedOrders = collect($this->groups)
            ->flatMap(fn (array $group) => array_column($group['orders'], 'id'))
            ->map(fn (int $id) => (string) $id)
            ->all();

        // Prefill the editable recipient with the resolved address; stays overridable.
        $this->recipientEmails = collect($this->groups)
            ->mapWithKeys(fn (array $group) => [$group['key'] => $group['recipient_email']])
            ->all();
    }

    public function preview(int $orderId): void
    {
        $order = resolve_static(Order::class, 'query')
            ->whereKey($orderId)
            ->with(['orderType', 'contact'])
            ->first();

        if (! $order) {
            return;
        }

        $reminder = app(PaymentReminder::class);
        $reminder->order_id = $order->getKey();
        $reminder->reminder_level = (int) $order->payment_reminder_current_level + 1;
        $reminder->setRelation('order', $order);

        try {
            $view = PaymentReminderView::make($reminder)
                ->preview()
                ->print();

            $this->previewSrc = 'data:application/pdf;base64,' . base64_encode($view->pdf->output());
            $this->showPreview = true;
        } catch (Throwable $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function sendGroup(string $key): void
    {
        $group = collect($this->groups)->firstWhere('key', $key);

        if (! $group) {
            return;
        }

        $groupIds = array_column($group['orders'], 'id');
        $orderIds = array_values(array_intersect($groupIds, $this->selectedOrders)) ?: $groupIds;

        $this->sendBundle($orderIds);
        $this->loadData();
    }

    public function sendSelected(): void
    {
        if (! $this->selectedOrders) {
            return;
        }

        $this->sendBundle($this->selectedOrders);
        $this->loadData();
    }

    public function toggleGroup(string $key): void
    {
        $group = collect($this->groups)->firstWhere('key', $key);

        if (! $group) {
            return;
        }

        $groupIds = array_map('strval', array_column($group['orders'], 'id'));
        $allSelected = empty(array_diff($groupIds, $this->selectedOrders));

        $this->selectedOrders = $allSelected
            ? array_values(array_diff($this->selectedOrders, $groupIds))
            : array_values(array_unique(array_merge($this->selectedOrders, $groupIds)));
    }

    protected function sendBundle(array $orderIds): void
    {
        if (! $orderIds) {
            return;
        }

        // Pair every order with its group's editable recipient so validation
        // ties the recipient to its invoice instead of a separate keyed map.
        $emailByOrderId = [];

        foreach ($this->groups as $group) {
            $email = data_get($this->recipientEmails, $group['key']);

            foreach ($group['orders'] as $order) {
                $emailByOrderId[$order['id']] = filled($email) ? $email : null;
            }
        }

        $orders = collect($orderIds)
            ->map(fn ($orderId): array => [
                'id' => (int) $orderId,
                'recipient' => data_get($emailByOrderId, (int) $orderId),
            ])
            ->all();

        try {
            // Fans the sends out as a monitored batch; the batch progress toast
            // gives the user feedback, so no extra toast is raised here.
            BundlePaymentReminders::make(['orders' => $orders])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
    }

    protected function sortGroups(Collection $groups): Collection
    {
        return match ($this->sort) {
            'overdue_days_asc' => $groups->sortBy('max_overdue_days')->values(),
            'balance_desc' => $groups->sortByDesc(fn (array $group) => (float) $group['total_balance'])->values(),
            'balance_asc' => $groups->sortBy(fn (array $group) => (float) $group['total_balance'])->values(),
            'contact_asc' => $groups->sortBy('contact_name')->values(),
            'contact_desc' => $groups->sortByDesc('contact_name')->values(),
            default => $groups->sortByDesc('max_overdue_days')->values(),
        };
    }
}
