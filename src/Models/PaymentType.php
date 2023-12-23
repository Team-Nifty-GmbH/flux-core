<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentType extends Model
{
    use Filterable, HasPackageFactory, HasTranslations, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'uuid' => 'string',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public $translatable = [
        'name',
        'description',
    ];

    public function paymentNotices(): HasMany
    {
        return $this->hasMany(PaymentNotice::class, 'payment_type_id');
    }

    public static function default(): ?static
    {
        return static::query()->where('is_default', true)->first();
    }
}
