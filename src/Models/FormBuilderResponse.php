<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormBuilderResponse extends FluxModel
{
    use HasPackageFactory, HasUuid, SoftDeletes;

    protected $with = [
        'form',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderResponse $response): void {
            $response->fieldsResponses->each(function ($item): void {
                $item->delete();
            });
        });
    }

    public function fieldResponses(): HasMany
    {
        return $this->hasMany(FormBuilderFieldResponse::class, 'form_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilderForm::class, 'form_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
