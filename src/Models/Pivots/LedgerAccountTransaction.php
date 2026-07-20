<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Transaction;
use FluxErp\Traits\Model\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerAccountTransaction extends FluxPivot
{
    use HasPackageFactory;

    public $timestamps = true;

    protected $table = 'ledger_account_transaction';

    protected static function booted(): void
    {
        static::saved(function (LedgerAccountTransaction $ledgerAccountTransaction): void {
            if ($ledgerAccountTransaction->transaction_id && $ledgerAccountTransaction->is_accepted) {
                $ledgerAccountTransaction->transaction->is_ignored = false;
                $ledgerAccountTransaction->transaction->calculateBalance()->save();
            }
        });

        static::deleted(function (LedgerAccountTransaction $ledgerAccountTransaction): void {
            if ($ledgerAccountTransaction->is_accepted) {
                $ledgerAccountTransaction->transaction->calculateBalance()->save();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
        ];
    }

    // Relations
    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
