<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Schedule\CreateSchedule;
use FluxErp\Actions\Schedule\DeleteSchedule;
use FluxErp\Actions\Schedule\UpdateSchedule;
use FluxErp\Facades\Repeatable;
use Livewire\Attributes\Locked;

class ScheduleForm extends FluxForm
{
    public array $cron = [
        'methods' => [
            'basic' => null,
            'dayConstraint' => null,
            'timeConstraint' => null,
        ],
        'parameters' => [
            'basic' => [null, null, null],
            'dayConstraint' => [],
            'timeConstraint' => [null, null],
        ],
    ];

    public ?int $current_recurrence = null;

    public ?string $description = null;

    public ?string $due_at = null;

    public ?string $end_radio = null;

    public ?string $ends_at = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $name = null;

    public ?array $orders = null;

    public array $parameters = [];

    public ?int $recurrences = null;

    public function fill($values): void
    {
        parent::fill($values);

        $this->cron['parameters']['basic'] = array_replace(
            [null, null, null],
            $this->cron['parameters']['basic']
        );

        $this->cron['parameters']['timeConstraint'] = array_replace(
            [null, null],
            $this->cron['parameters']['timeConstraint']
        );

        if (! is_null($this->name)) {
            $this->parameters = array_merge(
                Repeatable::get($this->name)['parameters'] ?? [],
                $this->parameters
            );
        }

        $this->end_radio = match (true) {
            ! is_null($this->ends_at) => 'ends_at',
            ! is_null($this->recurrences) => 'recurrences',
            default => 'never'
        };
    }

    public function save(): void
    {
        $data = $this->toArray();

        $data['cron']['parameters']['basic'] = match ($data['cron']['methods']['basic']) {
            'hourlyAt', 'everyOddHour', 'everyTwoHours',
            'everyThreeHours', 'everyFourHours', 'everySixHours' => [$data['cron']['parameters']['basic'][0] ?: 0],
            'dailyAt', 'lastDayOfMonth' => [$data['cron']['parameters']['basic'][0] ?? '00:00'],
            'twiceDaily', 'weeklyOn',
            'monthlyOn', 'quarterlyOn' => array_filter(
                $data['cron']['parameters']['basic'],
                fn ($key) => $key < 2,
                ARRAY_FILTER_USE_KEY
            ),
            'twiceDailyAt', 'twiceMonthly', 'yearlyOn' => $data['cron']['parameters']['basic'],
            default => [],
        };

        $data['cron']['parameters']['basic'] = array_map(
            fn ($item) => ! str_contains($item, ':') && ! is_null($item) ? (int) $item : $item,
            $data['cron']['parameters']['basic']
        );

        if (! $data['cron']['methods']['timeConstraint']) {
            $data['cron']['parameters']['timeConstraint'] = [];
        }

        if ($data['cron']['methods']['timeConstraint'] === 'at') {
            $data['cron']['parameters']['timeConstraint'] = [$data['cron']['parameters']['timeConstraint'][0]];
        }

        if ($data['cron']['methods']['dayConstraint'] !== 'days') {
            $data['cron']['parameters']['dayConstraint'] = [];
        }

        switch ($data['end_radio']) {
            case 'ends_at':
                $data['recurrences'] = null;
                break;
            case 'recurrences':
                $data['ends_at'] = null;
                break;
            default:
                $data['ends_at'] = null;
                $data['recurrences'] = null;
                break;
        }

        $action = $this->id ? UpdateSchedule::make($data) : CreateSchedule::make($data);

        $response = $action->validate()->execute();

        $this->fill($response);
    }

    protected function getActions(): array
    {
        return [
            'delete' => DeleteSchedule::class,
        ];
    }
}
