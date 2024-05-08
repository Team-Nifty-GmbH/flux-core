<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdditionalColumn extends Model
{
    use Filterable, HasPackageFactory;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'pivot',
    ];

    protected static function booted(): void
    {
        static::created(function (AdditionalColumn $additionalColumn) {
            Cache::store('array')->forget('meta_casts_' . $additionalColumn->model_type);
            Cache::store('array')->forget('meta_additional_columns_' . $additionalColumn->model_type);
        });
    }

    protected function casts(): array
    {
        return [
            'values' => 'array',
            'validations' => 'array',
            'is_translatable' => 'boolean',
            'is_customer_editable' => 'boolean',
            'is_frontend_visible' => 'boolean',
        ];
    }

    public function modelValues(): Builder
    {
        return self::query()
            ->join('meta', 'additional_columns.id', '=', 'meta.additional_column_id')
            ->where('additional_columns.id', $this->id);
    }
}
