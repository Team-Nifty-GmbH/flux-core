<?php

use FluxErp\Models\AbsenceRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        resolve_static(AbsenceRequest::class, 'query')
            ->with('employee.workTimeModelHistory.workTimeModel')
            ->chunkById(200, function (Collection $absenceRequests): void {
                foreach ($absenceRequests as $absenceRequest) {
                    if (! $absenceRequest->employee) {
                        continue;
                    }

                    $absenceRequest->updateQuietly([
                        'days_requested' => $absenceRequest->calculateDaysRequested(),
                    ]);
                }
            });
    }

    public function down(): void {}
};
