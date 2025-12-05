<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Contracts\IsSubscribable;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Traits\Model\Categorizable;
use FluxErp\Traits\Model\Commentable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasTags;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\HasFrontendAttributes;

class Transaction extends FluxModel implements InteractsWithDataTables, IsSubscribable
{
    use Categorizable, Commentable, HasFrontendAttributes, HasPackageFactory, HasParentChildRelations, HasTags,
        HasUserModification, HasUuid, LogsActivity, Searchable, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Transaction $transaction): void {
            $transaction->currency_id = $transaction->currency_id
                ?? Auth::user()?->currency_id
                ?? resolve_static(Currency::class, 'default')?->getKey();
            $transaction->balance ??= $transaction->amount;
        });
    }

    protected function casts(): array
    {
        return [
            'value_date' => 'date:Y-m-d',
            'booking_date' => 'date:Y-m-d',
            'amount' => Money::class,
            'balance' => Money::class,
            'is_ignored' => 'boolean',
        ];
    }

    public function bankConnection(): BelongsTo
    {
        return $this->belongsTo(BankConnection::class);
    }

    public function calculateBalance(): static
    {
        if ($this->contact_bank_connection_id) {
            $this->balance = 0;
        } else {
            $this->balance = bcround(
                bcsub(
                    $this->amount,
                    $this->orders()->withPivot('amount')->sum('order_transaction.amount'),
                    9
                ),
                2
            );
        }

        return $this;
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->purpose;
    }

    public function getLabel(): ?string
    {
        return $this->counterpart_name;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)->using(OrderTransaction::class);
    }

    public function orderTransactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class);
    }
}
