<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SerialNumber;

class DeleteSerialNumber extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:serial_numbers,id',
        ];
    }

    public static function models(): array
    {
        return [SerialNumber::class];
    }

    public function performAction(): ?bool
    {
        return SerialNumber::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
