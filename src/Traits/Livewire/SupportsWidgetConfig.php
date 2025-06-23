<?php

namespace FluxErp\Traits\Livewire;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Mechanisms\ComponentRegistry;

trait SupportsWidgetConfig
{
    use EnsureUsedInLivewire;

    #[Locked]
    public ?array $config = null;

    protected function storeConfig(string $path, array|bool|string|int|float|Arrayable|null $value): void
    {
        $widget = auth()
            ->user()
            ->widgets()
            ->where(
                'dashboard_component',
                app(ComponentRegistry::class)->getClass($this->dashboardComponent)
            )
            ->where('component_name', $this->getName())
            ->first();

        if (! $widget) {
            exception_to_notifications(
                ValidationException::withMessages([
                    'dashboard_component' => [__('dashboard_component')],
                ]),
                $this
            );

            return;
        }

        if (is_null($widget->config)) {
            $widget->config = new Fluent();
        }

        $widget->config->set($path, $value);
        $widget->save();
    }
}
