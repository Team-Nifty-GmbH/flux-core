<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Unit;

class DeleteUnit extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:units,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Unit::class];
    }

    public function execute(): ?bool
    {
        return Unit::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
