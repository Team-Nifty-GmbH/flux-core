<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\Pivot;
use ReflectionClass;

abstract class FluxPivot extends Pivot
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;

    public function resolveCollectionFromAttribute()
    {
        $parent = get_parent_class(static::class);

        if ($parent && (new ReflectionClass($parent))->isAbstract()) {
            return null;
        }

        return parent::resolveCollectionFromAttribute();
    }

    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $guarded = ['pivot_id'];
}
