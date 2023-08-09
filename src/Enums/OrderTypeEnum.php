<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum OrderTypeEnum: string
{
    use EnumTrait;

    case Order = 'order';
    case SplitOrder = 'split-order';
    case Retoure = 'retoure';
    case Purchase = 'purchase';
    case PurchaseRefund = 'purchase-refund';
    case Subscription = 'subscription';

    public function multiplier(): string
    {
        return self::getMultiplier($this);
    }

    public static function getMultiplier(self $value): int
    {
        return match ($value) {
            self::Order, self::Subscription, self::PurchaseRefund, self::SplitOrder => 1,
            self::Retoure, self::Purchase => -1,
        };
    }

    public function isPurchase(): bool
    {
        return self::getIsPurchase($this);
    }

    public static function getIsPurchase(self $value): bool
    {
        return in_array($value, [self::Purchase, self::PurchaseRefund]);
    }
}
