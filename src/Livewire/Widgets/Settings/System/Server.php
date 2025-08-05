<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Server extends Component
{
    use Widgetable;

    public ?string $document_root = null;

    public ?string $os = null;

    public ?string $server_name = null;

    public ?string $software = null;

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 0;
    }

    public static function getDefaultOrderRow(): int
    {
        return 3;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public function mount(): void
    {
        $this->getData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.settings.system.server');
    }

    #[Renderless]
    public function getData(): void
    {
        $this->fill([
            'software' => data_get($_SERVER, 'SERVER_SOFTWARE', __('Unknown')),
            'os' => php_uname(),
            'document_root' => data_get($_SERVER, 'DOCUMENT_ROOT', __('Unknown')),
            'server_name' => data_get($_SERVER, 'SERVER_NAME', __('Unknown')),
        ]);
    }
}
