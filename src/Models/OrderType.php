<?php

namespace FluxErp\Models;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderType extends FluxModel
{
    use Filterable, HasPackageFactory, HasTenantAssignment, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

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

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
