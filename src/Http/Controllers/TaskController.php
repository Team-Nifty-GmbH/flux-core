<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Task;
use FluxErp\States\Task\TaskState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function userIndex(Request $request): JsonResponse
    {
        $userId = $request->user()->getKey();

        $tasks = resolve_static(Task::class, 'query')
            ->where(function (Builder $query) use ($userId): void {
                $query->where('responsible_user_id', $userId)
                    ->orWhereRelation('users', 'users.id', $userId);
            })
            ->whereNotIn('state', TaskState::endStateKeys())
            ->orderByDesc('priority')
            ->orderByRaw('ISNULL(due_date), due_date ASC')
            ->get(['id', 'name', 'state', 'priority', 'due_date'])
            ->map(fn (Task $task): array => [
                'id' => $task->getKey(),
                'name' => $task->name,
                'state' => $task->state::$name,
                'priority' => $task->priority,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'url' => $task->getUrl(),
            ]);

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $tasks)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
