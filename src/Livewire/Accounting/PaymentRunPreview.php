<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Actions\PaymentRun\DeletePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Models\Order;
use FluxErp\States\Order\PaymentState\Open as OrderOpen;
use FluxErp\States\PaymentRun\Open;
use FluxErp\Support\PaymentRunPositionBuilder;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PaymentRunPreview extends Component
{
    use Actions;

    public array $groups = [];

    #[Locked]
    public ?string $paymentRunTypeEnum = null;

    public function mount(): void
    {
        $orderIds = session()->pull('payment_run_preview_orders', []);
        $paymentRunTypeEnum = session()->pull('payment_run_type_enum');
        $this->paymentRunTypeEnum = $paymentRunTypeEnum instanceof PaymentRunTypeEnum
            ? $paymentRunTypeEnum->value
            : $paymentRunTypeEnum;

        if (! $orderIds || ! $this->paymentRunTypeEnum) {
            $this->redirectRoute('accounting.money-transfer', navigate: true);

            return;
        }

        $orders = resolve_static(Order::class, 'query')
            ->whereKey($orderIds)
            ->with([
                'contactBankConnection.sepaMandates' => function (HasMany $query): void {
                    $query->select(['id', 'contact_bank_connection_id', 'sepa_mandate_type_enum'])
                        ->whereNotNull('signed_date');
                },
            ])
            ->get();

        $creditNotes = $this->suggestedCreditNotes($orders);
        $orders->merge($creditNotes)->loadMissing([
            'addressInvoice',
            'orderType',
            'contactBankConnection:id,contact_id,iban,bic,account_holder',
        ]);

        $this->groups = app(PaymentRunPositionBuilder::class)->build($orders, $creditNotes);

        $sepaMandateTypes = $orders->mapWithKeys(fn (Order $order) => [
            $order->getKey() => $order->contactBankConnection?->sepaMandates->last()?->sepa_mandate_type_enum,
        ]);

        foreach ($this->groups as $groupIndex => $group) {
            $this->groups[$groupIndex]['type'] = $sepaMandateTypes->get(data_get($group, 'orders.0.id'));
        }
    }

    public function render(): View
    {
        return view('flux::livewire.accounting.payment-run-preview');
    }

    #[Renderless]
    public function applyAmount(string $groupKey, int $orderId, string $amount): void
    {
        $this->setRowAmount($groupKey, $orderId, fn (): string => bcround($amount ?: 0, 2));
    }

    #[Renderless]
    public function applyBalance(string $groupKey, int $orderId): void
    {
        $this->setRowAmount($groupKey, $orderId, fn (array $row): string => bcabs(bcround(data_get($row, 'balance') ?? 0, 2)));
    }

    #[Renderless]
    public function applyDiscount(string $groupKey, int $orderId): void
    {
        $this->setRowAmount(
            $groupKey,
            $orderId,
            fn (array $row): ?string => data_get($row, 'balance_due_discount') && data_get($row, 'payment_discount_percent')
                ? bcabs(bcround(data_get($row, 'balance_due_discount'), 2))
                : null
        );
    }

    #[Renderless]
    public function cancel(): void
    {
        $this->redirectRoute('accounting.money-transfer', navigate: true);
    }

    #[Renderless]
    public function createPaymentRun(): void
    {
        $paymentRunTypeEnum = PaymentRunTypeEnum::from($this->paymentRunTypeEnum);

        $positions = collect($this->groups)
            ->map(fn (array $group) => [
                'type' => data_get($group, 'type'),
                'orders' => collect(data_get($group, 'orders', []))
                    ->reject(fn (array $row) => bccomp(bcround(data_get($row, 'amount'), 2), '0', 2) === 0)
                    ->map(fn (array $row) => [
                        'order_id' => data_get($row, 'id'),
                        'amount' => bcmul(
                            bcabs(bcround(data_get($row, 'amount'), 2)),
                            (string) data_get($row, 'multiplier')
                        ),
                    ])
                    ->values()
                    ->toArray(),
            ])
            ->filter(fn (array $position) => $position['orders'])
            ->values();

        if ($positions->isEmpty()) {
            return;
        }

        $groupedPositions = $paymentRunTypeEnum === PaymentRunTypeEnum::DirectDebit
            ? $positions->groupBy('type')->toArray()
            : [null => $positions->toArray()];

        $paymentRuns = [];
        foreach ($groupedPositions as $type => $positionsForType) {
            try {
                $paymentRuns[] = CreatePaymentRun::make([
                    'state' => Open::$name,
                    'payment_run_type_enum' => $paymentRunTypeEnum,
                    'sepa_mandate_type_enum' => $paymentRunTypeEnum === PaymentRunTypeEnum::DirectDebit
                        ? SepaMandateTypeEnum::tryFrom($type) ?? SepaMandateTypeEnum::BASIC
                        : null,
                    'positions' => $positionsForType,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();

                $this->toast()
                    ->success(__(':model created', ['model' => __('Payment Run')]))
                    ->send();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                foreach ($paymentRuns as $paymentRun) {
                    try {
                        DeletePaymentRun::make(['id' => $paymentRun->getKey()])
                            ->validate()
                            ->execute();
                    } catch (ValidationException $e) {
                        exception_to_notifications($e, $this);
                    }
                }

                return;
            }
        }

        $this->redirectRoute('accounting.payment-runs', navigate: true);
    }

    #[Renderless]
    public function removeOrder(string $groupKey, int $orderId): void
    {
        [$groupIndex, $rowIndex] = $this->locate($groupKey, $orderId);

        if ($rowIndex !== false) {
            array_splice($this->groups[$groupIndex]['orders'], $rowIndex, 1);

            if ($this->groups[$groupIndex]['orders']) {
                $this->recapAndRecalculate($groupIndex);
            } else {
                array_splice($this->groups, $groupIndex, 1);
            }
        }

        if (! $this->groups) {
            $this->redirectRoute('accounting.money-transfer', navigate: true);
        }
    }

    protected function suggestedCreditNotes(Collection $orders): Collection
    {
        if (PaymentRunTypeEnum::from($this->paymentRunTypeEnum) !== PaymentRunTypeEnum::MoneyTransfer) {
            return collect();
        }

        $contactIds = $orders->pluck('contact_id')->filter()->unique();

        if ($contactIds->isEmpty()) {
            return collect();
        }

        return resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('contact_id', $contactIds->all())
            ->whereNotNull('invoice_number')
            ->where('balance', '>', 0)
            ->whereState('payment_state', OrderOpen::class)
            ->whereRelation(
                'orderType',
                'order_type_enum',
                OrderTypeEnum::PurchaseRefund->value
            )
            ->whereKeyNot($orders->modelKeys())
            ->whereDoesntHave('paymentRuns')
            ->get();
    }

    protected function locate(string $groupKey, int $orderId): array
    {
        $groupIndex = array_search($groupKey, array_column($this->groups, 'key'), true);

        return [
            $groupIndex,
            $groupIndex === false
                ? false
                : array_search($orderId, array_column($this->groups[$groupIndex]['orders'], 'id'), true),
        ];
    }

    protected function recapAndRecalculate(int $groupIndex): void
    {
        $builder = app(PaymentRunPositionBuilder::class);

        $this->groups[$groupIndex]['orders'] = $builder->recap($this->groups[$groupIndex]['orders']);
        $this->groups[$groupIndex]['amount'] = $builder->total($this->groups[$groupIndex]['orders']);
    }

    private function setRowAmount(string $groupKey, int $orderId, callable $amount): void
    {
        [$groupIndex, $rowIndex] = $this->locate($groupKey, $orderId);

        if ($rowIndex === false || ($value = $amount($this->groups[$groupIndex]['orders'][$rowIndex])) === null) {
            return;
        }

        $this->groups[$groupIndex]['orders'][$rowIndex]['amount'] = $value;
        $this->recapAndRecalculate($groupIndex);
    }
}
