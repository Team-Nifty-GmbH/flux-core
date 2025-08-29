<?php

namespace FluxErp\Models;

use FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBalanceAdjustment extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => EmployeeBalanceAdjustmentTypeEnum::class,
            'reason' => EmployeeBalanceAdjustmentReasonEnum::class,
            'amount' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getDescription(): ?string
    {
        $sign = bccomp($this->amount, 0, 2) >= 0 ? '+' : '';

        return $sign . $this->amount . ' ' . $this->type->label() . ' - ' . $this->reason->label();
    }

    public function getLabel(): ?string
    {
        return $this->employee->name . ' - ' . $this->type->label() . ' - ' . $this->effective_date->format('Y-m-d');
    }
}
