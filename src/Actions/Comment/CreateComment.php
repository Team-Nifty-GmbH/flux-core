<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use FluxErp\Rulesets\Comment\CreateCommentRuleset;

class CreateComment extends FluxAction
{
    public static function models(): array
    {
        return [Comment::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateCommentRuleset::class;
    }

    public function performAction(): Comment
    {
        $comment = app(Comment::class, ['attributes' => $this->data]);
        $comment->save();

        return $comment->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['is_sticky'] ??= false;
        $this->data['is_internal'] ??= true;
    }
}
