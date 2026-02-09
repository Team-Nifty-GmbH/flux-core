<?php

namespace FluxErp\Actions\WorkTimeModel;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rulesets\WorkTimeModel\DeleteWorkTimeModelRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class DeleteWorkTimeModel extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeModel::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteWorkTimeModelRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(WorkTimeModel::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $now = now();

        $hasActiveAssignments = resolve_static(EmployeeWorkTimeModel::class, 'query')
            ->where('work_time_model_id', $this->getData('id'))
            ->where('valid_from', '<=', $now)
            ->where(fn (Builder $query): Builder => $query->whereNull('valid_until')
                ->orWhere('valid_until', '>=', $now)
            )
            ->exists();

        if ($hasActiveAssignments) {
            throw ValidationException::withMessages([
                'employees' => [__('This work time model is still assigned to employees.')],
            ])
                ->errorBag('deleteWorkTimeModel');
        }
    }
}
