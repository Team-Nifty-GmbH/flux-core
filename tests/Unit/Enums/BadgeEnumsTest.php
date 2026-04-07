<?php

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\PrintJobStatusEnum;
use Illuminate\Support\HtmlString;

test('AbsenceRequestStateEnum has expected states', function (): void {
    expect(AbsenceRequestStateEnum::values())->toBe(['approved', 'pending', 'rejected', 'revoked']);
});

test('AbsenceRequestStateEnum badge returns HtmlString', function (): void {
    expect(AbsenceRequestStateEnum::Approved->badge())->toBeInstanceOf(HtmlString::class);
    expect(AbsenceRequestStateEnum::Pending->badge())->toBeInstanceOf(HtmlString::class);
});

test('AbsenceRequestStateEnum color returns string', function (): void {
    expect(AbsenceRequestStateEnum::Approved->color())->toBeString()->not->toBeEmpty();
    expect(AbsenceRequestStateEnum::Rejected->color())->toBeString()->not->toBeEmpty();
});

test('PrintJobStatusEnum has expected statuses', function (): void {
    expect(PrintJobStatusEnum::values())->toBe(['queued', 'processing', 'completed', 'failed', 'cancelled']);
});

test('PrintJobStatusEnum badge returns HtmlString', function (): void {
    expect(PrintJobStatusEnum::Queued->badge())->toBeInstanceOf(HtmlString::class);
});

test('PrintJobStatusEnum color returns string', function (): void {
    expect(PrintJobStatusEnum::Queued->color())->toBeString()->not->toBeEmpty();
    expect(PrintJobStatusEnum::Failed->color())->toBeString()->not->toBeEmpty();
});
