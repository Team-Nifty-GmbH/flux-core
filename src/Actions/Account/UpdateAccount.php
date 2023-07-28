<?php

namespace FluxErp\Actions\Account;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateAccountRequest;
use FluxErp\Models\Account;
use Illuminate\Database\Eloquent\Model;

class UpdateAccount extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateAccountRequest())->rules();
    }

    public static function models(): array
    {
        return [Account::class];
    }

    public function performAction(): Model
    {
        $account = Account::query()
            ->whereKey($this->data['id'])
            ->first();

        $account->fill($this->data);
        $account->save();

        return $account->withoutRelations()->fresh();
    }
}
