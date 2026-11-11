<?php

namespace FluxErp\Models;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\Printable;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\View\Printing\PaymentReminder\PaymentReminderView;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentReminder extends FluxModel implements OffersPrinting
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity, Printable, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (PaymentReminder $model): void {
            if (! $model->reminder_level) {
                $model->reminder_level = $model->siblings()->max('reminder_level') + 1;
            }
        });
    }

    // Relations
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(PaymentReminder::class, 'order_id', 'order_id')
            ->whereKeyNot($this->id);
    }

    // Public methods
    public function getEmailTemplateModelType(): ?string
    {
        return morph_alias(static::class);
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
}
