<?php

namespace FluxErp\Invokable;

use Cron\CronExpression;
use FluxErp\Actions\RebateAgreement\SettleRebateAgreement;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\OrderType;
use FluxErp\Models\RebateAgreement;
use Throwable;

class ProcessRebateAgreements implements Repeatable
{
    public function __invoke(int|string|null $orderTypeId = null): bool
    {
        $isRefundOrderType = resolve_static(OrderType::class, 'query')
            ->whereKey($orderTypeId)
            ->where('is_active', true)
            ->where('order_type_enum', OrderTypeEnum::Refund)
            ->exists();

        if (! $isRefundOrderType) {
            activity()
                ->event(static::class)
                ->byAnonymous()
                ->withProperties(['order_type_id' => $orderTypeId])
                ->log('Invalid order type');

            return false;
        }

        $rebateAgreements = resolve_static(RebateAgreement::class, 'query')
            ->where('is_active', true)
            ->whereNull('settled_at')
            ->whereDate('period_end', '<', now())
            ->get();

        foreach ($rebateAgreements as $rebateAgreement) {
            try {
                SettleRebateAgreement::make([
                    'id' => $rebateAgreement->getKey(),
                    'order_type_id' => $orderTypeId,
                ])
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
        return 'Settle all due volume rebate agreements.';
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
        return [
            'orderTypeId' => null,
        ];
    }

    public static function withoutOverlapping(): bool
    {
        return true;
    }
}
