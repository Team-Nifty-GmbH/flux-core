<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum OrderTypeEnum: string
{
    use EnumTrait;

    public static function getIsPurchase(self $value): bool
    {
        return in_array($value, [self::Purchase, self::PurchaseRefund, self::PurchaseSubscription]);
    }

    public static function getIsSubscription(self $value): bool
    {
        return in_array($value, [self::Subscription, self::PurchaseSubscription]);
    }

    public static function getMultiplier(self $value): int
    {
        return match ($value) {
            self::Retoure, self::Purchase, self::Refund, self::PurchaseSubscription => -1,
            default => 1,
        };
    }

    public function isPurchase(): bool
    {
        return self::getIsPurchase($this);
    }

    public function isSubscription(): bool
    {
        return self::getIsSubscription($this);
    }

    public function multiplier(): string
    {
        return self::getMultiplier($this);
    }

    case Offer = 'offer';

    case Order = 'order';

    case Purchase = 'purchase';

    case PurchaseRefund = 'purchase-refund';

    case PurchaseSubscription = 'purchase-subscription';

    case Refund = 'refund';

    case Retoure = 'retoure';

    case SplitOrder = 'split-order';

    case Subscription = 'subscription';
}
