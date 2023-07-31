<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateCommentRequest;
use FluxErp\Models\Comment;
use Illuminate\Database\Eloquent\Model;

class UpdateComment extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateCommentRequest())->rules();
    }

    public static function models(): array
    {
        return [Comment::class];
    }

    public function performAction(): Model
    {
        $comment = Comment::query()
            ->whereKey($this->data['id'])
            ->first();

        $comment->fill($this->data);
        $comment->save();

        return $comment->withoutRelations()->fresh();
    }
}
