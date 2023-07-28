<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class WorkTimeType extends Model
{
    use HasPackageFactory, HasTranslations, HasUuid;

    protected $casts = [
        'is_billable' => 'boolean',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public $translatable = [
        'name',
    ];
}
