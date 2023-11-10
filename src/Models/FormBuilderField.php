<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormBuilderField extends Model
{
    use HasPackageFactory;
    use HasTranslations;
    use HasUuid;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderField $field) {
            if ($field->isForceDeleting()) {
                $field->fieldResponses()->withTrashed()->get()->each(function ($item) {
                    $item->forceDelete();
                });
            } else {
                $field->fieldResponses->each(function ($item) {
                    $item->delete();
                });
            }
        });
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
