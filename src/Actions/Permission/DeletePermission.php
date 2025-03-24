<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Permission;
use FluxErp\Rulesets\Permission\DeletePermissionRuleset;
use Illuminate\Validation\ValidationException;

class DeletePermission extends FluxAction
{
    public static function models(): array
    {
        return [Permission::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePermissionRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Permission::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Permission::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->is_locked
        ) {
            throw ValidationException::withMessages([
                'is_locked' => [__('Permission is locked')],
            ])
                ->errorBag('deletePermission')
                ->status(423);
        }
    }
}
