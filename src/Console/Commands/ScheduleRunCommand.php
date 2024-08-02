<?php

namespace FluxErp\Console\Commands;

use Cron\CronExpression;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Events\Scheduling\ScheduleTasksRegistered;
use FluxErp\Events\Scheduling\ScheduleTasksRegistering;
use FluxErp\Models\Schedule as ScheduleModel;
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
            ->where('is_active', true)
            ->get();
        foreach ($repeatables as $repeatable) {
            if (method_exists($repeatable->class, 'isRepeatable') && ! $repeatable->class::isRepeatable()) {
                continue;
            }

            if ($repeatable->due_at?->greaterThan(now())) {
                continue;
            }

            $event = match ($repeatable->type) {
                RepeatableTypeEnum::Command => $schedule->command($repeatable->class, $repeatable->parameters ?? []),
                RepeatableTypeEnum::Job => $schedule->job($repeatable->parameters ?
                        new $repeatable->class(...$repeatable->parameters) : new $repeatable->class()
                ),
                RepeatableTypeEnum::Invokable => $schedule->call($repeatable->parameters ?
                    new $repeatable->class(...$repeatable->parameters) : new $repeatable->class()
                ),
                RepeatableTypeEnum::Shell => $schedule->exec($repeatable->class, $repeatable->parameters ?? []),
                default => null
            };

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

            $nextRunDate = (new CronExpression($event->expression))->getNextRunDate();

            // Mark event as overdue
            if ($repeatable->due_at &&
                ! $event->isDue($this->laravel)
                && $repeatable->due_at->lessThan(now())
            ) {
                $overdueEvents[] = $event;
            }

            $repeatable->cron_expression = $event->expression;
            $repeatable->save();

            $event->before(function () use ($repeatable) {
                $repeatable->last_run = now();
                $repeatable->save();
            });

            $event->onSuccess(function () use ($repeatable) {
                if ($repeatable->recurrences) {
                    $repeatable->current_recurrence++;
                }

                $repeatable->last_success = now();
                $repeatable->save();
            });

            $event->after(function () use ($repeatable, $nextRunDate) {
                $repeatable->due_at = $nextRunDate;
                $repeatable->save();
            });

        }

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
}
