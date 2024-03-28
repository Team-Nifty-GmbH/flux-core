<?php

namespace FluxErp\Models;

use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    use Commentable, Filterable, HasPackageFactory, HasTranslations, HasUserModification, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public $translatable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function default(): ?static
    {
        return static::query()->where('is_default', true)->first();
    }
}
