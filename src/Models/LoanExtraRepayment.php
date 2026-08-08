<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Enums\ScheduleAdjustmentTypeEnum;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class LoanExtraRepayment extends FluxModel implements InteractsWithDataTables
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'executed_at' => 'date',
            'amount' => Money::class,
            'interest_saved' => Money::class,
            'schedule_adjustment_type_enum' => ScheduleAdjustmentTypeEnum::class,
        ];
    }

    // Relations
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    // Public methods
    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->note;
    }

    public function getLabel(): ?string
    {
        return trim(($this->loan?->name ?? '') . ' ' . __('Extra Repayment'));
    }

    public function getUrl(): ?string
    {
        return $this->loan?->getUrl();
    }
}
