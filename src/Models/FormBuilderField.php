<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormBuilderField extends Model
{
    use HasPackageFactory, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderField $field) {
            $field->fieldResponses->each(function ($item) {
                $item->delete();
            });
        });
    }

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function fieldResponses(): HasMany
    {
        return $this->hasMany(FormBuilderFieldResponse::class, 'field_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilderForm::class, 'form_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(FormBuilderSection::class, 'section_id');
    }
}
