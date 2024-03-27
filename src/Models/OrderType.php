<?php

namespace FluxErp\Models;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasEnums;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderType extends Model
{
    use Filterable, HasClientAssignment, HasEnums, HasPackageFactory, HasTranslations, HasUserModification, HasUuid,
        SoftDeletes;

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
