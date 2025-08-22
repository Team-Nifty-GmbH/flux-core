<?php

namespace FluxErp\Models;

use Carbon\CarbonPeriod;
use FluxErp\Models\Pivots\TargetUser;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Target extends FluxModel
{
    use HasParentChildRelations, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected static function booted(): void
    {
        static::saved(function (Target $target): void {
            if (is_null($target->parent_id)) {
                static::destroy(
                    static::query()
                        ->where('parent_id', $target->getKey())
                        ->pluck($target->getKeyName())
                        ->toArray()
                );
                $period = CarbonPeriod::create($target->start_date, '1d', $target->end_date);

                if (count($period) > 1) {
                    foreach ($period as $date) {
                        $target->replicate(['parent_id'])
                            ->fill([
                                'parent_id' => $target->id,
                                'start_date' => $date,
                                'end_date' => $date,
                                'target_value' => bcdiv($target->target_value, count($period)),
                            ])
                            ->save();
                    }
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'constraints' => 'array',
        ];
    }

    public function calculateCurrentValue(User|int $user): string
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return morphed_model($this->model_type)::query()
            ->whereBetween($this->timeframe_column, [$this->start_date, $this->end_date])
            ->where(
                $this->owner_column,
                match ($this->owner_column) {
                    'created_by', 'updated_by' => morph_alias(User::class) . ':' . $userId,
                    default => $userId,
                }
            )
            ->{$this->aggregate_type}($this->aggregate_column);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'target_user')
            ->withPivot(['target_allocation'])
            ->using(TargetUser::class);
    }
}
