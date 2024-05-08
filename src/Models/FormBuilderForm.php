<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormBuilderForm extends Model
{
    use Filterable, HasPackageFactory, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderForm $form) {
            $form->sections->each(function ($item) {
                $item->delete();
            });
            $form->responses->each(function ($item) {
                $item->delete();
            });
        });
    }

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];
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
