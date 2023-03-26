<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalColumn extends Model
{
    use HasPackageFactory, Filterable;

    protected $casts = [
        'values' => 'array',
        'validations' => 'array',
        'is_translatable' => 'boolean',
        'is_customer_editable' => 'boolean',
        'is_frontend_visible' => 'boolean',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'pivot',
    ];

    public function modelValues(): Builder
    {
        return self::query()
            ->join('meta', 'additional_columns.id', '=', 'meta.additional_column_id')
            ->where('additional_columns.id', $this->id);
    }
}
