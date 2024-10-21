<?php

namespace FluxErp\Livewire\Dashboard;

use FluxErp\Livewire\Forms\DashboardForm;
use FluxErp\Models\Dashboard as DashboardModel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class Dashboard extends Component
{
    use Actions;

    public ?int $dashboardId = null;

    public array $dashboards = [];

    #[Locked]
    public array $publicDashboards = [];

    public DashboardForm $dashboardForm;

    public function mount(): void
    {
        $this->dashboards = resolve_static(DashboardModel::class, 'query')
            ->where('authenticatable_id', auth()->id())
            ->where('authenticatable_type', auth()->user()->getMorphClass())
            ->get(['id', 'name'])
            ->prepend(['id' => null, 'name' => __('Default')])
            ->toArray();

        $this->publicDashboards = resolve_static(DashboardModel::class, 'query')
            ->where('is_public', true)
            ->whereNot('authenticatable_id', auth()->id())
            ->where('authenticatable_type', auth()->user()->getMorphClass())
            ->get(['id', 'name'])
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.dashboard.dashboard');
    }

    #[Renderless]
    public function edit(DashboardModel $dashboard): void
    {
        $this->dashboardForm->reset();
        $this->dashboardForm->fill($dashboard);
        $this->dashboardId = $dashboard->id;

        $this->js(<<<'JS'
            $openModal('edit-dashboard');
        JS);
    }

    #[Renderless]
    public function selectPublicDashboard(DashboardModel $dashboard): bool
    {
        $this->dashboardForm->fill($dashboard);
        if ($this->dashboardForm->copyPublic) {
            $this->dashboardForm->is_public = false;
            $this->dashboardForm->id = null;
            $this->dashboardForm->copy_from_dashboard_id = $dashboard->id;
        }

        return $this->save();
    }

    #[Renderless]
    public function save(): bool
    {
        $existing = (bool) $this->dashboardForm->id;
        try {
            $this->dashboardForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        if (! $existing) {
            $this->dashboards[] = ['id' => $this->dashboardForm->id, 'name' => $this->dashboardForm->name];
        }

        return true;
    }

    public function delete(DashboardModel $dashboard): bool
    {
        $this->dashboardForm->reset();
        $this->dashboardForm->fill($dashboard);

        try {
            $this->dashboardForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->dashboards = array_filter(
            $this->dashboards,
            fn (array $dashboardItem) => data_get($dashboardItem, 'id') !== $dashboard->id
        );

        if ($this->dashboardId === $dashboard->id) {
            $this->dashboardId = null;
        }

        return true;
    }
}
