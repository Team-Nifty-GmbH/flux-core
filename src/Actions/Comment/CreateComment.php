<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\Comment\CreateCommentRuleset;

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

        return $comment->fresh();
    }
}
