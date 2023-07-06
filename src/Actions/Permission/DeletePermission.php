<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeletePermission implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:permissions,id',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'permission.delete';
    }

    public static function description(): string|null
    {
        return 'delete permission';
    }

    public static function models(): array
    {
        return [Permission::class];
    }

    public function execute()
    {
        return Permission::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

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
