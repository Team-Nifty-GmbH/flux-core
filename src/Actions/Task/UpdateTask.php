<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\Task\TaskAssignedEvent;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\UpdateTaskRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class UpdateTask extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateTaskRuleset::class;
    }

    public static function models(): array
    {
        return [Task::class];
    }

    public function performAction(): Model
    {
        $task = resolve_static(Task::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $users = Arr::pull($this->data, 'users');
        $orderPositions = Arr::pull($this->data, 'order_positions');
        $tags = Arr::pull($this->data, 'tags');

        if (! is_null($users)) {
            $result = $task->users()->sync($users);

            event(TaskAssignedEvent::make($task)
                ->subscribeChannel(collect(data_get($result, 'attached'))
                    ->when(
                        $this->getData('responsible_user_id') !== $task->responsible_user_id,
                        fn (Collection $users) => $users->add($this->getData('responsible_user_id'))
                    )
                )
                ->unsubscribeChannel(collect(data_get($result, 'detached'))
                    ->when(
                        $this->getData('responsible_user_id') !== $task->responsible_user_id
                        && ! is_null($task->responsible_user_id),
                        fn (Collection $users) => $users->add($task->responsible_user_id)
                    )
                )
            );
        }

        if (! is_null($orderPositions)) {
            $task->orderPositions()->sync(
                Arr::mapWithKeys(
                    $orderPositions,
                    fn ($item, $key) => [$item['id'] => ['amount' => $item['amount']]]
                )
            );
        }

        if (! is_null($tags)) {
            $task->syncTags(resolve_static(Tag::class, 'query')->whereIntegerInRaw('id', $tags)->get());
        }

        $task->fill($this->data);
        $task->save();

        return $task->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Task::class));

        $this->data = $validator->validated();
    }
}
