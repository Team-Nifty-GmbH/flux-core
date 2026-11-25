<?php

namespace FluxErp\Invokable;

use Cron\CronExpression;
use FluxErp\Actions\RebateAgreement\SettleRebateAgreement;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Models\RebateAgreement;
use Throwable;

class ProcessRebateAgreements implements Repeatable
{
    public function __invoke(): bool
    {
        $rebateAgreements = resolve_static(RebateAgreement::class, 'query')
            ->whereDate('period_end', '<', now())
            ->whereNull('settled_at')
            ->where('is_active', true)
            ->get(['id']);

        foreach ($rebateAgreements as $rebateAgreement) {
            try {
                SettleRebateAgreement::make(['id' => $rebateAgreement->getKey()])
                    ->validate()
                    ->execute();
            } catch (Throwable $e) {
                activity()
                    ->event(static::class)
                    ->byAnonymous()
                    ->performedOn($rebateAgreement)
                    ->withProperties(['message' => $e->getMessage()])
                    ->log(class_basename($e));
            }
        }

        return true;
    }

    public static function defaultCron(): ?CronExpression
    {
        return null;
    }

    public static function description(): ?string
    {
        return 'Settle all due rebate agreements.';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return class_basename(static::class);
    }

    public static function parameters(): array
    {
        return [];
    }

    public static function withoutOverlapping(): bool
    {
        return true;
    }
}
