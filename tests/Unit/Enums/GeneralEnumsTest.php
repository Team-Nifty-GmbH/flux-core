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

test('BundleTypeEnum has standard and group', function (): void {
    expect(BundleTypeEnum::values())->toBe(['group', 'standard']);
});

test('CommunicationTypeEnum has mail letter phone-call', function (): void {
    expect(CommunicationTypeEnum::values())->toContain('mail', 'letter', 'phone-call');
});

test('DayPartEnum has full_day first_half second_half', function (): void {
    expect(DayPartEnum::values())->toContain('full_day', 'first_half', 'second_half');
});

test('DevicePlatformEnum has all platforms', function (): void {
    expect(DevicePlatformEnum::values())->toBe(['android', 'ios', 'web']);
});

test('FrequenciesEnum basic frequencies include daily weekly monthly yearly', function (): void {
    $basic = FrequenciesEnum::getBasicFrequencies();

    expect($basic)->toContain('daily', 'weekly', 'monthly', 'yearly');
});

test('FrequenciesEnum has day and time constraints', function (): void {
    expect(FrequenciesEnum::getDayConstraints())->toBeArray()->not->toBeEmpty();
    expect(FrequenciesEnum::getTimeConstraints())->toBeArray()->not->toBeEmpty();
});

test('LedgerAccountTypeEnum has revenue and expense', function (): void {
    $values = LedgerAccountTypeEnum::values();

    expect($values)->toContain('revenue', 'expense');
});

test('OvertimeCompensationEnum has all options', function (): void {
    expect(OvertimeCompensationEnum::values())->toBe(['payment', 'time_off', 'mixed', 'none']);
});

test('PaymentRunTypeEnum has direct_debit and money_transfer', function (): void {
    expect(PaymentRunTypeEnum::values())->toBe(['direct_debit', 'money_transfer']);
});

test('PropertyTypeEnum has text and option', function (): void {
    expect(PropertyTypeEnum::values())->toBe(['option', 'text']);
});

test('RoundingMethodEnum apply with none returns original', function (): void {
    $result = RoundingMethodEnum::apply(RoundingMethodEnum::None, 10.456, 2, null, null);

    expect($result)->toEqual(10.456);
});

test('SepaMandateTypeEnum has B2B and BASIC', function (): void {
    expect(SepaMandateTypeEnum::values())->toBe(['B2B', 'BASIC']);
});
