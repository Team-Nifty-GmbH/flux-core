<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\SerialNumberRange;

class DeleteSerialNumberRange extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:serial_number_ranges,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    public function execute(): bool|null
    {
        return SerialNumberRange::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
