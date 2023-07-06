<?php

namespace FluxErp\Actions\User;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateUserRequest;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Validator;

class CreateUser implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateUserRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'user.create';
    }

    public static function description(): string|null
    {
        return 'create user';
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function execute(): User
    {
        $this->data['is_active'] = $this->data['is_active'] ?? true;
        $this->data['language_id'] = array_key_exists('language_id', $this->data) ?
            $this->data['language_id'] :
            Language::query()->where('language_code', config('app.locale'))->first()?->id;

        $user = new User($this->data);
        $user->save();

        return $user->refresh();
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
