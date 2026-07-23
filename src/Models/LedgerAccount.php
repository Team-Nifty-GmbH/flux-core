<?php

namespace FluxErp\Models;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\Pivots\LedgerAccountTransaction;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends FluxModel
{
    use HasPackageFactory, HasTenantAssignment, HasUserModification, HasUuid, SoftDeletes;
    use Searchable {
        Searchable::scoutIndexSettings as baseScoutIndexSettings;
    }

    // Public static methods
    public static function scoutIndexSettings(): ?array
    {
        return static::baseScoutIndexSettings() ?? [
            'filterableAttributes' => [
                'ledger_account_type_enum',
                'is_automatic',
            ],
        ];
    }

    protected function casts(): array
    {
        return [
            'ledger_account_type_enum' => LedgerAccountTypeEnum::class,
            'is_automatic' => 'boolean',
        ];
    }

    // Public methods
    public function calculateBookingBalance(): string
    {
        // Raw movement only: debits minus credits. Sign interpretation per account
        // type is left to reporting, this returns the plain double-entry balance.
        return bcsub(
            (string) $this->debitBookings()->sum('amount'),
            (string) $this->creditBookings()->sum('amount'),
            2
        );
    }

    // Relations
    public function creditBookings(): HasMany
    {
        return $this->hasMany(LedgerBooking::class, 'credit_ledger_account_id');
    }

    public function debitBookings(): HasMany
    {
        return $this->hasMany(LedgerBooking::class, 'debit_ledger_account_id');
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class)
            ->using(LedgerAccountTransaction::class);
    }
}
