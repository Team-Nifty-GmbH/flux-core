<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\BankConnection;
use FluxErp\Rulesets\BankConnection\UpdateBankConnectionRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateBankConnection extends FluxAction
{
    public static function models(): array
    {
        return [BankConnection::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateBankConnectionRuleset::class;
    }

    public function performAction(): Model
    {
        $bankConnection = resolve_static(BankConnection::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $bankConnection->fill($this->data);
        $bankConnection->save();

        return $bankConnection->withoutRelations()->fresh();
    }
}
