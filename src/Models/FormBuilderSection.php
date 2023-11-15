<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class FormBuilderSection extends Model implements Sortable
{
    use HasPackageFactory, HasUuid, SoftDeletes, SortableTrait;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderSection $section) {
            $section->fields->each(function ($item) {
                $item->delete();
            });
        });
    }

    public function buildSortQuery()
    {
        return static::query()->where('form_id', $this->form_id);
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
