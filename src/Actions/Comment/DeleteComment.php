<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use FluxErp\Rulesets\Comment\DeleteCommentRuleset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeleteComment extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteCommentRuleset::class;
    }

    public static function models(): array
    {
        return [Comment::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Comment::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $comment = resolve_static(Comment::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        // only super admins can delete other users comments
        if (! Auth::user()->hasRole('Super Admin') && ! $comment->getCreatedBy()?->is(Auth::user())) {
            throw ValidationException::withMessages([
                'comment' => [__('Cant delete other users comments.')],
            ]);
        }
    }
}
