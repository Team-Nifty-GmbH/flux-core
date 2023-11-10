<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormBuilderResponse extends Model
{
    use HasPackageFactory;
    use HasUuid;
    use SoftDeletes;

    protected $with = [
        'form',
        'user'
    ];

    protected $guarded = [
        'id'
    ];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderResponse $response) {
            if ($response->isForceDeleting()) {
                $response->fieldResponses()->withTrashed()->get()->each(function ($item) {
                    $item->forceDelete();
                });
            } else {
                $response->fieldsResponses->each(function ($item) {
                    $item->delete();
                });
            }
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
