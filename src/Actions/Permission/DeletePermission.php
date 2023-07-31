<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Permission;
use Illuminate\Validation\ValidationException;

class DeletePermission extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:permissions,id',
        ];
    }

    public static function models(): array
    {
        return [Permission::class];
    }

    public function execute(): ?bool
    {
        return Permission::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

        if (Permission::query()
            ->whereKey($this->data['id'])
            ->first()
            ->is_locked
        ) {
            throw ValidationException::withMessages([
                'is_locked' => [__('Permission is locked')],
            ])->errorBag('deletePermission');
        }

        return $this;
    }
}
