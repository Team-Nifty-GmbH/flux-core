<?php

namespace FluxErp\Actions\Comment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeleteComment extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:comments,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Comment::class];
    }

    public function performAction(): ?bool
    {
        return Comment::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

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
    }
}
