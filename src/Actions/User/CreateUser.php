<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateUserRequest;
use FluxErp\Models\Language;
use FluxErp\Models\User;

class CreateUser extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateUserRequest())->rules();
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
}
