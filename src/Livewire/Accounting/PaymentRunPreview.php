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
                'contact:id,expense_ledger_account_id',
                'addressInvoice',
                'orderType',
                'contactBankConnection:id,contact_id,iban,bic,account_holder',
                'contactBankConnection.sepaMandates' => function (HasMany $query): void {
                    $query->select(['id', 'contact_bank_connection_id', 'sepa_mandate_type_enum'])
                        ->whereNotNull('signed_date');
                },
            ])
            ->get();

        $creditNotes = $this->suggestedCreditNotes($orders);
        $creditNoteIds = $creditNotes->pluck('id')->all();

        $builder = new PaymentRunPositionBuilder();
        $this->groups = $builder->build($orders, $creditNotes);

        $sepaMandateTypes = $orders->merge($creditNotes)->mapWithKeys(
            fn (Order $order) => [
                $order->getKey() => $order->contactBankConnection?->sepaMandates->last()?->sepa_mandate_type_enum,
            ]
        );

        foreach ($this->groups as $groupIndex => $group) {
            $this->groups[$groupIndex]['type'] = $sepaMandateTypes->get(data_get($group, 'orders.0.id'));

            foreach ($group['orders'] as $rowIndex => $row) {
                if (in_array($row['id'], $creditNoteIds, true)) {
                    $this->groups[$groupIndex]['orders'][$rowIndex]['is_suggested'] = true;
                }
            }
        }
    }

    public function render(): View
    {
        return view('flux::livewire.accounting.payment-run-preview');
    }

    #[Renderless]
    public function applyAmount(string $groupKey, int $orderId, string $amount): void
    {
        [$groupIndex, $rowIndex] = $this->locate($groupKey, $orderId);

        if ($groupIndex === null || $rowIndex === null) {
            return;
        }

        data_set(
            $this->groups,
            $groupIndex . '.orders.' . $rowIndex . '.amount',
            bcround($amount ?: 0, 2)
        );

        $this->recapAndRecalculate($groupIndex);
    }

    #[Renderless]
    public function applyBalance(string $groupKey, int $orderId): void
    {
        [$groupIndex, $rowIndex] = $this->locate($groupKey, $orderId);

        if ($groupIndex === null || $rowIndex === null) {
            return;
        }

        data_set(
            $this->groups,
            $groupIndex . '.orders.' . $rowIndex . '.amount',
            bcabs(bcround(data_get($this->groups, $groupIndex . '.orders.' . $rowIndex . '.balance') ?? 0, 2))
        );

        $this->recapAndRecalculate($groupIndex);
    }

    #[Renderless]
    public function applyDiscount(string $groupKey, int $orderId): void
    {
        [$groupIndex, $rowIndex] = $this->locate($groupKey, $orderId);

        if ($groupIndex === null
            || $rowIndex === null
            || ! data_get($this->groups, $groupIndex . '.orders.' . $rowIndex . '.balance_due_discount')
            || ! data_get($this->groups, $groupIndex . '.orders.' . $rowIndex . '.payment_discount_percent')
        ) {
            return;
        }

        data_set(
            $this->groups,
            $groupIndex . '.orders.' . $rowIndex . '.amount',
            bcabs(bcround(data_get($this->groups, $groupIndex . '.orders.' . $rowIndex . '.balance_due_discount'), 2))
        );

        $this->recapAndRecalculate($groupIndex);
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

        $skipped = [];

        $groups = collect($this->groups)
            ->reject(function (array $group) use (&$skipped): bool {
                $hasInvoice = collect(data_get($group, 'orders', []))
                    ->contains(fn (array $row) => ! data_get($row, 'is_credit_note'));

                if ($hasInvoice || bccomp((string) data_get($group, 'amount', '0'), '0', 2) !== 0) {
                    return false;
                }

                $skipped[] = data_get($group, 'contact_name') ?: data_get($group, 'account_holder');

                return true;
            })
            ->values();

        if ($skipped) {
            $this->toast()
                ->warning(__('Skipped :recipients because their credit notes have nothing to offset.', [
                    'recipients' => implode(', ', $skipped),
                ]))
                ->send();
        }

        if ($groups->isEmpty()) {
            return;
        }

        $positions = $groups
            ->map(fn (array $group) => [
                'contact_id' => data_get($group, 'contact_id'),
                'iban' => data_get($group, 'iban'),
                'bic' => data_get($group, 'bic'),
                'account_holder' => data_get($group, 'account_holder'),
                'type' => data_get($group, 'type', '__NO_TYPE__'),
                'orders' => collect(data_get($group, 'orders', []))
                    ->map(fn (array $row) => [
                        'order_id' => data_get($row, 'id'),
                        'amount' => bcmul(
                            bcabs(bcround(data_get($row, 'amount'), 2)),
                            (string) data_get($row, 'multiplier')
                        ),
                    ])
                    ->values()
                    ->toArray(),
            ]);

        $groupedPositions = $paymentRunTypeEnum === PaymentRunTypeEnum::DirectDebit
            ? $positions->groupBy('type')->toArray()
            : [null => $positions->toArray()];

        $paymentRuns = [];
        foreach ($groupedPositions as $type => $positionsForType) {
            $positionsForType = array_map(function (array $position) {
                data_forget($position, 'type');

                return $position;
            }, $positionsForType);

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
    public function removeOrder(int $orderId): void
    {
        foreach ($this->groups as $groupIndex => $group) {
            $rowIndex = $this->findRowIndex($group, $orderId);

            if ($rowIndex === null) {
                continue;
            }

            unset($this->groups[$groupIndex]['orders'][$rowIndex]);
            $this->groups[$groupIndex]['orders'] = array_values($this->groups[$groupIndex]['orders']);

            if (! $this->groups[$groupIndex]['orders']) {
                unset($this->groups[$groupIndex]);
                $this->groups = array_values($this->groups);
            } else {
                $this->recapAndRecalculate($groupIndex);
            }

            break;
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
            ->with([
                'contact:id,expense_ledger_account_id',
                'addressInvoice',
                'orderType',
                'contactBankConnection:id,contact_id,iban,bic,account_holder',
                'contactBankConnection.sepaMandates' => function (HasMany $query): void {
                    $query->select(['id', 'contact_bank_connection_id', 'sepa_mandate_type_enum'])
                        ->whereNotNull('signed_date');
                },
            ])
            ->get();
    }

    protected function locate(string $groupKey, int $orderId): array
    {
        foreach ($this->groups as $groupIndex => $group) {
            if ($group['key'] !== $groupKey) {
                continue;
            }

            return [$groupIndex, $this->findRowIndex($group, $orderId)];
        }

        return [null, null];
    }

    protected function findRowIndex(array $group, int $orderId): ?int
    {
        foreach ($group['orders'] as $rowIndex => $row) {
            if ($row['id'] === $orderId) {
                return $rowIndex;
            }
        }

        return null;
    }

    protected function recapAndRecalculate(int $groupIndex): void
    {
        $builder = new PaymentRunPositionBuilder();

        $this->groups[$groupIndex]['orders'] = $builder->recap($this->groups[$groupIndex]['orders']);
        $this->groups[$groupIndex]['amount'] = $builder->total($this->groups[$groupIndex]['orders']);
    }
}
