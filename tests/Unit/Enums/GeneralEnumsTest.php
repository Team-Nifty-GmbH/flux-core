<?php

use FluxErp\Enums\BundleTypeEnum;
use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Enums\DayPartEnum;
use FluxErp\Enums\DevicePlatformEnum;
use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Enums\RoundingMethodEnum;
use FluxErp\Enums\SepaMandateTypeEnum;

test('BundleTypeEnum has values', function (): void {
    expect(BundleTypeEnum::values())->toContain('standard', 'group');
});

test('CommunicationTypeEnum has values', function (): void {
    expect(CommunicationTypeEnum::toArray())->toBeArray()->not->toBeEmpty();
});

test('DayPartEnum has values', function (): void {
    expect(DayPartEnum::toArray())->toBeArray()->not->toBeEmpty();
});

test('DevicePlatformEnum has values', function (): void {
    expect(DevicePlatformEnum::values())->toContain('android', 'ios', 'web');
});

test('FrequenciesEnum basic frequencies', function (): void {
    $basic = FrequenciesEnum::getBasicFrequencies();

    expect($basic)->toBeArray()->toContain('daily', 'weekly', 'monthly');
});

test('LedgerAccountTypeEnum has values', function (): void {
    expect(LedgerAccountTypeEnum::toArray())->toBeArray()->not->toBeEmpty();
});

test('OvertimeCompensationEnum has values', function (): void {
    expect(OvertimeCompensationEnum::values())->toContain('payment', 'time_off', 'none');
});

test('PaymentRunTypeEnum has values', function (): void {
    expect(PaymentRunTypeEnum::values())->toContain('direct_debit', 'money_transfer');
});

test('PropertyTypeEnum has values', function (): void {
    expect(PropertyTypeEnum::toArray())->toBeArray()->not->toBeEmpty();
});

test('RoundingMethodEnum apply with none returns original', function (): void {
    $result = RoundingMethodEnum::apply(RoundingMethodEnum::None, 10.456, 2, null, null);

    expect($result)->toEqual(10.456);
});

test('SepaMandateTypeEnum has values', function (): void {
    expect(SepaMandateTypeEnum::values())->toContain('B2B', 'BASIC');
});
