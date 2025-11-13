<?php

namespace FluxErp\Actions\Task;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Events\Task\TaskAssignedEvent;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\UpdateTaskRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateTask extends FluxAction
{
    public static function models(): array
    {
        return [Task::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateTaskRuleset::class;
    }

    public function performAction(): Model
    {
        $task = resolve_static(Task::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $users = Arr::pull($this->data, 'users');
        $orderPositions = Arr::pull($this->data, 'order_positions');
        $tags = Arr::pull($this->data, 'tags');

        $subscribers = [];
        $unsubscribers = [];
        if ($this->getData('responsible_user_id', $task->responsible_user_id) !== $task->responsible_user_id) {
            $subscribers[] = $this->getData('responsible_user_id');
            $unsubscribers[] = $task->responsible_user_id;
        }

        $task->fill($this->data);
        $task->save();

        if (! is_null($orderPositions)) {
            $task->orderPositions()->sync(
                Arr::mapWithKeys(
                    $orderPositions,
                    fn ($item, $key) => [$item['id'] => ['amount' => $item['amount']]]
                )
            );
        }

        if (! is_null($tags)) {
            $task->syncTags(resolve_static(Tag::class, 'query')
                ->whereIntegerInRaw('id', $tags)
                ->get()
            );
        }

        if (! is_null($users)) {
            $result = $task->users()->sync($users);

            $subscribers = array_merge($subscribers, data_get($result, 'attached') ?? []);
            $unsubscribers = array_merge($unsubscribers, data_get($result, 'detached') ?? []);
        }

        $subscribers = array_filter(array_unique($subscribers));
        $unsubscribers = array_filter(array_unique($unsubscribers));
        if ($subscribers || $unsubscribers) {
            event(TaskAssignedEvent::make($task)
                ->subscribeChannel($subscribers)
                ->unsubscribeChannel($unsubscribers)
            );
        }

        return $task->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
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
                    ->errorBag('updateTask');
            }
        }
    }
}
