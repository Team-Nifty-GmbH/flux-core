<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteComment implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:comments,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'comment.delete';
    }

    public static function description(): string|null
    {
        return 'delete comment';
    }

    public static function models(): array
    {
        return [Comment::class];
    }

    public function execute()
    {
        return Comment::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        $comment = Comment::query()
            ->whereKey($this->data['id'])
            ->first();

        // only super admins can delete other users comments
        if (! (
                $comment->created_by instanceof (Auth::user()->getMorphClass())
                && $comment->created_by->id === Auth::id()
            )
            && ! Auth::user()->hasRole('Super Admin')
        ) {
            throw ValidationException::withMessages([
                'comment' => [__('Cant delete other users comments.')]
            ]);
        }

        return $this;
    }
}
