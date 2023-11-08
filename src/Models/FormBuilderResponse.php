<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormBuilderResponse extends Model
{
    use SoftDeletes;
    use HasPackageFactory;

    protected $with = ['form', 'user'];

    protected $guarded = [];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderResponse $response) {
            if ($response->isForceDeleting()) {
                $response->fieldsResponses()->withTrashed()->get()->each(function ($item) {
                    $item->forceDelete();
                });
            } else {
                $response->fieldsResponses->each(function ($item) {
                    $item->delete();
                });
            }
        });
    }

    public function fieldsResponses(): HasMany
    {
        return $this->hasMany(FormBuilderFieldResponse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilderForm::class);
    }
}
