<?php

namespace FluxErp\Livewire\Dashboard;

use FluxErp\Livewire\Forms\DashboardForm;
use FluxErp\Models\Dashboard as DashboardModel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class Dashboard extends Component
{
    use Actions;

    public ?int $dashboardId = null;

    public array $dashboards = [];

    public DashboardForm $dashboardForm;

    public function mount(): void
    {
        $this->dashboards = resolve_static(DashboardModel::class, 'query')
            ->where('authenticatable_id', auth()->id())
            ->where('authenticatable_type', auth()->user()->getMorphClass())
            ->get(['id', 'name'])
            ->prepend(['id' => null, 'name' => __('Default')])
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

        $this->js(<<<'JS'
            $openModal('edit-dashboard');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->dashboardForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
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

        return true;
    }
}
