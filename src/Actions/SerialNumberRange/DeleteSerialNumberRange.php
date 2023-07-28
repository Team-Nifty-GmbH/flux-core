<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\SerialNumberRange;

class DeleteSerialNumberRange extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:serial_number_ranges,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    public function performAction(): ?bool
    {
        return SerialNumberRange::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
