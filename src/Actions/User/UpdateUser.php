<?php

namespace FluxErp\Actions\User;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateUserRequest;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateUser implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateUserRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'user.update';
    }

    public static function description(): string|null
    {
        return 'delete user';
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function execute(): Model
    {
        $user = User::query()
            ->whereKey($this->data['id'])
            ->first();

        $user->fill($this->data);
        $user->save();

        // Delete all tokens of the user if the user is set to is_active = false
        if (! ($this->data['is_active'] ?? true)) {
            $user->tokens()->delete();
        }

        return $user->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
