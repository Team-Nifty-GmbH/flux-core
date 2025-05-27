<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Facades\Widget;
use FluxErp\Livewire\Support\Dashboard;
use FluxErp\Livewire\Widgets\Settings\System\Cache;
use FluxErp\Livewire\Widgets\Settings\System\Database;
use FluxErp\Livewire\Widgets\Settings\System\Extensions;
use FluxErp\Livewire\Widgets\Settings\System\Laravel;
use FluxErp\Livewire\Widgets\Settings\System\Php;
use FluxErp\Livewire\Widgets\Settings\System\Queue;
use FluxErp\Livewire\Widgets\Settings\System\Server;
use FluxErp\Livewire\Widgets\Settings\System\Session;
use FluxErp\Livewire\Widgets\Settings\System\Storage;
use Illuminate\Support\Str;

class System extends Dashboard
{
    protected static ?array $defaultWidgets = [
        'default' => [
        ],
    ];

    protected bool $hasTimeSelector = false;

    public static function getDefaultOrderColumn(): int
    {
        return 1;
    }

    public static function getDefaultOrderRow(): int
    {
        return 0;
    }

    public static function getDefaultWidgets(): array
    {
        return collect([
            Widget::get(Cache::class),
            Widget::get(Database::class),
            Widget::get(Extensions::class),
            Widget::get(Laravel::class),
            Widget::get(Php::class),
            Widget::get(Queue::class),
            Widget::get(Server::class),
            Widget::get(Session::class),
            Widget::get(Storage::class),
        ])
            ->map(function ($widget) {
                $widget['id'] ??= Str::uuid()->toString();
                $widget['width'] ??= data_get($widget, 'defaultWidth');
                $widget['height'] ??= data_get($widget, 'defaultHeight');
                $widget['order_column'] ??= data_get($widget, 'defaultOrderColumn');
                $widget['order_row'] ??= data_get($widget, 'defaultOrderRow');

                return $widget;
            })
            ->toArray();
    }
}
