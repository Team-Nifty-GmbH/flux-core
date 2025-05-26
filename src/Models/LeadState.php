<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LeadState extends FluxModel
{
    use HasDefault, HasUserModification, HasUuid, SoftDeletes;

    protected $appends = [
        'image',
    ];

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ];
    }

    public function getImageAttribute(): string
    {
        return route('avatar', [
            'text' => $this->name,
            'color' => Str::after($this->color, '#'),
        ]);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
