<?php

namespace FluxErp\Models;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasEnums;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderType extends Model
{
    use CacheModelQueries, Filterable, HasClientAssignment, HasEnums, HasPackageFactory, HasTranslations,
        HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public $translatable = [
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'print_layouts' => 'array',
            'order_type_enum' => OrderTypeEnum::class,
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
