<?php

namespace FluxErp\Rulesets\FixedAsset;

use FluxErp\Enums\DepreciationMethodEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Tenant;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateFixedAssetRuleset extends FluxRuleset
{
    protected static ?string $model = FixedAsset::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:fixed_assets,uuid',
            'contact_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'depreciation_ledger_account_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'ledger_account_id' => [
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
            'tenant_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
            'name' => 'required|string|max:255',
            'number' => 'string|max:255|nullable',
            'acquired_at' => 'required|date',
            'depreciation_starts_at' => 'required|date',
            'acquisition_cost' => [
                'required',
                'decimal:0,2',
                app(Numeric::class, ['min' => 0.01]),
            ],
            'residual_value' => [
                'required',
                'decimal:0,2',
                app(Numeric::class, ['min' => 0]),
            ],
            'depreciation_method_enum' => [
                'required',
                app(EnumRule::class, ['type' => DepreciationMethodEnum::class]),
            ],
            'externally_depreciated_until' => 'nullable|date',
            'useful_life_months' => 'required|integer|min:1|max:1200',
        ];
    }
}
