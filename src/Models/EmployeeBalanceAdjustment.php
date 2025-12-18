<?php

namespace FluxErp\Models;

use FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBalanceAdjustment extends FluxModel
{
    use HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => EmployeeBalanceAdjustmentTypeEnum::class,
            'reason' => EmployeeBalanceAdjustmentReasonEnum::class,
            'effective_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
