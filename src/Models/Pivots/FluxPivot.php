<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\Pivot;
use ReflectionClass;

abstract class FluxPivot extends Pivot
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;

    /**
     * Columns a relation using this pivot should carry, for withPivot().
     *
     * @var list<string>
     */
    protected static array $pivotColumns = [];

    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $guarded = ['pivot_id'];

    public static function pivotColumns(): array
    {
        return static::$pivotColumns;
    }

    public function resolveCollectionFromAttribute(): ?string
    {
        $parent = get_parent_class(static::class);

        if ($parent && (new ReflectionClass($parent))->isAbstract()) {
            return null;
        }

        return parent::resolveCollectionFromAttribute();
    }
}
