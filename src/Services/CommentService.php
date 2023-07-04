<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateCommentRequest;
use FluxErp\Models\Comment;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Traits\Commentable;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function create(array $data): array
    {
        $model = class_exists($data['model_type'])
            ? $data['model_type']
            : 'FluxErp\Models\\' . ucfirst($data['model_type']);

        if (! class_exists($model)) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['model_type' => __('model type not found')]
            );
        }

        if (! array_key_exists(Commentable::class, class_uses($model))) {
            return ResponseHelper::createArrayResponse(
                statusCode: 405,
                data: ['model_type' => __('no comments allowed')]
            );
        }

        $modelInstance = $model::query()->whereKey($data['model_id'])->first();

        if (! $modelInstance) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['model_id' => 'model instance not found']
            );
        }

        preg_match_all('/data-mention="(.*?)"/', $data['comment'], $matches);
        $mentions = collect($matches[1])->map(function ($mention) {
            $exploded = explode(':', $mention);

            return [
                'class' => $exploded[0],
                'id' => $exploded[1],
            ];
        });

        $mentions = $mentions
            ->groupBy('class')
            ->map(function ($mentions) {
                return $mentions->pluck('id')->unique()->map(fn ($id) => (int) $id);
            })
            ->toArray();

        $mentionedUsers = User::query()
            ->where(function ($query) use ($mentions) {
                $query->whereIn('id', $mentions[User::class] ?? [])
                    ->orWhereHas('roles', function ($query) use ($mentions) {
                        $query->whereIn('id', $mentions[Role::class] ?? []);
                    })
                    ->orWhere('id', Auth::id());
            })
            ->where('is_active', true)
            ->get();

        foreach ($mentionedUsers as $mention) {
            $eventSubscription = EventSubscription::query()
                ->where('event', eloquent_model_event('created', Comment::class))
                ->where('user_id', $mention->id)
                ->where('model_type', $model)
                ->where('model_id', $modelInstance->id)
                ->first();

            if (! $eventSubscription) {
                $subscriptionService = new EventSubscriptionService();
                $subscriptionService->create([
                    'event' => eloquent_model_event('created', Comment::class),
                    'user_id' => $mention->id,
                    'model_type' => $model,
                    'model_id' => $modelInstance->id,
                    'is_broadcast' => false,
                    'is_notifiable' => true,
                ]);
            }
        }

        $comment = new Comment();
        $comment->model_type = $model;
        $comment->model_id = $modelInstance->id;
        $comment->parent_id = $data['parent_id'] ?? null;
        $comment->comment = $data['comment'];
        $comment->is_sticky = $data['is_sticky'] ?? false;
        $comment->is_internal = $data['is_internal'] ?? true;
        $comment->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $comment,
            statusMessage: __('comment created')
        );
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateCommentRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $comment = Comment::query()
                ->whereKey($item['id'])
                ->first();

            $comment->fill($item);
            $comment->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $comment->withoutRelations()->fresh(),
                additions: ['id' => $comment->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : __('comments updated'),
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $comment = Comment::query()
            ->whereKey($id)
            ->first();

        if (! $comment) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => __('comment not found')]
            );
        }

        // only super admins can delete other users comments
        if (
            ! ($comment->created_by instanceof (Auth::user()->getMorphClass()) && $comment->created_by->id === Auth::id())
            && ! Auth::user()->hasRole('Super Admin')
        ) {
            return ResponseHelper::createArrayResponse(
                statusCode: 403,
                statusMessage: __('cant delete other users comments.')
            );
        }

        $comment->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: __('comment deleted')
        );
    }
}
