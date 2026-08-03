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

    /**
     * Orders whose reminders were dispatched in this session. The sends run as a
     * queued batch, so the database still lists them as due when the component
     * reloads; hide them optimistically instead of forcing a manual reload.
     */
    #[Locked]
    public array $sentOrderIds = [];

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
                $this->sentOrderIds,
                fn (Builder $query, array $sentOrderIds) => $query->whereKeyNot($sentOrderIds)
            )
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
                        ->orWhereHas(
                            'contact.addresses',
                            fn (Builder $query) => $query
                                ->where('company', 'like', '%' . $this->search . '%')
                                ->orWhere('name', 'like', '%' . $this->search . '%')
                                ->orWhere('firstname', 'like', '%' . $this->search . '%')
                                ->orWhere('lastname', 'like', '%' . $this->search . '%')
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

        $this->handleSendResult($this->sendBundle($orderIds), $orderIds);

        $this->loadData();
    }

    public function sendSelected(): void
    {
        if (! $this->selectedOrders) {
            return;
        }

        $this->handleSendResult($this->sendBundle($this->selectedOrders), $this->selectedOrders);

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

    /**
     * Only the invoices that actually made it into the send batch may disappear from
     * the run. The rest stay listed together with the reason they were held back, so
     * a misconfigured reminder level can no longer look like a completed run.
     */
    protected function handleSendResult(?array $result, array $orderIds): void
    {
        if (is_null($result)) {
            return;
        }

        $unsendable = data_get($result, 'unsendable', []);
        $unsendableIds = array_column($unsendable, 'id');

        $this->markAsSent(array_values(array_diff(array_map('intval', $orderIds), $unsendableIds)));

        if (! $unsendable) {
            return;
        }

        $this->toast()
            ->warning(
                __(':count of :total reminders were not sent', [
                    'count' => count($unsendable),
                    'total' => count($orderIds),
                ]),
                collect($unsendable)
                    ->groupBy('reason')
                    ->map(fn (Collection $entries, string $reason) => $reason . ' ('
                        . trans_choice('{1} :count invoice|[2,*] :count invoices', $entries->count())
                        . ')'
                    )
                    ->implode(' ')
            )
            ->send();
    }

    protected function markAsSent(array $orderIds): void
    {
        $this->sentOrderIds = array_values(array_unique(array_merge(
            $this->sentOrderIds,
            array_map('intval', $orderIds)
        )));

        $this->selectedOrders = array_values(array_diff(
            $this->selectedOrders,
            array_map('strval', $orderIds)
        ));
    }

    protected function sendBundle(array $orderIds): ?array
    {
        if (! $orderIds) {
            return null;
        }

        $orderIds = array_map('intval', $orderIds);

        // Pair every order with its group's editable recipient so validation
        // ties the recipient to its invoice instead of a separate keyed map.
        $orders = [];

        foreach ($this->groups as $group) {
            $email = data_get($this->recipientEmails, $group['key']);

            foreach ($group['orders'] as $order) {
                if (in_array($order['id'], $orderIds, true)) {
                    $orders[] = [
                        'id' => $order['id'],
                        'recipient' => filled($email) ? $email : null,
                    ];
                }
            }
        }

        try {
            // Fans the sends out as a monitored batch; the batch progress toast
            // reports the deliveries, so only the held-back invoices are toasted here.
            return BundlePaymentReminders::make(['orders' => $orders])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
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
