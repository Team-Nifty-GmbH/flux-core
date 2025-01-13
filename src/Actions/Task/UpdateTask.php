<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\Task\TaskAssignedEvent;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Rulesets\Task\UpdateTaskRuleset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
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

            resolve_static(User::class, 'query')
                ->whereIntegerInRaw('id', data_get($result, 'detached'))
                ->get()
                ->when(
                    $this->getData('responsible_user_id') !== $task->responsible_user_id
                    && ! is_null($task->responsible_user_id),
                    fn (Collection $users) => $users->add(
                        resolve_static(User::class, 'query')
                            ->whereKey($task->responsible_user_id)
                            ->first(['id'])
                    )
                )
                ->filter()
                ->each(function (Model $user) use ($task) {
                    $user->unsubscribeNotificationChannel($task->broadcastChannel());
                });

            resolve_static(User::class, 'query')
                ->whereIntegerInRaw('id', data_get($result, 'attached'))
                ->get()
                ->when(
                    $this->getData('responsible_user_id') !== $task->responsible_user_id,
                    fn (Collection $users) => $users->add(
                        resolve_static(User::class, 'query')
                            ->whereKey($this->getData('responsible_user_id'))
                            ->first(['id'])
                    )
                )
                ->filter()
                ->each(function (Model $user) use ($task) {
                    $user->subscribeNotificationChannel($task->broadcastChannel());
                });

            event(new TaskAssignedEvent($task));
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
