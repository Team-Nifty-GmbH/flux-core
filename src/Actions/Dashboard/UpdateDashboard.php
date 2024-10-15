<?php

namespace FluxErp\Actions\Dashboard;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Dashboard;
use FluxErp\Rulesets\Dashboard\UpdateDashboardRuleset;

class UpdateDashboard extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateDashboardRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Dashboard::class];
    }

    public function performAction(): Dashboard
    {
        $dashboard = resolve_static(Dashboard::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $dashboard->fill($this->data);
        $dashboard->save();

        return $dashboard->withoutRelations()->fresh();
    }
}
