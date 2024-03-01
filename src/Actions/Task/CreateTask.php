<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\CreateTaskRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateTask extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateTaskRuleset::class, 'getRules');
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
            $task->attachTags(app(Tag::class)->query()->whereIntegerInRaw('id', $tags)->get());
        }

        return $task->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Task::class));

        $this->data = $validator->validated();
    }
}
