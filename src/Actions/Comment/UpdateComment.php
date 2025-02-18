<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use FluxErp\Rulesets\Comment\UpdateCommentRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateComment extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateCommentRuleset::class;
    }

    public static function models(): array
    {
        return [Comment::class];
    }

    public function performAction(): Model
    {
        $comment = resolve_static(Comment::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $comment->fill($this->data);
        $comment->save();

        return $comment->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (is_null($this->getData('is_sticky'))) {
            unset($this->data['is_sticky']);
        }

        if (is_null($this->getData('is_internal'))) {
            unset($this->data['is_internal']);
        }
    }
}
