<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\Comment\CreateCommentRuleset;
use FluxErp\Traits\Notifiable;
use Illuminate\Database\Eloquent\Model;

class CreateComment extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateCommentRuleset::class;
    }

    public static function models(): array
    {
        return [Comment::class, EventSubscription::class];
    }

    public function performAction(): Comment
    {
        $this->data['is_sticky'] ??= false;
        $this->data['is_internal'] ??= true;

        $comment = app(Comment::class, ['attributes' => $this->data]);
        $comment->save();

        preg_match_all('/data-id="([^:]+:\d+)"/', $this->data['comment'], $matches);
        collect(data_get($matches, 1, []))
            ->map(fn ($mention) => morph_to($mention))
            ->filter() // filter null values if morph was not possible
            ->filter(fn (Model $notifiable) => in_array(Notifiable::class, class_uses_recursive($notifiable)))
            ->each(fn (Model $notifiable) => $notifiable->subscribeNotificationChannel($comment->broadcastChannel()));

        return $comment->fresh();
    }
}
