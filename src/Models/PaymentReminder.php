<?php

namespace FluxErp\Models;

use FluxErp\Actions\Order\UpdateLockedOrder;
use FluxErp\Contracts\HasMediaForeignKey;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Printable;
use FluxErp\Traits\SoftDeletes;
use FluxErp\View\Printing\PaymentReminder\PaymentReminderView;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentReminder extends FluxModel implements HasMediaForeignKey, OffersPrinting
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity, Printable, SoftDeletes;

    public static function mediaReplaced(int|string|null $oldMediaId, int|string|null $newMediaId): void
    {
        static::query()
            ->where('media_id', $oldMediaId)
            ->update(['media_id' => $newMediaId]);
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentReminder $model): void {
            if (! $model->reminder_level) {
                $model->reminder_level = $model->siblings()->max('reminder_level') + 1;
            }
        });

        static::created(function (PaymentReminder $model): void {
            UpdateLockedOrder::make([
                'id' => $model->order_id,
                'payment_reminder_current_level' => $model->reminder_level,
                'payment_reminder_next_date' => $model->created_at
                    ->addDays(
                        $model->order->{'payment_reminder_days_' . $model->reminder_level + 1}
                            ?? $model->order->payment_reminder_days_3
                    )
                    ->toDateString(),
            ])
                ->validate()
                ->execute();
        });
    }

    public function getPaymentReminderText(): ?PaymentReminderText
    {
        return app(PaymentReminderText::class)
            ->where('reminder_level', '<=', $this->reminder_level)
            ->orderBy('reminder_level', 'desc')
            ->first();
    }

    public function getPrintViews(): array
    {
        return [
            'payment-reminder' => PaymentReminderView::class,
        ];
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
}
