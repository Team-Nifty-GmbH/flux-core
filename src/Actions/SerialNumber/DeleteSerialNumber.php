<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\SerialNumber;

class DeleteSerialNumber extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:serial_numbers,id',
        ];
    }

    public static function models(): array
    {
        return [SerialNumber::class];
    }

    public function execute(): bool|null
    {
        return SerialNumber::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
