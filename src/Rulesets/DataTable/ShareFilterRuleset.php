<?php

namespace FluxErp\Rulesets\DataTable;

use FluxErp\Rulesets\FluxRuleset;
use TeamNiftyGmbH\DataTable\Models\DatatableUserSetting;

class ShareFilterRuleset extends FluxRuleset
{
    protected static ?string $model = DatatableUserSetting::class;

    public function rules(): array
    {
        return [
            'id' => ['required', 'exists:datatable_user_settings,id'],
            'is_shared' => ['required', 'boolean'],
        ];
    }
}
