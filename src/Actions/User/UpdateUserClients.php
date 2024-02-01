<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\EditUserClientRequest;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Model;

class UpdateUserClients extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = (new EditUserClientRequest())->rules();
    }

    public static function name(): string
    {
        return 'user.update-clients';
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function performAction(): Model
    {
        $user = User::query()
            ->whereKey($this->data['user_id'])
            ->first();

        $user->clients()->sync($this->data['clients']);

        return $user;
    }
}
