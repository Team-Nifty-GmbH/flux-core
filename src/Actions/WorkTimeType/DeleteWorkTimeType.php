<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rulesets\WorkTimeType\DeleteWorkTimeTypeRuleset;
use Illuminate\Validation\ValidationException;

class DeleteWorkTimeType extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteWorkTimeTypeRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(WorkTimeType::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(WorkTime::class, 'query')
            ->whereKey($this->data['id'])
            ->whereNotNull('order_position_id')
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'order_position' => [__('The given work time has an order position')],
            ])->errorBag('deleteWorkTime');
        }
    }
}
