<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateBankConnectionRequest;
use FluxErp\Models\BankConnection;
use Illuminate\Database\Eloquent\Model;

class UpdateBankConnection extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateBankConnectionRequest())->rules();
    }

    public static function models(): array
    {
        return [BankConnection::class];
    }

    public function execute(): Model
    {
        $bankConnection = BankConnection::query()
            ->whereKey($this->data['id'])
            ->first();

        $bankConnection->fill($this->data);
        $bankConnection->save();

        return $bankConnection->withoutRelations()->fresh();
    }
}
