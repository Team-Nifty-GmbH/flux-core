<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormBuilderSection extends Model
{
    use HasPackageFactory;
    use HasTranslations;
    use HasUuid;
    use SoftDeletes;

    public array $translatable = ['name'];

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderSection $section) {
            if ($section->isForceDeleting()) {
                $section->fields()->withTrashed()->get()->each(function ($item) {
                    $item->fieldResponses()->withTrashed()->get()->each(function ($item) {
                        $item->forceDelete();
                    });
                    $item->forceDelete();
                });
            } else {
                $section->fields->each(function ($item) {
                    $item->fieldResponses->each(function ($item) {
                        $item->delete();
                    });
                    $item->delete();
                });
            }
        });
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormBuilderField::class, 'section_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilderForm::class, 'form_id');
    }
}
