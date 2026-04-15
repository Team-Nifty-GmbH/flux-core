<?php

use Carbon\Carbon;
use FluxErp\RuleEngine\Conditions\DateRangeCondition;
use FluxErp\RuleEngine\Conditions\DayOfWeekCondition;
use FluxErp\RuleEngine\Conditions\TimeRangeCondition;
use FluxErp\RuleEngine\Scopes\PriceScope;

// --- DateRangeCondition ---

test('date range matches when date is within range', function (): void {
    $condition = new DateRangeCondition();
    $condition->from = '2024-01-01';
    $condition->to = '2024-12-31';

    $scope = new PriceScope(now: Carbon::parse('2024-06-15'));

    expect($condition->match($scope))->toBeTrue();
});

test('date range does not match when date is before range', function (): void {
    $condition = new DateRangeCondition();
    $condition->from = '2024-06-01';
    $condition->to = '2024-12-31';

    $scope = new PriceScope(now: Carbon::parse('2024-05-31'));

    expect($condition->match($scope))->toBeFalse();
});

test('date range does not match when date is after range', function (): void {
    $condition = new DateRangeCondition();
    $condition->from = '2024-01-01';
    $condition->to = '2024-05-31';

    $scope = new PriceScope(now: Carbon::parse('2024-06-01'));

    expect($condition->match($scope))->toBeFalse();
});

test('date range matches on boundary from date', function (): void {
    $condition = new DateRangeCondition();
    $condition->from = '2024-06-15';
    $condition->to = '2024-12-31';

    $scope = new PriceScope(now: Carbon::parse('2024-06-15'));

    expect($condition->match($scope))->toBeTrue();
});

test('date range matches on boundary to date', function (): void {
    $condition = new DateRangeCondition();
    $condition->from = '2024-01-01';
    $condition->to = '2024-06-15';

    $scope = new PriceScope(now: Carbon::parse('2024-06-15'));

    expect($condition->match($scope))->toBeTrue();
});

test('date range with only from is open-ended', function (): void {
    $condition = new DateRangeCondition();
    $condition->from = '2024-01-01';
    $condition->to = null;

    $scope = new PriceScope(now: Carbon::parse('2099-12-31'));

    expect($condition->match($scope))->toBeTrue();
});

test('date range with only to is open-ended at start', function (): void {
    $condition = new DateRangeCondition();
    $condition->from = null;
    $condition->to = '2099-12-31';

    $scope = new PriceScope(now: Carbon::parse('2024-01-01'));

    expect($condition->match($scope))->toBeTrue();
});

test('date range with no from and no to always matches', function (): void {
    $condition = new DateRangeCondition();

    $scope = new PriceScope(now: Carbon::parse('2024-06-15'));

    expect($condition->match($scope))->toBeTrue();
});

// --- DayOfWeekCondition ---

test('day of week matches when current day is in list', function (): void {
    $condition = new DayOfWeekCondition();
    $condition->days = [1, 2, 3]; // Mon, Tue, Wed

    // Monday = dayOfWeekIso 1
    $scope = new PriceScope(now: Carbon::parse('2024-06-17')); // Monday

    expect($condition->match($scope))->toBeTrue();
});

test('day of week does not match when current day is not in list', function (): void {
    $condition = new DayOfWeekCondition();
    $condition->days = [6, 7]; // Sat, Sun

    // Monday = dayOfWeekIso 1
    $scope = new PriceScope(now: Carbon::parse('2024-06-17')); // Monday

    expect($condition->match($scope))->toBeFalse();
});

test('day of week with empty days returns false', function (): void {
    $condition = new DayOfWeekCondition();
    $condition->days = [];

    $scope = new PriceScope(now: Carbon::parse('2024-06-17'));

    expect($condition->match($scope))->toBeFalse();
});

// --- TimeRangeCondition ---

test('time range matches when current time is within range', function (): void {
    $condition = new TimeRangeCondition();
    $condition->from = '09:00';
    $condition->to = '17:00';

    $scope = new PriceScope(now: Carbon::parse('2024-06-17 12:00:00'));

    expect($condition->match($scope))->toBeTrue();
});

test('time range does not match when current time is before range', function (): void {
    $condition = new TimeRangeCondition();
    $condition->from = '09:00';
    $condition->to = '17:00';

    $scope = new PriceScope(now: Carbon::parse('2024-06-17 08:59:00'));

    expect($condition->match($scope))->toBeFalse();
});

test('time range does not match when current time is after range', function (): void {
    $condition = new TimeRangeCondition();
    $condition->from = '09:00';
    $condition->to = '17:00';

    $scope = new PriceScope(now: Carbon::parse('2024-06-17 17:01:00'));

    expect($condition->match($scope))->toBeFalse();
});

test('time range with no bounds always matches', function (): void {
    $condition = new TimeRangeCondition();

    $scope = new PriceScope(now: Carbon::parse('2024-06-17 03:00:00'));

    expect($condition->match($scope))->toBeTrue();
});
