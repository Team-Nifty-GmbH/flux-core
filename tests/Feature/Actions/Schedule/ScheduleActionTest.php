<?php

use FluxErp\Actions\Schedule\CreateSchedule;

test('create schedule requires name and cron', function (): void {
    CreateSchedule::assertValidationErrors([], ['name', 'cron']);
});
