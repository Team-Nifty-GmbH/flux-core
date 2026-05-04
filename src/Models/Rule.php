<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rule extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(RuleCondition::class)->orderBy('position');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function rootConditions(): HasMany
    {
        return $this->hasMany(RuleCondition::class)->whereNull('parent_id')->orderBy('position');
    }
}
