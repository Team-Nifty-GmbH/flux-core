<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormBuilderField extends Model
{
    use HasTranslations;
    use SoftDeletes;

    public array $translatable = ['name'];

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
    ];

    protected static function booted(): void
    {
//        static::deleting(function (Field $field) {
//            if ($field->isForceDeleting()) {
//                $field->fieldResponses()->withTrashed()->get()->each(function ($item) {
//                    $item->forceDelete();
//                });
//            } else {
//                $field->fieldResponses->each(function ($item) {
//                    $item->delete();
//                });
//            }
//        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilderForm::class);
    }

    public function section(): BelongsToMany
    {
        return $this->belongsToMany(FormBuilderSection::class);
    }

    public function fieldResponses(): HasMany
    {
        return $this->hasMany(FormBuilderFieldResponse::class);
    }
}
