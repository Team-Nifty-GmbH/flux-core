<?php

namespace FluxErp\Models;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\Printable;
use FluxErp\Traits\SoftDeletes;
use FluxErp\View\Printing\PaymentReminder\PaymentReminderView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentReminder extends Model implements OffersPrinting
{
    use HasPackageFactory, HasUserModification, HasUuid, Printable, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (PaymentReminder $model) {
            if (! $model->reminder_level) {
                $model->reminder_level = $model->siblings()->max('reminder_level') + 1;
            }
        });
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(PaymentReminder::class, 'order_id', 'order_id')
            ->whereKeyNot($this->id);
    }

    public function getPrintViews(): array
    {
        return [
            'payment-reminder' => PaymentReminderView::class,
        ];
    }
}
