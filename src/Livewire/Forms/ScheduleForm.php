<?php

namespace FluxErp\Livewire\Forms;

use Cron\CronExpression;
use FluxErp\Actions\Schedule\CreateSchedule;
use FluxErp\Actions\Schedule\DeleteSchedule;
use FluxErp\Actions\Schedule\UpdateSchedule;
use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Facades\Repeatable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Locked;
use Throwable;

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

    #[Locked]
    public array $nextExecutionDates = [];

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

        $this->nextExecutionDates = $this->getNextExecutionDates();
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

        $response = $action->checkPermission()
            ->validate()
            ->execute();

        $this->fill($response);
    }

    public function getNextExecutionDates(int $count = 5): array
    {
        $method = data_get($this->cron, 'methods.basic');

        if (! $method) {
            return [];
        }

        $basicParams = data_get($this->cron, 'parameters.basic', []);

        $parameters = match ($method) {
            'hourlyAt', 'everyOddHour', 'everyTwoHours',
            'everyThreeHours', 'everyFourHours', 'everySixHours' => [data_get($basicParams, '0') ?: 0],
            'dailyAt', 'lastDayOfMonth' => [data_get($basicParams, '0') ?? '00:00'],
            'twiceDaily', 'weeklyOn',
            'monthlyOn', 'quarterlyOn' => array_filter(
                $basicParams,
                fn ($key) => $key < 2,
                ARRAY_FILTER_USE_KEY
            ),
            'twiceDailyAt', 'twiceMonthly', 'yearlyOn' => $basicParams,
            default => [],
        };

        $parameters = array_map(
            fn ($item) => ! is_null($item) && ! str_contains((string) $item, ':') ? (int) $item : $item,
            $parameters
        );

        $schedule = app(Schedule::class);
        $event = $schedule->call(fn () => null);

        if ($parameters !== []) {
            $event = $event->{$method}(...$parameters);
        } else {
            $event = $event->{$method}();
        }

        $dates = [];
        $dueAt = $this->due_at ? Carbon::parse($this->due_at) : null;
        $from = $dueAt ? $dueAt->copy() : now();
        $endsAt = $this->end_radio === 'ends_at' && $this->ends_at
            ? Carbon::parse($this->ends_at)
            : null;
        $remainingRecurrences = $this->end_radio === 'recurrences' && $this->recurrences
            ? max(0, $this->recurrences - ($this->current_recurrence ?? 0))
            : null;

        if ($dueAt && $dueAt->greaterThan(now())) {
            if (! $endsAt || $dueAt->lessThanOrEqualTo($endsAt)) {
                $dates[] = $dueAt->toDateTimeString();
            }
        }

        if ($from->lessThan(now())) {
            $from = now();
        }

        $maxDates = $remainingRecurrences !== null
            ? min($count, $remainingRecurrences)
            : $count;

        if ($maxDates <= 0) {
            return $dates;
        }

        try {
            if ($method === FrequenciesEnum::LastDayOfMonth->value) {
                $parts = explode(' ', $event->expression);
                $current = $from->copy();

                for ($i = 0; $i < $maxDates; $i++) {
                    $next = $current->copy()->endOfMonth()
                        ->setTime((int) $parts[1], (int) $parts[0]);

                    if ($next->lessThanOrEqualTo($current)) {
                        $next = $current->copy()->addMonthNoOverflow()->endOfMonth()
                            ->setTime((int) $parts[1], (int) $parts[0]);
                    }

                    if ($endsAt && $next->greaterThan($endsAt)) {
                        break;
                    }

                    $dates[] = $next->toDateTimeString();
                    $current = $next->copy()->addDay();
                }
            } else {
                $cron = new CronExpression($event->expression);
                $current = $from;

                for ($i = 0; $i < $maxDates; $i++) {
                    $next = Carbon::instance($cron->getNextRunDate($current));

                    if ($endsAt && $next->greaterThan($endsAt)) {
                        break;
                    }

                    $dates[] = $next->toDateTimeString();
                    $current = $next;
                }
            }
        } catch (Throwable) {
            return [];
        }

        return $dates;
    }

    protected function getActions(): array
    {
        return [
            'delete' => DeleteSchedule::class,
        ];
    }
}
