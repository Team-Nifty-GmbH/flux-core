<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;
use TeamNiftyGmbH\DataTable\Casts\Money;
use TeamNiftyGmbH\DataTable\Traits\HasFrontendAttributes;

class Transaction extends Model
{
    use HasFrontendAttributes, HasUserModification, HasUuid, Searchable;

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'uuid' => 'string',
        'amount' => Money::class,
        'created_at' => 'datetime',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    protected static function boot()
    {
        parent::boot();

        self::saving(function (Transaction $transaction) {
            $transaction->currency_id = $transaction->currency_id ?:
                (
                    Auth::user()->currency_id ?:
                    Currency::query()
                        ->orderBy('is_default', 'DESC')
                        ->first('id')
                        ?->id
                );
        });

        self::saved(function (Transaction $transaction) {
            $originalOrderId = $transaction->getRawOriginal('order_id');
            if ($originalOrderId) {
                Order::query()
                    ->whereKey($originalOrderId)
                    ->first()
                    ->calculatePaymentState()
                    ->save();
            }

            if ($transaction->order_id) {
                $transaction->order->calculatePaymentState()->save();
            }
        });

        self::deleted(function (Transaction $transaction) {
            if ($transaction->order_id) {
                $transaction->order->calculatePaymentState()->save();
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
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
}
