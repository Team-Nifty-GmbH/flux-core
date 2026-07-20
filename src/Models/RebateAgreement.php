<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RebateAgreement extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected static function normalizeTierValue(mixed $value): string
    {
        $normalized = rtrim(rtrim(sprintf('%.10F', (float) $value), '0'), '.');

        return $normalized === '' || $normalized === '-' ? '0' : $normalized;
    }

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'tiers' => 'array',
            'settled_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function rebateOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'rebate_order_id');
    }

    public function resolvePercentage(string $volume): ?string
    {
        $percentage = null;
        $highest = null;

        foreach ($this->tiers ?? [] as $tier) {
            $fromVolume = static::normalizeTierValue(data_get($tier, 'from_volume'));

            if (bccomp($volume, $fromVolume, 9) === -1) {
                continue;
            }

            if (is_null($highest) || bccomp($fromVolume, $highest, 9) === 1) {
                $highest = $fromVolume;
                $percentage = static::normalizeTierValue(data_get($tier, 'percentage'));
            }
        }

        return $percentage;
    }
}
