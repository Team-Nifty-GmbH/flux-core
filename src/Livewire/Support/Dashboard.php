<?php

namespace FluxErp\Livewire\Support;

use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\Dashboard\RendersWidgets;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

abstract class Dashboard extends Component
{
    use Actions, RendersWidgets;

    #[Url]
    public ?string $group = null;

    protected bool $canEdit = true;

    protected bool $hasTimeSelector = true;

    public static function getDefaultWidgets(): ?array
    {
        return static::mapDefaultWidgets(static::$defaultWidgets);
    }

    public static function setDefaultWidgets(?array $defaultWidgets): void
    {
        static::$defaultWidgets = $defaultWidgets;
    }

    protected static function mapDefaultWidgets(?array $widgets = null): array
    {
        return collect($widgets ?? [])
            ->filter()
            ->map(function (array $widget) {
                $widget['id'] ??= Str::uuid()->toString();
                $widget['width'] ??= data_get($widget, 'defaultWidth');
                $widget['height'] ??= data_get($widget, 'defaultHeight');
                $widget['order_column'] ??= data_get($widget, 'defaultOrderColumn');
                $widget['order_row'] ??= data_get($widget, 'defaultOrderRow');
                $widget['group'] ??= null;

                return $widget;
            })
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.support.dashboard');
    }

    public function getWidgetAttributes(): array
    {
        return [];
    }
}
