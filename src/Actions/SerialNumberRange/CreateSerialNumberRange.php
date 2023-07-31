<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateSerialNumberRangeRequest;
use FluxErp\Models\SerialNumberRange;

class CreateSerialNumberRange extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateSerialNumberRangeRequest())->rules();
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    public function performAction(): SerialNumberRange
    {
        $this->data['current_number'] = array_key_exists('start_number', $this->data) ?
            --$this->data['start_number'] : 0;
        unset($this->data['start_number']);

        $serialNumberRange = new SerialNumberRange($this->data);
        $serialNumberRange->save();

        return $serialNumberRange->fresh();
    }
}
