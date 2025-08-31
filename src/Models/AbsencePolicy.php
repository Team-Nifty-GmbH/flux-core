<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\AbsencePolicyAbsenceType;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AbsencePolicy extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'max_consecutive_days' => 'integer',
            'min_notice_days' => 'integer',
            'documentation_after_days' => 'integer',
            'can_select_substitute' => 'boolean',
            'is_active' => 'boolean',
            'requires_documentation' => 'boolean',
            'requires_proof' => 'boolean',
            'requires_reason' => 'boolean',
            'requires_substitute' => 'boolean',
        ];
    }

    public function absenceTypes(): BelongsToMany
    {
        return $this->belongsToMany(AbsenceType::class, 'absence_policy_absence_type')
            ->using(AbsencePolicyAbsenceType::class)
            ->withPivot('pivot_id');
    }

    public function validateRequest(AbsenceRequest $request): array
    {
        $errors = [];

        $daysRequested = $request->start_date->diffInDays($request->end_date) + 1;
        if (
            $this->max_consecutive_days
            && $daysRequested > $this->max_consecutive_days
        ) {
            $errors[] = __('Maximum consecutive days exceeded. Maximum: :days', [
                'days' => $this->max_consecutive_days,
            ]);
        }

        if ($this->min_notice_days > 0) {
            if (now()->diffInDays($request->start_date->copy()->addDay()) < $this->min_notice_days) {
                $errors[] = __('Minimum notice period not met. Required: :days days', [
                    'days' => $this->min_notice_days,
                ]);
            }
        }

        if ($this->requires_substitute && ! $request->substitute_employee_id) {
            $errors[] = __('A substitute is required for this absence type');
        }

        if ($this->requires_documentation
            && $this->documentation_after_days
            && $daysRequested >= $this->documentation_after_days
            && ! $request->hasMedia('documentation')
        ) {
            $errors[] = __('Documentation required for absences of :days days or more', [
                'days' => $this->documentation_after_days,
            ]);
        }

        return $errors;
    }
}
