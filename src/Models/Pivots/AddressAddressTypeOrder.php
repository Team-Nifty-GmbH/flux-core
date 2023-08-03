<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AddressAddressTypeOrder extends Pivot
{
    protected $table = 'address_address_type_order';

    protected $casts = [
        'address' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (AddressAddressTypeOrder $model) {
            if ($model->isDirty('address_id')) {
                $model->address = $model->address()->first();
            }
        });
    }

    public function getAddressAttribute(): ?Model
    {
        $address = $this->fromJson($this->attributes['address'] ?? null);

        return $address
            ? (Address::query()->whereKey($address['id'])->firstOrNew())->fill($address)
            : $this->address()->first();
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
