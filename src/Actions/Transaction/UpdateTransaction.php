<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Transaction;
use FluxErp\Rulesets\Transaction\UpdateTransactionRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateTransaction extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateTransactionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function performAction(): Model
    {
        $transaction = resolve_static(Transaction::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $transaction->fill($this->data);
        $transaction->save();

        return $transaction->withoutRelations()->fresh();
    }
}
