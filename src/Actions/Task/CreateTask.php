<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\CreateTaskRuleset;
use Illuminate\Support\Arr;

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
}
