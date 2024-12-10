<?php

namespace FluxErp\Actions\Dashboard;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Dashboard;
use FluxErp\Rulesets\Dashboard\DeleteDashboardRuleset;

class DeleteDashboard extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteDashboardRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Dashboard::class];
    }

    public function performAction(): bool
    {
        return resolve_static(Dashboard::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
