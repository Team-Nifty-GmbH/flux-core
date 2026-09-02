<?php

namespace FluxErp\Rulesets\WarehouseBin;

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateWarehouseBinRuleset extends FluxRuleset
{
    protected static ?string $model = WarehouseBin::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WarehouseBin::class]),
            ],
            'warehouse_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Warehouse::class]),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => WarehouseBin::class]),
            ],
            'code' => 'sometimes|required|string|max:255',
            'name' => 'nullable|string|max:255',
            'warehouse_bin_type_enum' => [
                'sometimes',
                'required',
                Rule::enum(WarehouseBinTypeEnum::class),
            ],
            'is_storage_location' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
