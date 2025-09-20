<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Transaction;
use FluxErp\Rulesets\Transaction\UpdateTransactionRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UpdateTransaction extends FluxAction
{
    public static function models(): array
    {
        return [Transaction::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateTransactionRuleset::class;
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

    protected function prepareForValidation(): void
    {
        if ($this->getData('counterpart_iban')) {
            $this->data['counterpart_iban'] = is_string($this->getData('counterpart_iban'))
                ? Str::of($this->getData('counterpart_iban'))->upper()->remove(' ')->toString()
                : $this->getData('counterpart_iban');
        }

        if ($this->getData('counterpart_bic')) {
            $this->data['counterpart_bic'] = is_string($this->getData('counterpart_bic'))
                ? Str::of($this->getData('counterpart_bic'))->upper()->remove(' ')->toString()
                : $this->getData('counterpart_bic');
        }
    }
}
