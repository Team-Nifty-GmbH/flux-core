<?php

namespace FluxErp\Actions\DataTable;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\DataTable\ShareFilterRuleset;
use TeamNiftyGmbH\DataTable\Models\DatatableUserSetting;

class ShareFilter extends FluxAction
{
    public static function models(): array
    {
        return [DatatableUserSetting::class];
    }

    protected function getRulesets(): string|array
    {
        return ShareFilterRuleset::class;
    }

    public function performAction(): DatatableUserSetting
    {
        $setting = DatatableUserSetting::query()->findOrFail($this->data['id']);
        $setting->is_shared = $this->data['is_shared'];
        $setting->save();

        return $setting;
    }
}
