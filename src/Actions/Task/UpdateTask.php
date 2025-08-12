<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\Task\TaskAssignedEvent;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\UpdateTaskRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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
}
