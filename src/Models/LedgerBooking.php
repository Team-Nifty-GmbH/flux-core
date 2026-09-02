<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerBooking extends FluxModel
{
    use Filterable, HasPackageFactory, HasTenantAssignment, HasUserModification, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::saved(function (LedgerBooking $ledgerBooking): void {
            if ($ledgerBooking->wasChanged(['source_type', 'source_id'])) {
                static::recalculateSettledOrder(
                    $ledgerBooking->getOriginal('source_type'),
                    $ledgerBooking->getOriginal('source_id')
                );
            }

            static::recalculateSettledOrder($ledgerBooking->source_type, $ledgerBooking->source_id);
        });

        static::deleted(function (LedgerBooking $ledgerBooking): void {
            static::recalculateSettledOrder($ledgerBooking->source_type, $ledgerBooking->source_id);
        });
    }

    protected static function recalculateSettledOrder(?string $sourceType, int|string|null $sourceId): void
    {
        if (! $sourceId || $sourceType !== morph_alias(Order::class)) {
            return;
        }

        resolve_static(Order::class, 'query')
            ->whereKey($sourceId)
            ->first()
            ?->calculatePaymentState()
            ->save();
    }

    protected function casts(): array
    {
        return [
            'amount' => Money::class,
            'booking_date' => 'date',
        ];
    }

    // Relations
    /**
     * @return BelongsTo<LedgerAccount, $this>
     */
    public function creditLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'credit_ledger_account_id');
    }

    /**
     * @return BelongsTo<LedgerAccount, $this>
     */
    public function debitLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'debit_ledger_account_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
