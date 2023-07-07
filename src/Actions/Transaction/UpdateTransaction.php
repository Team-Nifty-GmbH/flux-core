<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateTransactionRequest;
use FluxErp\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class UpdateTransaction extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateTransactionRequest())->rules();
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function execute(): Model
    {
        $warehouse = Transaction::query()
            ->whereKey($this->data['id'])
            ->first();

        $warehouse->fill($this->data);
        $warehouse->save();

        return $warehouse->withoutRelations()->fresh();
    }
}
