<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateCommentRequest;
use FluxErp\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateComment implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateCommentRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'comment.update';
    }

    public static function description(): string|null
    {
        return 'update comment';
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
