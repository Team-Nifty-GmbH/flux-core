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
        $setting = resolve_static(DatatableUserSetting::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $setting->is_shared = $this->getData('is_shared');
        $setting->save();

        return $setting;
    }
}
