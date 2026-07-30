<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class LedgerBooking extends FluxModel
{
    use Filterable, HasPackageFactory, HasTenantAssignment, HasUserModification, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        $recalculateSettledOrders = function (LedgerBooking $ledgerBooking): void {
            $ledgerBooking->settledOrders()->each(
                fn (Order $order) => $order->calculatePaymentState()->save()
            );
        };

        static::saved($recalculateSettledOrders);
        static::deleted($recalculateSettledOrders);
    }

    protected function casts(): array
    {
        return [
            'amount' => Money::class,
            'booking_date' => 'date',
        ];
    }

    // Relations
    public function creditLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'credit_ledger_account_id');
    }

    public function debitLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'debit_ledger_account_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The orders whose payment state depends on this booking: the one it points at
     * now and, when the source was moved, the one it pointed at before.
     */
    public function settledOrders(): Collection
    {
        return collect([
            [$this->getOriginal('source_type'), $this->getOriginal('source_id')],
            [$this->source_type, $this->source_id],
        ])
            ->filter(fn (array $source) => $source[1] && is_a(morphed_model($source[0]) ?? '', Order::class, true))
            ->unique(fn (array $source) => $source[1])
            ->map(fn (array $source) => resolve_static(Order::class, 'query')->whereKey($source[1])->first())
            ->filter();
    }
}
