<?php

namespace FluxErp\Actions\Dashboard;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Dashboard;
use FluxErp\Rulesets\Dashboard\CreateDashboardRuleset;
use Illuminate\Support\Arr;

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
        $copyFromDashboardId = Arr::pull($this->data, 'copy_from_dashboard_id');

        $this->data['authenticatable_type'] ??= auth()->user()->getMorphClass();
        $this->data['authenticatable_id'] ??= auth()->id();

        $dashboard = app(Dashboard::class, ['attributes' => $this->data]);
        $dashboard->save();

        if ($copyFromDashboardId) {
            $this->copyFromDashboard($copyFromDashboardId, $dashboard);
        }

        return $dashboard->fresh();
    }

    public function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if ($copyFromDashboardId = $this->getData('copy_from_dashboard_id')) {
            $this->data['name'] ??= resolve_static(Dashboard::class, 'query')
                ->whereKey($copyFromDashboardId)
                ->value('name');
            $this->data['is_public'] ??= false;
        }
    }

    protected function copyFromDashboard(int $copyFromId, Dashboard $targetDashboard): void
    {
        $widgets = resolve_static(Dashboard::class, 'query')
            ->whereKey($copyFromId)
            ->where('is_public', true)
            ->first()
            ->widgets()
            ->get();

        foreach ($widgets as $widget) {
            $widget->replicate()->fill([
                'dashboard_id' => $targetDashboard->id,
                'widgetable_id' => $targetDashboard->authenticatable_id,
                'widgetable_type' => $targetDashboard->authenticatable_type,
            ])->save();
        }
    }
}
