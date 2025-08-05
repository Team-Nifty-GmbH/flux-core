<?php

namespace FluxErp\Models;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasEnums;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderType extends FluxModel
{
    use CacheModelQueries, Filterable, HasClientAssignment, HasEnums, HasPackageFactory, HasUserModification, HasUuid,
        LogsActivity, SoftDeletes;

    public static function hasPermission(): bool
    {
        return false;
    }

    protected function casts(): array
    {
        return [
            'print_layouts' => 'array',
            'post_stock_print_layouts' => 'array',
            'reserve_stock_print_layouts' => 'array',
            'order_type_enum' => OrderTypeEnum::class,
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
            'is_visible_in_sidebar' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
