<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\UpdateUserClientsRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateUserClients extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = resolve_static(UpdateUserClientsRuleset::class, 'getRules');
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
        $user = app(User::class)->query()
            ->whereKey($this->data['user_id'])
            ->first();

        $user->clients()->sync($this->data['clients']);

        return $user;
    }
}
