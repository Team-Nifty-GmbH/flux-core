<?php

namespace FluxErp\Rulesets\SerialNumber;

use FluxErp\Models\SerialNumber;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateSerialNumberRuleset extends FluxRuleset
{
    protected static ?string $model = SerialNumber::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => SerialNumber::class]),
            ],
            'serial_number' => 'sometimes|required|string|max:255',
            'supplier_serial_number' => 'string|nullable|max:255',
        ];
    }
}
