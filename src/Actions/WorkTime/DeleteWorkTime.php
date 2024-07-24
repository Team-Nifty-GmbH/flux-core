<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\DeleteWorkTimeRuleset;
use Illuminate\Validation\ValidationException;

class DeleteWorkTime extends FluxAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = resolve_static(DeleteWorkTimeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(WorkTime::class, 'query')
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
