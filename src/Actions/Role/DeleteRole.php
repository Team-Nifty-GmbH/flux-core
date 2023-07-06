<?php

namespace FluxErp\Actions\Role;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteRole implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:roles,id',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'role.delete';
    }

    public static function description(): string|null
    {
        return 'delete role';
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function execute(): bool|null
    {
        return Role::query()
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

        if (Role::query()
            ->whereKey($this->data['id'])
            ->first()
            ->name === 'Super Admin'
        ) {
            throw ValidationException::withMessages([
                'role' => [__('Cannot delete Super Admin role')],
            ])->errorBag('deleteRole');
        }

        return $this;
    }
}
