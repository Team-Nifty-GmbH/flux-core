<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormBuilderForm extends FluxModel
{
    use Filterable, HasPackageFactory, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderForm $form): void {
            $form->sections->each(function ($item): void {
                $item->delete();
            });
            $form->responses->each(function ($item): void {
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

    public function responses(): HasMany
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
