<?php

namespace FluxErp\Models;

use FluxErp\Traits\Commentable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class PurchaseInvoice extends Model implements HasMedia
{
    use Commentable, HasPackageFactory, HasUuid, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::creating(function (PurchaseInvoice $model) {
            $model->invoice_date = $model->invoice_date ?: now()->toDateString();
        });

        static::saving(function (PurchaseInvoice $model) {
            if ($model->isDirty('iban')) {
                $model->iban = str_replace(' ', '', strtoupper($model->iban));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'is_net' => 'boolean',
        ];
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
