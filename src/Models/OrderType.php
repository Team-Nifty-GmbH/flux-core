<?php

namespace FluxErp\Models;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasEnums;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderType extends Model
{
    use Filterable, HasEnums, HasPackageFactory, HasTranslations, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'uuid' => 'string',
        'print_layouts' => 'array',
        'order_type_enum' => OrderTypeEnum::class,
        'is_active' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public $translatable = [
        'name',
        'description',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function documentGenerationSettings(): HasMany
    {
        return $this->hasMany(DocumentGenerationSetting::class, 'order_type_id');
    }
}
