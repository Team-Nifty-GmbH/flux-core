<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Permission;
use Illuminate\Validation\ValidationException;

class DeletePermission extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:permissions,id',
        ];
    }

    public static function models(): array
    {
        return [Permission::class];
    }

    public function performAction(): ?bool
    {
        return Permission::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        if (Permission::query()
            ->whereKey($this->data['id'])
            ->first()
            ->is_locked
        ) {
            throw ValidationException::withMessages([
                'is_locked' => [__('Permission is locked')],
            ])->errorBag('deletePermission');
        }
    }
}
