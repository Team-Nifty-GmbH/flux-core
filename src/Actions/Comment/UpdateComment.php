<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use FluxErp\Rulesets\Comment\UpdateCommentRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateComment extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateCommentRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Comment::class];
    }

    public function performAction(): Model
    {
        $comment = app(Comment::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $comment->fill($this->data);
        $comment->save();

        return $comment->withoutRelations()->fresh();
    }
}
