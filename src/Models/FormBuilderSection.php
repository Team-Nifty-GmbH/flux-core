<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormBuilderSection extends FluxModel
{
    use HasPackageFactory, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderSection $section): void {
            $section->fields->each(function ($item): void {
                $item->delete();
            });
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
