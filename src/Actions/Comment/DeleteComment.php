<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeleteComment extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:comments,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Comment::class];
    }

    public function execute(): bool|null
    {
        return Comment::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

        $comment = Comment::query()
            ->whereKey($this->data['id'])
            ->first();

        // only super admins can delete other users comments
        if (
            ! (
                $comment->created_by instanceof (Auth::user()->getMorphClass())
                && $comment->created_by->id === Auth::id()
            ) && ! Auth::user()->hasRole('Super Admin')
        ) {
            throw ValidationException::withMessages([
                'comment' => [__('Cant delete other users comments.')],
            ]);
        }

        return $this;
    }
}
