<?php

namespace FluxErp\Actions\Task;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Events\Task\TaskAssignedEvent;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\CreateTaskRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CreateTask extends FluxAction
{
    public static function models(): array
    {
        return [Task::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateTaskRuleset::class;
    }

    public function performAction(): Task
    {
        $users = Arr::pull($this->data, 'users');
        $orderPositions = Arr::pull($this->data, 'order_positions');
        $tags = Arr::pull($this->data, 'tags');

        $task = app(Task::class, ['attributes' => $this->data]);
        $task->save();

        if ($users) {
            $task->users()->attach($users);

            event(TaskAssignedEvent::make($task)
                ->subscribeChannel(
                    collect($users)
                        ->when(
                            $this->getData('responsible_user_id'),
                            fn (Collection $users) => $users->add($this->getData('responsible_user_id'))
                        )
                )
            );
        }

        if ($orderPositions) {
            $task->orderPositions()->attach(
                Arr::mapWithKeys(
                    $orderPositions,
                    fn ($item, $key) => [$item['id'] => ['amount' => $item['amount']]]
                )
            );
        }

        if ($tags) {
            $task->attachTags(resolve_static(Tag::class, 'query')->whereIntegerInRaw('id', $tags)->get());
        }

        return $task->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['priority'] ??= 0;

        if ($this->getData('start_date')) {
            $this->data['start_time'] ??= null;
        } else {
            $this->data['has_start_reminder'] = false;
            $this->data['start_reminder_minutes_before'] = null;
        }

        if ($this->getData('due_date')) {
            $this->data['due_time'] ??= null;
        } else {
            $this->data['has_due_reminder'] = false;
            $this->data['due_reminder_minutes_before'] = null;
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (
            $this->getData('start_date')
            && $this->getData('due_date')
        ) {
            $start = Carbon::parse($this->getData('start_date'))
                ->setTimeFromTimeString($this->getData('start_time') ?? '00:00:00');
            $end = Carbon::parse($this->getData('due_date'))
                ->setTimeFromTimeString($this->getData('due_time') ?? '23:59:59');

            if ($end->lt($start)) {
                throw ValidationException::withMessages([
                    'due_datetime' => [
                        __('validation.after', ['attribute' => 'due_time', 'date' => $start->toDateTimeString()]),
                    ],
                ])
                    ->errorBag('createTask');
            }
        }
    }
}
