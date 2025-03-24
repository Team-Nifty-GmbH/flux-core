<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
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

        $task->fill($this->data);
        $task->save();

        if (! is_null($users)) {
            $task->users()->sync($users);
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

        return $task->withoutRelations()->fresh();
    }
}
