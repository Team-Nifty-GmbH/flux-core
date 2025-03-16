<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressAddressTypeOrder extends FluxPivot
{
    protected $table = 'address_address_type_order';

    protected static function booted(): void
    {
        static::saving(function (AddressAddressTypeOrder $model): void {
            if ($model->isDirty('address_id')) {
                $model->address = $model->address()->first();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'address' => 'array',
        ];
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function addressType(): BelongsTo
    {
        return $this->belongsTo(AddressType::class);
    }

    public function getAddressAttribute(): ?Model
    {
        $address = $this->fromJson($this->attributes['address'] ?? null);

        return $address
            ? resolve_static(Address::class, 'query')
                ->whereKey($address['id'])
                ->firstOrNew()
                ->fill($address)
            : $this->address()->first();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
