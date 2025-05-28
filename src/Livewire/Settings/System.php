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

class System extends Dashboard
{
    protected bool $hasTimeSelector = false;

    public static function getDefaultWidgets(): array
    {
        return parent::mapDefaultWidgets(
            static::$defaultWidgets ??
            [
                Widget::get(Cache::class),
                Widget::get(Database::class),
                Widget::get(Extensions::class),
                Widget::get(Laravel::class),
                Widget::get(Php::class),
                Widget::get(Queue::class),
                Widget::get(Server::class),
                Widget::get(Session::class),
                Widget::get(Storage::class),
            ]
        );
    }
}
