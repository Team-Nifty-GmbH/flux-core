<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateCommentRequest;
use FluxErp\Models\Comment;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreateComment implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateCommentRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'comment.create';
    }

    public static function description(): string|null
    {
        return 'create comment';
    }

    public static function models(): array
    {
        return [Comment::class, EventSubscription::class];
    }

    public function execute(): Comment
    {
        $mentions = [];
        preg_match_all("(@(?P<names>[a-zA-Z0-9\-_]+))", $this->data['comment'], $mentions);
        $mentionedUsers = User::query()
            ->whereIn('user_code', $mentions['names'])
            ->orWhere('id', Auth::id())
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

        $comment = new Comment();
        $comment->model_type = $this->data['model_type'];
        $comment->model_id = $this->data['model_id'];
        $comment->parent_id = $this->data['parent_id'] ?? null;
        $comment->comment = $this->data['comment'];
        $comment->is_sticky = $this->data['is_sticky'] ?? false;
        $comment->is_internal = $this->data['is_internal'] ?? true;
        $comment->save();

        return $comment;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
