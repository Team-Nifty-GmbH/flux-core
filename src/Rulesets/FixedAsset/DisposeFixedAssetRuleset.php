<?php

namespace FluxErp\Rulesets\FixedAsset;

use FluxErp\Models\FixedAsset;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DisposeFixedAssetRuleset extends FluxRuleset
{
    protected static ?string $model = FixedAsset::class;

    public function rules(): array
    {
        return [
            'fixed_asset_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FixedAsset::class]),
            ],
            'disposal_ledger_account_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'disposed_at' => 'required|date|before_or_equal:today',
        ];
    }
}
