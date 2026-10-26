<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Contracts\SupportsBulkExecution;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use FluxErp\Rulesets\Transaction\DeleteTransactionRuleset;

class DeleteTransaction extends DispatchableFluxAction implements SupportsBulkExecution
{
    public static function models(): array
    {
        return [Transaction::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteTransactionRuleset::class;
    }

    public function performAction(): ?bool
    {
        $transaction = resolve_static(Transaction::class, 'query')
            ->whereKey($this->getData('id'))
            ->lockForUpdate()
            ->first();

        $orderIds = $transaction->orderTransactions()
            ->distinct()
            ->pluck('order_id');

        $transaction->orderTransactions()->delete();
        $deleted = $transaction->delete();

        resolve_static(Order::class, 'query')
            ->withTrashed()
            ->whereKey($orderIds)
            ->lockForUpdate()
            ->get()
            ->each(function (Order $order): void {
                $order->calculatePaymentState()->save();
            });

        return $deleted;
    }
}
