<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Contracts\UserWidget;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Component;
use Livewire\Livewire;
use FluxErp\Widgets\WidgetManager;

class DashboardTest extends BaseSetup
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        // Auto discover and register widgets for testing
        $widgetManager = new WidgetManager();
        $widgetManager->autoDiscoverWidgets();
    }

    public function test_dashboard_widget_rendering()
    {
        $this->actingAs($this->user, 'web');

        // Create some dummy widgets to test the rendering
        $widgets = [
            ['id' => 1, 'component_name' => 'sample-component', 'name' => 'Widget 1', 'width' => 2, 'height' => 1],
        ];

        $component = new class extends Component implements UserWidget{
            public function render(): string
            {
                return <<<'blade'
                    <div>Hello from sample component</div>
                blade;
            }

            public static function getLabel(): string
            {
                return 'Sample Component';
            }
        };

        Livewire::component('sample-component', $component);
        Livewire::component('sample-component-2', $component);

        // Perform the Livewire test
        Livewire::test('dashboard.dashboard')
            ->set('widgets', $widgets)
            ->assertSee('Hello from sample component');
    }
}
