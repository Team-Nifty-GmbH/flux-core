<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class Widget extends Model
{
    use HasPackageFactory, HasUuid;

    protected $guarded = [
        'id',
    ];

//    protected static function booted(): void
//    {
//        static::addGlobalScope('ordered', function (Builder $builder) {
//            $builder->ordered();
//        });
//    }

    public function buildSortQuery(): Builder
    {
        return static::query()
            ->where('widgetable_type', $this->widgetable_type)
            ->where('widgetable_id', $this->widgetable_id);
    }
}
