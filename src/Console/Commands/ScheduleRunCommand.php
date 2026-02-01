<?php

namespace FluxErp\Console\Commands;

use Cron\CronExpression;
use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Events\Scheduling\ScheduleTasksRegistered;
use FluxErp\Events\Scheduling\ScheduleTasksRegistering;
use FluxErp\Facades\Repeatable;
use FluxErp\Models\Schedule as ScheduleModel;
use FluxErp\Traits\TracksSchedule;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\ScheduleRunCommand as BaseScheduleRunCommand;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;

class ScheduleRunCommand extends BaseScheduleRunCommand
{
    public function handle(Schedule $schedule, Dispatcher $dispatcher, Cache $cache, ExceptionHandler $handler): void
    {
        if ($this->laravel->isDownForMaintenance()) {
            parent::handle($schedule, $dispatcher, $cache, $handler);

            return;
        }

        $dispatcher->dispatch(new ScheduleTasksRegistering($schedule));

        $overdueEvents = [];
        $repeatables = resolve_static(ScheduleModel::class, 'query')
            ->where(fn (Builder $query) => $query->where('ends_at', '>', now()->toDateTimeString())
                ->orWhereNull('ends_at')
            )
            ->where(fn (Builder $query) => $query->whereRaw('recurrences > COALESCE(current_recurrence,0)')
                ->orWhereNull('recurrences')
            )
            ->whereNotNull('cron->methods->basic')
            ->where('is_active', true)
            ->get();
        foreach ($repeatables as $repeatable) {
            if (method_exists($repeatable->class, 'isRepeatable') && ! $repeatable->class::isRepeatable()) {
                continue;
            }

            if ($repeatable->due_at?->greaterThan(now())) {
                continue;
            }

            $event = $this->makeScheduleEvent(
                $schedule, $repeatable->type, $repeatable->class, $repeatable->parameters, $repeatable->getKey()
            );

            if (is_null($event)) {
                continue;
            }

            foreach ($repeatable->cron['methods'] as $key => $method) {
                if (! $method) {
                    continue;
                }

                if (! is_null($parameters = $repeatable->cron['parameters'][$key] ?? null) && $parameters !== []) {
                    $event = $event->{$method}(...$parameters);
                } else {
                    $event = $event->{$method}();
                }
            }

            if (data_get($repeatable->cron, 'methods.basic') === FrequenciesEnum::LastDayOfMonth->value) {
                $parts = explode(' ', $event->expression);
                $nextRunDate = now()->addMonthNoOverflow()->endOfMonth()
                    ->setTime((int) $parts[1], (int) $parts[0]);
            } else {
                $nextRunDate = (new CronExpression($event->expression))->getNextRunDate(now());
            }

            // Mark event as overdue
            if ($repeatable->due_at &&
                ! $event->isDue($this->laravel)
                && $repeatable->due_at->lessThan(now())
            ) {
                $overdueEvents[] = $event;
            }

            $repeatable->cron_expression = $event->expression;
            $repeatable->save();

            $event->before(function () use ($repeatable): void {
                $repeatable->last_run = now();
                $repeatable->save();
            });

            $event->onSuccess(function () use ($repeatable): void {
                if ($repeatable->type === RepeatableTypeEnum::Job) {
                    return;
                }

                if ($repeatable->recurrences) {
                    $repeatable->current_recurrence++;
                }

                $repeatable->last_success = now();
                $repeatable->save();
            });

            $event->after(function () use ($repeatable, $nextRunDate): void {
                $repeatable->due_at = $nextRunDate;
                $repeatable->save();
            });
        }

        // Register repeatables with defaultCron that aren't in the DB
        $scheduledClasses = $repeatables->pluck('class')->all();

        Repeatable::all()->each(function (array $repeatable) use ($schedule, $scheduledClasses): void {
            if (in_array($repeatable['class'], $scheduledClasses)) {
                return;
            }

            if (! $repeatable['class']::isRepeatable()) {
                return;
            }

            $defaultCron = $repeatable['class']::defaultCron();

            if (is_null($defaultCron)) {
                return;
            }

            $event = $this->makeScheduleEvent(
                $schedule, $repeatable['type'], $repeatable['class'], $repeatable['parameters']
            );

            if (is_null($event)) {
                return;
            }

            $event->cron($defaultCron->getExpression());
        });

        $dispatcher->dispatch(new ScheduleTasksRegistered($schedule));

        parent::handle($schedule, $dispatcher, $cache, $handler);

        // Run overdue events
        foreach ($overdueEvents as $overdueEvent) {
            if (! $overdueEvent->filtersPass($this->laravel)) {
                $this->dispatcher->dispatch(new ScheduledTaskSkipped($overdueEvent));

                continue;
            }

            if ($overdueEvent->onOneServer) {
                $this->runSingleServerEvent($overdueEvent);
            } else {
                $this->runEvent($overdueEvent);
            }
        }
    }

    protected function makeScheduleEvent(
        Schedule $schedule,
        RepeatableTypeEnum $type,
        string $class,
        ?array $parameters,
        ?int $scheduleId = null
    ): mixed {
        return match ($type) {
            RepeatableTypeEnum::Command => $schedule->command($class, $parameters ?? []),
            RepeatableTypeEnum::Job => $schedule->job(tap(
                $parameters ? new $class(...$parameters) : new $class(),
                function ($job) use ($scheduleId): void {
                    if ($scheduleId && in_array(TracksSchedule::class, class_uses_recursive($job))) {
                        $job->scheduleId = $scheduleId;
                    }
                }
            )),
            RepeatableTypeEnum::Invokable => $schedule->call(new $class(), $parameters ?? []),
            RepeatableTypeEnum::Shell => $schedule->exec($class, $parameters ?? []),
            default => null,
        };
    }
}
