<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
use FluxErp\Rulesets\Role\DeleteRoleRuleset;
use Illuminate\Validation\ValidationException;

class DeleteRole extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteRoleRuleset::class;
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Role::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Role::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->name === 'Super Admin'
        ) {
            throw ValidationException::withMessages([
                'role' => [__('Cannot delete Super Admin role')],
            ])->errorBag('deleteRole');
        }
    }
}
