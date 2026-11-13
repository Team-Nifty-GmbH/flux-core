<?php

namespace FluxErp\Rulesets\FixedAsset;

use FluxErp\Enums\DepreciationMethodEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateFixedAssetRuleset extends FluxRuleset
{
    protected static ?string $model = FixedAsset::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FixedAsset::class]),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'depreciation_ledger_account_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'ledger_account_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'order_id' => [
                'nullable',
                'required_with:order_position_id',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'order_position_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'number' => 'string|max:255|nullable',
            'acquired_at' => 'sometimes|required|date',
            'depreciation_starts_at' => 'sometimes|required|date',
            'acquisition_cost' => [
                'sometimes',
                'required',
                'decimal:0,2',
                app(Numeric::class, ['min' => 0.01]),
            ],
            'residual_value' => [
                'sometimes',
                'required',
                'decimal:0,2',
                app(Numeric::class, ['min' => 0]),
            ],
            'depreciation_method_enum' => [
                'sometimes',
                'required',
                app(EnumRule::class, ['type' => DepreciationMethodEnum::class]),
            ],
            'externally_depreciated_until' => 'nullable|date',
            'useful_life_months' => 'sometimes|required|integer|min:1|max:1200',
        ];
    }
}
