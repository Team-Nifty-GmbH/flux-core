<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use FluxErp\Rulesets\Comment\UpdateCommentRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateComment extends FluxAction
{
    public static function models(): array
    {
        return [Comment::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateCommentRuleset::class;
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
        if (is_null($this->getData('comment'))) {
            unset($this->data['comment']);
        }

        if (is_null($this->getData('is_sticky'))) {
            unset($this->data['is_sticky']);
        }

        if (is_null($this->getData('is_internal'))) {
            unset($this->data['is_internal']);
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        // The comment text may only be changed by its author; sticky/internal
        // toggles stay open to anyone with the update permission.
        if (! is_null($this->getData('comment'))) {
            // The created_by accessor resolves to the author's name, so read the
            // raw morph value through the base query instead.
            $author = resolve_static(Comment::class, 'query')
                ->whereKey($this->getData('id'))
                ->toBase()
                ->value('created_by');

            $currentUser = auth()->user()?->getMorphClass() . ':' . auth()->id();

            if ($author !== $currentUser) {
                throw ValidationException::withMessages([
                    'comment' => [__('You can only edit your own comments.')],
                ]);
            }
        }
    }
}
