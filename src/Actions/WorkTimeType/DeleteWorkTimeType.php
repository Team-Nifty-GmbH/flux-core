<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use Illuminate\Validation\ValidationException;

class DeleteWorkTimeType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:work_time_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    public function execute(): bool|null
    {
        return WorkTime::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

        if (WorkTime::query()
            ->whereKey($this->data['id'])
            ->whereNotNull('order_position_id')
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'order_position' => [__('The given work time has an order position')],
            ])->errorBag('deleteWorkTime');
        }

        return $this;
    }
}
