<?php

namespace FluxErp\Models;

use FluxErp\Enums\AbsenceRequestCreationTypeEnum;
use FluxErp\Models\Pivots\AbsencePolicyAbsenceType;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class AbsenceType extends FluxModel implements InteractsWithDataTables
{
    use HasUserModification, HasUuid, Searchable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'employee_can_create' => AbsenceRequestCreationTypeEnum::class,
            'affects_overtime' => 'boolean',
            'affects_vacation' => 'boolean',
            'affects_sick' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function absencePolicies(): BelongsToMany
    {
        return $this->belongsToMany(AbsencePolicy::class, 'absence_policy_absence_type')
            ->using(AbsencePolicyAbsenceType::class);
    }

    public function absenceRequests(): HasMany
    {
        return $this->hasMany(AbsenceRequest::class);
    }

    public function getAbbreviation(): string
    {
        return Str::of($this->name)
            ->replaceMatches('/[^A-Z]/', '')
            ->trim()
            ->limit(2, '')
            ->toString();
    }

    public function getAvatarUrl(): ?string
    {
        return route('avatar', [
            'text' => $this->getAbbreviation(),
            'color' => Str::after($this->color, '#'),
        ]);
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return null;
    }
}
