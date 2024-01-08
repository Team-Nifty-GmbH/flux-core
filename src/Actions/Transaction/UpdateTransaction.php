<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateTransactionRequest;
use FluxErp\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class UpdateTransaction extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateTransactionRequest())->rules();
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function performAction(): Model
    {
        $transaction = Transaction::query()
            ->whereKey($this->data['id'])
            ->first();

        $transaction->fill($this->data);
        $transaction->save();

        return $transaction->withoutRelations()->fresh();
    }
}
