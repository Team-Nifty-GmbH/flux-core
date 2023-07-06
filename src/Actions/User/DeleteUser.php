<?php

namespace FluxErp\Actions\User;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteUser implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:users,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'user.delete';
    }

    public static function description(): string|null
    {
        return 'delete user';
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function execute(): bool|null
    {
        $user = User::query()
            ->whereKey($this->data['id'])
            ->first();

        $user->tokens()->delete();

        return $user->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        if ($this->data['id'] == Auth::id()) {
            throw ValidationException::withMessages([
                'id' => [__('Cannot delete yourself')],
            ])->errorBag('deleteUser');
        }

        return $this;
    }
}
