<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;

class AbsenceTypeDistributionChart extends CircleChart
{
    use IsTimeFrameAwareWidget;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public static function getCategory(): ?string
    {
        return 'Human Resources';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 2;
    }

    public static function getDefaultOrderRow(): int
    {
        return 1;
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
    }

    public function calculateChart(): void
    {
        $startDate = $this->getStart();
        $endDate = $this->getEnd();

        $absenceRequests = resolve_static(AbsenceRequest::class, 'query')
            ->where('state', AbsenceRequestStateEnum::Approved)
            ->whereHas('employeeDays', function (Builder $query) use ($startDate, $endDate): void {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->with('absenceType:id,name,color')
            ->get(['id', 'absence_type_id', 'work_days_affected']);

        $absenceByType = [];
        foreach ($absenceRequests as $absenceRequest) {
            $absenceTypeId = $absenceRequest->absence_type_id;

            if (! isset($absenceByType[$absenceTypeId])) {
                $absenceByType[$absenceTypeId] = [
                    'name' => $absenceRequest->absenceType->name,
                    'color' => $absenceRequest->absenceType->color ?? ChartColorEnum::Slate,
                    'days' => 0,
                ];
            }

            $absenceByType[$absenceTypeId]['days'] = bcadd(
                data_get($absenceByType, $absenceTypeId . '.days'),
                $absenceRequest->work_days_affected ?? 0,
                2
            );
        }

        $labels = [];
        $series = [];
        $colors = [];

        foreach ($absenceByType as $typeData) {
            if (bccomp($typeData['days'], 0, 2) > 0) {
                $labels[] = $typeData['name'];
                $series[] = (float) bcround($typeData['days'], 2);
                $colors[] = $typeData['color'];
            }
        }

        $this->labels = $labels;
        $this->series = $series;
        $this->colors = $colors;
    }

    public function getPlotOptions(): array
    {
        return [
            'pie' => [
                'donut' => [
                    'labels' => [
                        'show' => true,
                        'total' => [
                            'show' => true,
                            'label' => __('Days'),
                        ],
                    ],
                ],
            ],
        ];
    }
}
