<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormBuilderForm extends Model
{
    use HasPackageFactory, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
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
            $form->fieldsResponses->each(function ($item) {
                $item->delete();
            });
            $form->responses->each(function ($item) {
                $item->delete();
            });
            $form->sections->each(function ($item) {
                $item->delete();
            });
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
