<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Traits\HasNotificationSubscriptions;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\HasFrontendAttributes;

class Transaction extends FluxModel implements InteractsWithDataTables
{
    use HasFrontendAttributes, HasNotificationSubscriptions, HasPackageFactory, HasUserModification, HasUuid,
        LogsActivity, Searchable, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::saving(function (Transaction $transaction) {
            $transaction->currency_id = $transaction->currency_id
                ?? Auth::user()?->currency_id
                ?? Currency::default()?->id;
        });

        static::saved(function (Transaction $transaction) {
            $originalOrderId = $transaction->getRawOriginal('order_id');
            if ($originalOrderId) {
                resolve_static(Order::class, 'query')
                    ->whereKey($originalOrderId)
                    ->first()
                    ->calculatePaymentState()
                    ->save();
            }

            if ($transaction->order_id) {
                $transaction->order->calculatePaymentState()->save();
            }
        });

        static::deleted(function (Transaction $transaction) {
            if ($transaction->order_id) {
                $transaction->order->calculatePaymentState()->save();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'value_date' => 'date:Y-md',
            'booking_date' => 'date:Y-md',
            'amount' => Money::class,
            'created_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    public function bankConnection(): BelongsTo
    {
        return $this->belongsTo(BankConnection::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_id');
    }

    public function getLabel(): ?string
    {
        return $this->counterpart_name;
    }

    public function getDescription(): ?string
    {
        return $this->purpose;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }
}
