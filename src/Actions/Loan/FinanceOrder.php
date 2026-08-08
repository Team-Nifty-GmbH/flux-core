<?php

namespace FluxErp\Actions\Loan;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\LedgerBooking\CreateLedgerBooking;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Loan\FinanceOrderRuleset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceOrder extends FluxAction
{
    public static function models(): array
    {
        return [Loan::class];
    }

    protected function getRulesets(): string|array
    {
        return FinanceOrderRuleset::class;
    }

    public function performAction(): Loan
    {
        $loan = resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('loan_id'))
            ->firstOrFail();

        return DB::transaction(function () use ($loan): Loan {
            CreateLedgerBooking::make([
                'tenant_id' => $loan->tenant_id,
                'debit_ledger_account_id' => $this->getData('debit_ledger_account_id'),
                'credit_ledger_account_id' => $loan->ledger_account_id,
                'source_type' => morph_alias(Order::class),
                'source_id' => $this->getData('order_id'),
                'amount' => $this->getData('amount'),
                'booking_date' => $this->getData('booking_date'),
                'booking_text' => $this->getData('booking_text'),
                'note' => $this->getData('note'),
            ])
                ->validate()
                ->execute();

            $loan->order_id = $this->getData('order_id');
            $loan->save();

            return $loan->refresh();
        });
    }

    protected function validateData(): void
    {
        parent::validateData();

        $loan = resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('loan_id'))
            ->firstOrFail();

        if ($loan->order_id) {
            throw ValidationException::withMessages([
                'loan_id' => ['This loan already finances an order.'],
            ]);
        }

        $order = resolve_static(Order::class, 'query')
            ->with('orderType:id,order_type_enum')
            ->whereKey($this->getData('order_id'))
            ->firstOrFail();

        if ($order->tenant_id !== $loan->tenant_id) {
            throw ValidationException::withMessages([
                'order_id' => ['The order must belong to the loan tenant.'],
            ]);
        }

        if (! $order->orderType?->order_type_enum?->isPurchase()) {
            throw ValidationException::withMessages([
                'order_id' => ['Only purchase orders can be financed.'],
            ]);
        }

        if (bccomp(
            bcround($this->getData('amount'), 2),
            bcround(abs((float) $order->balance), 2),
            2
        ) === 1) {
            throw ValidationException::withMessages([
                'amount' => ['The amount must not exceed the open amount of the order.'],
            ]);
        }
    }
}
