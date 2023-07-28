<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateSerialNumberRangeRequest;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateSerialNumberRange extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateSerialNumberRangeRequest())->rules();
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    public function performAction(): Model
    {
        $serialNumberRange = SerialNumberRange::query()
            ->whereKey($this->data['id'])
            ->first();

        $serialNumberRange->fill($this->data);
        $serialNumberRange->save();

        return $serialNumberRange->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        if ((array_key_exists('prefix', $this->data) || array_key_exists('affix', $this->data))
            && SerialNumber::query()
                ->where('serial_number_range_id', $this->data['id'])
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'serial_numbers' => [__('Serial number range has serial numbers')],
            ])->errorBag('updateSerialNumberRange');
        }
    }
}
