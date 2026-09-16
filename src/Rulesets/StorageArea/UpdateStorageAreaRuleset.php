<?php

namespace FluxErp\Rulesets\StorageArea;

use FluxErp\Enums\StorageAreaTypeEnum;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateStorageAreaRuleset extends FluxRuleset
{
    protected static ?string $model = StorageArea::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => StorageArea::class]),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => StorageArea::class]),
            ],
            'warehouse_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Warehouse::class]),
            ],
            'code' => 'sometimes|required|string|max:255',
            'name' => 'nullable|string|max:255',
            'storage_area_type_enum' => [
                'sometimes',
                'required',
                Rule::enum(StorageAreaTypeEnum::class),
            ],
            'sort_number' => 'sometimes|required|integer|min:0',
            'is_active' => 'boolean',
            'is_storage_location' => 'boolean',
        ];
    }
}
