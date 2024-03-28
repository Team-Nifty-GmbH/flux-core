<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class WorkTimeType extends Model
{
    use HasPackageFactory, HasTranslations, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public $translatable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'is_billable' => 'boolean',
        ];
    }
}
