<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormBuilderForm extends Model
{
    use HasPackageFactory;
    use HasUuid;
    use SoftDeletes;

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'options' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderForm $form) {
            if ($form->isForceDeleting()) {
                $form->fieldResponses()->withTrashed()->get()->each(function ($item) {
                    $item->forceDelete();
                });
                $form->responses()->withTrashed()->get()->each(function ($item) {
                    $item->forceDelete();
                });
                $form->sections()->withTrashed()->get()->each(function ($item) {
                    $item->fields()->withTrashed()->get()->each(function ($item) {
                        $item->forceDelete();
                    });
                    $item->forceDelete();
                });
            } else {
                $form->fieldsResponses->each(function ($item) {
                    $item->delete();
                });
                $form->responses->each(function ($item) {
                    $item->delete();
                });
                $form->sections->each(function ($item) {
                    $item->fields->each(function ($item) {
                        $item->delete();
                    });
                    $item->delete();
                });
            }
        });
    }

    public function responses(): hasMany
    {
        return $this->hasMany(FormBuilderResponse::class, 'form_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(FormBuilderSection::class, 'form_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
