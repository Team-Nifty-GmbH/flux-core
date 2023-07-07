<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\SepaMandate;

class DeleteSepaMandate extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:sepa_mandates,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [SepaMandate::class];
    }

    public function execute(): bool|null
    {
        return SepaMandate::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
