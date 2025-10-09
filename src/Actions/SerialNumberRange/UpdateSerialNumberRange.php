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
    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateSerialNumberRangeRuleset::class;
    }

    public function performAction(): Model
    {
        $serialNumberRange = resolve_static(SerialNumberRange::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $serialNumberRange->fill($this->data);
        $serialNumberRange->save();

        return $serialNumberRange->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ((array_key_exists('prefix', $this->data) || array_key_exists('affix', $this->data))
            && resolve_static(SerialNumber::class, 'query')
                ->where('serial_number_range_id', $this->getData('id'))
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'serial_numbers' => ['Serial number range has serial numbers'],
            ])
                ->errorBag('updateSerialNumberRange')
                ->status(423);
        }
    }
}
