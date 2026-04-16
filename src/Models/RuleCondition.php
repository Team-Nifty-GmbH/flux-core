<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuleCondition extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid;

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'position' => 'integer',
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(RuleCondition::class, 'parent_id')->orderBy('position');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RuleCondition::class, 'parent_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }
}
