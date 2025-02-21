<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\Task\TaskAssignedEvent;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\CreateTaskRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class CreateTask extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateTaskRuleset::class;
    }

    public static function models(): array
    {
        return [Task::class];
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
                ->subscribeChannel(collect($users)
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

        return $task->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['priority'] ??= 0;
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Task::class));

        $this->data = $validator->validated();
    }
}
