<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class FormBuilderField extends Model implements Sortable
{
    use HasPackageFactory, HasUuid, SoftDeletes, SortableTrait;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'options' => 'array',
        'option_values' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderField $field) {
            $field->fieldResponses->each(function ($item) {
                $item->delete();
            });
        });
    }

    public function buildSortQuery(): Builder
    {
        return static::query()->where('section_id', $this->section_id);
    }

    public function fieldResponses(): HasMany
    {
        return $this->hasMany(FormBuilderFieldResponse::class, 'field_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilderForm::class, 'form_id');
    }

    public function getOptionValuesAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(FormBuilderSection::class, 'section_id');
    }
}
