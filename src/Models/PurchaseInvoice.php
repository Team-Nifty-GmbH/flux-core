<?php

namespace FluxErp\Models;

use FluxErp\Contracts\HasMediaForeignKey;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class PurchaseInvoice extends FluxModel implements HasMedia, HasMediaForeignKey
{
    use Commentable, Communicatable, HasPackageFactory, HasTags, HasUserModification, HasUuid, InteractsWithMedia,
        LogsActivity, SoftDeletes;

    public static function mediaReplaced(int|string|null $oldMediaId, int|string|null $newMediaId): void
    {
        static::query()
            ->where('media_id', $oldMediaId)
            ->update(['media_id' => $newMediaId]);
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseInvoice $model): void {
            $model->invoice_date = $model->invoice_date ?: now()->toDateString();
        });

        static::saving(function (PurchaseInvoice $model): void {
            if ($model->isDirty('iban') && is_string($model->iban)) {
                $model->iban = str_replace(' ', '', strtoupper($model->iban));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'payment_target_date' => 'date',
            'payment_discount_target_date' => 'date',
            'total_gross_price' => 'decimal:2',
            'is_net' => 'boolean',
        ];
    }

    public function calculateTotalGrossPrice(): ?string
    {
        $vatRates = resolve_static(VatRate::class, 'query')
            ->pluck('rate_percentage', 'id')
            ->toArray();

        return $this->purchaseInvoicePositions?->reduce(
            function (string $carry, PurchaseInvoicePosition $position) use ($vatRates) {
                if ($this->is_net) {
                    $positionGross = net_to_gross(
                        $position->total_price,
                        data_get($vatRates, $position->vat_rate_id) ?? 0
                    );
                } else {
                    $positionGross = $position->total_price;
                }

                return bcadd($carry, $positionGross);
            },
            '0'
        );
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function layOutUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lay_out_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderType(): BelongsTo
    {
        return $this->belongsTo(OrderType::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function purchaseInvoicePositions(): HasMany
    {
        return $this->hasMany(PurchaseInvoicePosition::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('purchase_invoice')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/xml', 'text/xml'])
            ->singleFile();
    }
}
