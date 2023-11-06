<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeleteUser extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:users,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function performAction(): ?bool
    {
        $user = User::query()
            ->whereKey($this->data['id'])
            ->first();

        $user->tokens()->delete();
        $user->locks()->delete();

        $user->children()->update(['parent_id' => $user->parent_id]);

        return $user->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        if ($this->data['id'] == Auth::id()) {
            throw ValidationException::withMessages([
                'id' => [__('Cannot delete yourself')],
            ])->errorBag('deleteUser');
        }
    }
}
