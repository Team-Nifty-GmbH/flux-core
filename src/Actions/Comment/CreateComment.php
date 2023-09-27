<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCommentRequest;
use FluxErp\Models\Comment;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Auth;

class CreateComment extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateCommentRequest())->rules();
    }

    public static function models(): array
    {
        return [Comment::class, EventSubscription::class];
    }

    public function performAction(): Comment
    {
        preg_match_all('/data-mention="(.*?)"/', $this->data['comment'], $matches);
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
                ->where('model_type', $this->data['model_type'])
                ->where('model_id', $this->data['model_id'])
                ->first();

            if (! $eventSubscription) {
                CreateEventSubscription::make([
                    'event' => eloquent_model_event('created', Comment::class),
                    'user_id' => $mention->id,
                    'model_type' => $this->data['model_type'],
                    'model_id' => $this->data['model_id'],
                    'is_broadcast' => false,
                    'is_notifiable' => true,
                ])->execute();
            }
        }

        $this->data['is_sticky'] = $this->data['is_sticky'] ?? false;
        $this->data['is_internal'] = $this->data['is_internal'] ?? true;

        $comment = new Comment($this->data);
        $comment->save();

        return $comment->fresh();
    }
}
