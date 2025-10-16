<?php

namespace FluxErp\Actions\Task;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\ReplicateTaskRuleset;
use FluxErp\States\Task\Open;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ReplicateTask extends FluxAction
{
    public static function models(): array
    {
        return [Task::class];
    }

    protected function getRulesets(): string|array
    {
        return ReplicateTaskRuleset::class;
    }

    public function performAction(): Task
    {
        $categories = Arr::pull($this->data, 'categories');
        $tags = Arr::pull($this->data, 'tags');
        $users = Arr::pull($this->data, 'users');

        $originTask = resolve_static(Task::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $task = $originTask->replicate(array_merge(array_keys($this->getData()), ['state']));
        $task->fill(array_merge($this->getData(), ['state' => Open::$name]))
            ->save();

        $categories = ! is_null($categories) ? $categories : $originTask->categories()->pluck('id')->toArray();
        if ($categories) {
            $task->categories()->attach($categories);
        }

        $users = ! is_null($users) ? $users : $originTask->users()->pluck('id')->toArray();
        if ($users) {
            $task->users()->attach($users);
        }

        $tags = ! is_null($tags) ? $tags : $originTask->tags()->pluck('id')->toArray();
        if ($tags) {
            $task->attachTags(resolve_static(Tag::class, 'query')->whereIntegerInRaw('id', $tags)->get());
        }

        return $task->refresh();
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
                    ->errorBag('replicateTask');
            }
        }
    }
}
