<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTransaction extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = true;

    protected $guarded = [
        'pivot_id',
    ];

    protected $primaryKey = 'pivot_id';

    protected static function booted(): void
    {
        static::saved(function (OrderTransaction $orderTransaction): void {
            $originalOrderId = $orderTransaction->getRawOriginal('order_id');
            if ($originalOrderId && $orderTransaction->is_accepted) {
                resolve_static(Order::class, 'query')
                    ->whereKey($originalOrderId)
                    ->first()
                    ->calculatePaymentState()
                    ->save();
            }

            if ($orderTransaction->order_id && $orderTransaction->is_accepted) {
                $orderTransaction->order->calculatePaymentState()->save();
            }

            if ($orderTransaction->transaction_id && $orderTransaction->is_accepted) {
                $orderTransaction->transaction->is_ignored = false;
                $orderTransaction->transaction->calculateBalance()->save();
            }
        });

        static::deleted(function (OrderTransaction $orderTransaction): void {
            if ($orderTransaction->order_id && $orderTransaction->is_accepted) {
                $orderTransaction->order->calculatePaymentState()->save();
            }

            if ($orderTransaction->transaction_id && $orderTransaction->is_accepted) {
                $orderTransaction->transaction->calculateBalance()->save();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
