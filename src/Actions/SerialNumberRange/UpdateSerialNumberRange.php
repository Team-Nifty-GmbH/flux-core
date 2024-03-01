<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Rulesets\SerialNumberRange\UpdateSerialNumberRangeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateSerialNumberRange extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateSerialNumberRangeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    public function performAction(): Model
    {
        $serialNumberRange = app(SerialNumberRange::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $serialNumberRange->fill($this->data);
        $serialNumberRange->save();

        return $serialNumberRange->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ((array_key_exists('prefix', $this->data) || array_key_exists('affix', $this->data))
            && app(SerialNumber::class)->query()
                ->where('serial_number_range_id', $this->data['id'])
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'serial_numbers' => [__('Serial number range has serial numbers')],
            ])->errorBag('updateSerialNumberRange');
        }
    }
}
