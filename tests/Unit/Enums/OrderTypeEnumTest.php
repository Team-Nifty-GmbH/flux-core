<?php

use FluxErp\Enums\OrderTypeEnum;

test('order type has correct values', function (): void {
    expect(OrderTypeEnum::Order->value)->toBe('order');
});

test('multiplier returns positive for order', function (): void {
    expect(bccomp(OrderTypeEnum::Order->multiplier(), '0'))->toBe(1);
});

test('multiplier returns negative for retoure', function (): void {
    expect(bccomp(OrderTypeEnum::Retoure->multiplier(), '0'))->toBe(-1);
});

test('isPurchase returns true for purchase types', function (): void {
    expect(OrderTypeEnum::Purchase->isPurchase())->toBeTrue();
    expect(OrderTypeEnum::PurchaseRefund->isPurchase())->toBeTrue();
});

test('isPurchase returns false for non-purchase types', function (): void {
    expect(OrderTypeEnum::Order->isPurchase())->toBeFalse();
});

test('toArray returns localized values', function (): void {
    expect(OrderTypeEnum::toArray())->toBeArray()->not->toBeEmpty();
});

test('values returns all case values', function (): void {
    expect(OrderTypeEnum::values())->toBeArray()->toContain('order', 'purchase', 'retoure');
});
