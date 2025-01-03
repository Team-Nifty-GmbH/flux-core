<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\DeleteUserRuleset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeleteUser extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteUserRuleset::class;
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function performAction(): ?bool
    {
        $user = resolve_static(User::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $user->tokens()->delete();
        $user->locks()->delete();

        $user->children()->update(['parent_id' => $user->parent_id]);

        return $user->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->data['id'] == Auth::id()) {
            throw ValidationException::withMessages([
                'id' => [__('Cannot delete yourself')],
            ])->errorBag('deleteUser');
        }
    }
}
