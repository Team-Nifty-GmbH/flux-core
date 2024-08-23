<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormBuilderResponse extends Model
{
    use HasPackageFactory, HasUuid, SoftDeletes;

    protected $with = [
        'form',
    ];

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderResponse $response) {
            $response->fieldsResponses->each(function ($item) {
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
