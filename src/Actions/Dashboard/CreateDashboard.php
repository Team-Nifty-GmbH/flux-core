<?php

namespace FluxErp\Actions\Dashboard;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Dashboard;
use FluxErp\Rulesets\Dashboard\CreateDashboardRuleset;

class CreateDashboard extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateDashboardRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Dashboard::class];
    }

    public function performAction(): Dashboard
    {
        $this->data['authenticatable_type'] ??= auth()->user()->getMorphClass();
        $this->data['authenticatable_id'] ??= auth()->id();

        $dashboard = app(Dashboard::class, ['attributes' => $this->data]);
        $dashboard->save();

        return $dashboard->fresh();
    }
}
