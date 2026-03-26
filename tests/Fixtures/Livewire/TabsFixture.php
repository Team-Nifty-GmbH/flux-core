<?php

namespace FluxErp\Tests\Fixtures\Livewire;

use FluxErp\Htmlables\TabButton;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class TabsFixture extends Component
{
    use WithTabs;

    public string $activeTab = 'tabs-fixture-general';

    public ?int $modelId = null;

    public function render(): View
    {
        return view()->file(__DIR__ . '/../views/tabs-fixture.blade.php');
    }

    #[Renderless]
    public function getTabs(): array
    {
        return [
            TabButton::make('tabs-fixture-general')
                ->isLivewireComponent()
                ->wireModel('modelId')
                ->text(__('General')),
            TabButton::make('tabs-fixture-child')
                ->isLivewireComponent()
                ->wireModel('modelId')
                ->text(__('Child')),
        ];
    }

    public function refreshParent(): void
    {
        // Triggers a parent re-render (default Livewire behavior)
    }

    public function updatedActiveTab(): void
    {
        $this->forceRender();
    }
}
