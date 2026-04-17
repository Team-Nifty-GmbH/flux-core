<?php

namespace FluxErp\Models;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Pivots\OrderTypeTenant;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasTenants;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Model\SortableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;

class OrderType extends FluxModel implements Sortable
{
    use Filterable, HasAttributeTranslations, HasPackageFactory, HasTenantAssignment, HasTenants, HasUserModification,
        HasUuid, LogsActivity, SoftDeletes, SortableTrait;

    public array $sortable = [
        'sort_when_creating' => true,
    ];

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

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'order_type_tenant')->using(OrderTypeTenant::class);
    }

    protected function translatableAttributes(): array
    {
        return [
            'name',
            'document_header',
            'document_footer',
        ];
    }
}
