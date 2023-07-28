<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Role;
use Illuminate\Validation\ValidationException;

class DeleteRole extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:roles,id',
        ];
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function performAction(): ?bool
    {
        return Role::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        if (Role::query()
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
