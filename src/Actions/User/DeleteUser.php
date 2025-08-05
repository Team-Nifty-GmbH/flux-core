<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\DeleteUserRuleset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeleteUser extends FluxAction
{
    public static function models(): array
    {
        return [User::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteUserRuleset::class;
    }

    public function performAction(): ?bool
    {
        $user = resolve_static(User::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $user->tokens()->delete();
        $user->locks()->delete();

        $user->children()->update(['parent_id' => $user->parent_id]);

        return $user->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->getData('id') == Auth::id()) {
            throw ValidationException::withMessages([
                'id' => [__('Cannot delete yourself')],
            ])
                ->status(403)
                ->errorBag('deleteUser');
        }
    }
}
