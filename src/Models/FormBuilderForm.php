<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class FormBuilderForm extends Model
{
    // use HasActive;
    use HasTranslations;

    // use HasUpdates;
    use SoftDeletes;

    public array $translatable = ['name', 'description', 'details'];

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'options' => 'array',
        'user_id' => 'integer',
    ];

    protected $appends = ['sections'];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderForm $form) {
            if ($form->isForceDeleting()) {
                $form->fieldsResponses()->withTrashed()->get()->each(function ($item) {
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function sections(): HasMany
    {
        return $this->hasMany(FormBuilderSection::class, 'form_id', 'id');
    }

    public function fields(): HasManyThrough
    {
        return $this->hasManyThrough(FormBuilderField::class, FormBuilderSection::class);
    }

    public function responses(): hasMany
    {
        return $this->hasMany(FormBuilderResponse::class, 'form_id', 'id');
    }

    public function fieldsResponses(): HasMany
    {
        return $this->hasMany(FormBuilderFieldResponse::class, 'form_id', 'id');
    }

    public function getSectionsAttribute()
    {
        return $this->sections()->with('fields')->get();
    }
}
