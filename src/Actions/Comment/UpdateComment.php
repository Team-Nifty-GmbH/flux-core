<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateCommentRequest;
use FluxErp\Models\Comment;
use Illuminate\Database\Eloquent\Model;

class UpdateComment extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateCommentRequest())->rules();
    }

    public static function models(): array
    {
        return [Comment::class];
    }

    public function execute(): Model
    {
        $comment = Comment::query()
            ->whereKey($this->data['id'])
            ->first();

        $comment->fill($this->data);
        $comment->save();

        return $comment->withoutRelations()->fresh();
    }
}
