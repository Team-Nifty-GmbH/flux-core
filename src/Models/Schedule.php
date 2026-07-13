<?php

namespace FluxErp\Models;

use Carbon\CarbonInterface;
use Cron\CronExpression;
use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Models\Pivots\OrderSchedule;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Schedule extends FluxModel
{
    use HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    /**
     * The end of the period that starts on $start, based on the schedule frequency.
     *
     * Single source of truth for consumers that derive a period from a schedule
     * (e.g. the subscription performance period and the schedule preview), so all
     * always agree. lastDayOfMonth is handled explicitly because Laravel bakes a
     * fixed day-of-month into the cron expression, which is unreliable in short months.
     */
    public static function performancePeriodEnd(CarbonInterface $start, ?array $cron, ?string $cronExpression): Carbon
    {
        $start = Carbon::instance($start);

        if (data_get($cron, 'methods.basic') === FrequenciesEnum::LastDayOfMonth->value) {
            return $start->endOfMonth();
        }

        if ($cronExpression) {
            return Carbon::instance(
                (new CronExpression($cronExpression))
                    ->getNextRunDate($start->endOfDay()->toDateTime())
            )
                ->subDay();
        }

        return $start;
    }

    protected function casts(): array
    {
        return [
            'type' => RepeatableTypeEnum::class,
            'cron' => 'array',
            'parameters' => 'array',
            'due_at' => 'datetime',
            'ends_at' => 'datetime',
            'last_success' => 'datetime',
            'last_run' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_schedule')
            ->using(OrderSchedule::class);
    }
}
