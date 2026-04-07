<?php

namespace FluxErp\Actions\DataTable;

use FluxErp\Actions\FluxAction;
use TeamNiftyGmbH\DataTable\Models\DatatableUserSetting;

class ShareFilter extends FluxAction
{
    public static function models(): array
    {
        return [DatatableUserSetting::class];
    }

    protected function getRulesets(): string|array
    {
        return [];
    }

    public function performAction(): DatatableUserSetting
    {
        $setting = DatatableUserSetting::query()->findOrFail($this->data['id']);
        $setting->is_shared = $this->data['is_shared'];
        $setting->save();

        return $setting;
    }

    protected function prepareForValidation(): void
    {
        $this->rules = [
            'id' => ['required', 'exists:datatable_user_settings,id'],
            'is_shared' => ['required', 'boolean'],
        ];
    }
}
