<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
use FluxErp\Rulesets\Role\DeleteRoleRuleset;
use Illuminate\Validation\ValidationException;

class DeleteRole extends FluxAction
{
    public static function models(): array
    {
        return [Role::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteRoleRuleset::class;
    }

    public function performAction(): ?bool
    {
        $role = resolve_static(Role::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $deleted = $role->delete();

        if ($deleted) {
            $role->widgets()->delete();
        }

        return $deleted;
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
                'role' => ['Cannot delete Super Admin role'],
            ])
                ->errorBag('deleteRole')
                ->status(423);
        }
    }
}
